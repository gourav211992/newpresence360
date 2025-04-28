<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;


trait Deletable
{
    public function isReferenced(array $ignoreTables = [])
    {
        $table = $this->getTable();
        $primaryKey = $this->getKeyName();
        $id = $this->getKey();
        $modelName = class_basename($this); 

        try {
            $foreignKeys = DB::select(
                "
                SELECT
                    TABLE_NAME AS referencing_table,
                    COLUMN_NAME AS referencing_column
                FROM
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                    REFERENCED_TABLE_NAME = :table
                    AND REFERENCED_COLUMN_NAME = :column
                    AND TABLE_SCHEMA = DATABASE()
                ",
                ['table' => $table, 'column' => $primaryKey]
            );

            $referencingTables = [];

            foreach ($foreignKeys as $fk) {
                $referencingTable = $fk->referencing_table;
                $referencingColumn = $fk->referencing_column;

                if (!in_array($referencingTable, $ignoreTables)) {
                    $count = DB::table($referencingTable)
                    ->where($referencingColumn, $id)
                    ->count();
                 
                    if ($count > 0) {
                        if (!isset($referencingTables[$referencingTable])) {
                            $referencingTables[$referencingTable] = [];
                        }
                        $referencingTables[$referencingTable][] = $referencingColumn;
                        break;
                    }
                }
            }

            if (!empty($referencingTables)) {
                $messages = [];
                foreach ($referencingTables as $table => $columns) {
                    $columnList = implode(', ', $columns);
                    $messages[] = "Table '$table'";
                }
                // $message = "{$modelName} cannot be deleted because it is already in use";
                $message = "Record cannot be deleted because it is already in use";
                
                return [
                    'status' => false,
                    'message' => $message
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error fetching foreign keys: ' . $e->getMessage()
            ];
        }

        return ['status' => true];
    }

    public function deleteWithReferences(array $referenceTables = [], array $ignoreTables = [])
    {
        $id = $this->getKey(); 
        $table = $this->getTable(); 
        $totalReferences = 0;
        $referencedTables = []; 
        $linkedTables = [];
        $allReferences = $this->getReferencedTablesAndColumns($ignoreTables);   
        if (!$allReferences['status']) {
            return [
                'status' => false,
                'message' => $allReferences['message']
            ];
        }
    
        foreach ($allReferences['referenced_tables'] as $referencingTable => $columns) {
            if (array_key_exists($referencingTable, $referenceTables)) {
                $linkedRecords = DB::table($referencingTable)
                    ->where($columns[0] ?? 'id', $id) 
                    ->get();
                if ($linkedRecords->isNotEmpty()) {
                    $totalReferences += $linkedRecords->count();
                    $linkedTables[] = $referencingTable; 
                    break;
                }
            } else {
                foreach ($columns as $column) {
                    $linkedRecords = DB::table($referencingTable)
                        ->where($column, $id)
                        ->get();
                    if ($linkedRecords->isNotEmpty()) {
                        Log::warning("Attempted to delete {$table} ID {$id} but found references in '{$referencingTable}', column '{$column}'. Linked Records: ", $linkedRecords->toArray());
                        return [
                            'status' => false,
                            'message' => "Record cannot be deleted because it is already in use.",
                            'referenced_tables' => $linkedTables
                        ];
                    }
                }
            }
    
        }
        
        # Code by shobhit
        foreach ($this->getCachedModels() as $modelClass) {
            $relatedModel = resolve($modelClass);
            if (property_exists($relatedModel, 'referencingRelationships')) {
                foreach ($relatedModel->referencingRelationships as $relationMethod => $foreignKey) {
                    try {

                        if (method_exists($relatedModel, $relationMethod)) {
                            $relationQuery = $relatedModel->$relationMethod();
                            $relatedTable = $relationQuery->getRelated()->getTable();

                            if($table == $relatedTable) {
                                $relationQuery = $relatedModel;
                                if ($relationQuery->where($foreignKey, $id)->exists()) {
                                    $referencedTables[] = $modelClass;
                                    $linkedTables[] = $modelClass;
                                    $linkedRecords = $relationQuery->where($foreignKey, $id)->get();
                                    Log::warning("Attempted to delete {$table} ID {$id} but found references in '{$modelClass}', which is not in the specified reference tables. Linked Records: ", $linkedRecords->toArray());
                                    return [
                                        'status' => false,
                                        'message' => 'Record cannot be deleted because it is already in use.',
                                        'referenced_tables' => $linkedTables
                                    ];
                                }
                            }
                            
                        } else {
                            Log::warning("The method '{$relationMethod}' is not defined in {$modelClass}.");
                        }
                    } catch (\Throwable $e) {
                        Log::error("Error processing {$relationMethod} in {$modelClass}: " . $e->getMessage());
                    }
                }   
            }
        }

        foreach ($referenceTables as $referenceTable => $columnNames) {
            foreach ($columnNames as $columnName) {
                $count = DB::table($referenceTable)
                    ->where($columnName, $id)
                    ->count();
    
                if ($count > 0) {
                    $deletedCount = DB::table($referenceTable)
                        ->where($columnName, $id)
                        ->delete();
    
                    if ($deletedCount > 0) {
                        Log::info("Deleted {$deletedCount} references from table '{$referenceTable}' for {$table} ID {$id}.");
                        $totalReferences += $deletedCount; 
                        $referencedTables[] = $referenceTable; 
                    }
                }
            }
        }
        
        if ($totalReferences > 0 || count($linkedTables) === 0) { 
            $this->delete(); 
            Log::info("Deleted {$table} ID {$id} successfully.");
            return [
                'status' => true,
                'message' => 'Item deleted successfully.'
            ];
        }
        Log::info("Item cannot be deleted as it is referenced in other tables: " . implode(", ", $referencedTables));

        return [
            'status' => false,
            'message' => 'Item cannot be deleted as it is referenced in other tables.',
            'referenced_tables' => $referencedTables
        ];
    }
    
    public function getReferencedTablesAndColumns(array $ignoreTables = [])
    {
        $table = $this->getTable();
        $primaryKey = $this->getKeyName();

        try {
            $foreignKeys = DB::select(
                "
                SELECT
                    TABLE_NAME AS referencing_table,
                    COLUMN_NAME AS referencing_column
                FROM
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                    REFERENCED_TABLE_NAME = :table
                    AND REFERENCED_COLUMN_NAME = :column
                    AND TABLE_SCHEMA = DATABASE()
                ",
                ['table' => $table, 'column' => $primaryKey]
            );

            $referencingTables = [];
            foreach ($foreignKeys as $fk) {
                $referencingTable = $fk->referencing_table;
                $referencingColumn = $fk->referencing_column;
                if (!in_array($referencingTable, $ignoreTables)) {
                    if (!isset($referencingTables[$referencingTable])) {
                        $referencingTables[$referencingTable] = [];
                    }
                    $referencingTables[$referencingTable][] = $referencingColumn;
                }
            }

            return [
                'status' => true,
                'referenced_tables' => $referencingTables
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error fetching foreign keys: ' . $e->getMessage()
            ];
        }
    }

    protected function getCachedModels()
    {
        return Cache::remember('model_relations', 3600, function () {
            $modelFiles = File::allFiles(app_path('Models'));
            $models = [];
            foreach ($modelFiles as $file) {
                $relativePath = $file->getRelativePathname();
                $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
                $modelClass = 'App\\Models\\' . $classPath;
                if (class_exists($modelClass)) {
                    $models[] = $modelClass;
                }
            }
            return $models;
        });
    }   

    
    public function isModify(array $columnsToCheck = [], array $onlyTables = [])
    {
        $id = $this->getKey();
        $referencingTables = [];
    
        try {
            if (empty($onlyTables)) {
                return [
                    'status' => false,
                    'message' => 'No tables specified to check.'
                ];
            }
    
            foreach ($onlyTables as $tableName) {
                if (!Schema::hasTable($tableName)) {
                    continue;
                }
    
                $columns = Schema::getColumnListing($tableName);
    
                $matchingColumns = array_intersect($columnsToCheck, $columns);
                foreach ($matchingColumns as $column) {
                    $exists = DB::table($tableName)->where($column, $id)->limit(1)->exists();
                    if ($exists) {
                        $referencingTables[$tableName][] = $column;
                        break 2;
                    }
                }
            }
    
            if (!empty($referencingTables)) {
                return [
                    'status' => true,
                    'message' => 'Record cannot be modified because it is already in use.',
                    'referencingTables' => $referencingTables
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error checking references: ' . $e->getMessage()
            ];
        }
    
        return ['status' => false];
    }
    
    public function checkReferencesForMultipleIds(array $ids, array $columnsToCheck, string $specificTable = null, $attributeGroupId)
    {
        $results = [];
        $idsStr = array_map('strval', $ids);
    
        if (!$specificTable) {
            return [
                'status' => false,
                'message' => 'Specific table is required.'
            ];
        }
    
        try {
            if (!Schema::hasTable($specificTable)) {
                return [
                    'status' => false,
                    'message' => "Table '{$specificTable}' does not exist."
                ];
            }
    
            $columns = Schema::getColumnListing($specificTable);
            $matchingColumns = array_intersect($columnsToCheck, $columns);
    
            foreach ($matchingColumns as $column) {
                try {
                    $columnType = Schema::getColumnType($specificTable, $column);
                } catch (\Exception $e) {
                    continue;
                }
    
                $query = DB::table($specificTable)
                    ->select($column)
                    ->where('attribute_group_id', $attributeGroupId)
                    ->where(function ($q) use ($column, $idsStr, $columnType) {
                        $q->whereIn($column, $idsStr);
    
                        if (in_array($columnType, ['json', 'text'])) {
                            $q->orWhere(function ($q2) use ($column, $idsStr) {
                                foreach ($idsStr as $id) {
                                    $q2->orWhereJsonContains($column, $id);
                                }
                            });
                        }
                    });
    
                foreach ($query->get() as $record) {
                    $value = $record->$column;
    
                    $valuesArray = is_array($value)
                        ? $value
                        : (is_numeric($value)
                            ? [(string)$value, (int)$value]
                            : (json_decode($value, true) ?: [$value]));
    
                    foreach ($valuesArray as $val) {
                        foreach ($idsStr as $id) {
                            if ((string)$val === $id) {
                                $results[$id][$specificTable][] = $column;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    
        return $results;
    }

    public function checkAttributeUsage($erpItemAttributeId, $attributeGroupId, $attributeId, $tableName)
    {
        try {
            $tables = is_array($tableName) ? $tableName : [$tableName];
            $attributeMatch = false;
            $groupMatch = false;
            $matchedAttributeIds = [];
    
            $itemAttributeColumns = ['item_attribute_id', 'erp_item_attribute_id'];
            $attributeGroupColumns = ['attribute_group_id', 'attribute_name', 'attr_name'];
            $attributeValueColumns = ['attribute_id', 'attribute_value', 'attr_value'];
    
            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }
    
                $columns = Schema::getColumnListing($table);
    
                if ($this->hasColumns($columns, $itemAttributeColumns) && $this->hasColumns($columns, $attributeGroupColumns)) {
                    $query = DB::table($table);
                    $this->addWhereCondition($query, $itemAttributeColumns, $erpItemAttributeId);
                    $this->addWhereCondition($query, $attributeGroupColumns, $attributeGroupId);
    
                    if ($query->exists()) {
                        $groupMatch = true;
    
                        if ($this->hasColumns($columns, $attributeValueColumns)) {
                            $rows = $query->get();
                            foreach ($rows as $row) {
                                foreach ($attributeValueColumns as $attrColumn) {
                                    if (isset($row->$attrColumn)) {
                                        $val = $row->$attrColumn;
                                        $decoded = $this->decodeValue($val);
                                        $targetIds = is_array($attributeId)
                                            ? array_map('strval', $attributeId)
                                            : [(string)$attributeId];
    
                                        foreach ($targetIds as $target) {
                                            if (in_array($target, $decoded, true)) {
                                                $attributeMatch = true;
                                                $matchedAttributeIds[] = $target;
                                                break 3;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
    
            return [
                'group_match' => $groupMatch,
                'attribute_match' => $attributeMatch,
                'matched_attribute_ids' => array_unique($matchedAttributeIds),
                'status' => true,
                'message' => 'Success'
            ];
    
        } catch (\Exception $e) {
            return [
                'group_match' => false,
                'attribute_match' => false,
                'matched_attribute_ids' => [],
                'status' => false,
                'message' => 'Error checking attribute usage: ' . $e->getMessage()
            ];
        }
    }
    
    private function hasColumns($columns, $requiredColumns)
    {
        return !empty(array_intersect($columns, $requiredColumns));
    }
    
    private function addWhereCondition($query, $columns, $value)
    {
        $table = $query->from;
    
        $query->where(function ($q) use ($columns, $value, $table) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $q->orWhere($column, $value);
                }
            }
        });
    }
    
    private function decodeValue($value)
    {
        if ($this->isJson($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? array_map('strval', $decoded) : [(string)$decoded];
        } else {
            return is_array($value) ? array_map('strval', $value) : [(string)$value];
        }
    }
    
    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
