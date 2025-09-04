<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\GenericImport\GenericImportHelper;
use App\Helpers\Helper;
use App\Exports\GenericItemExport;
use Exception;

class GenericImportController extends Controller
{
    public function import(Request $request, string $alias)
    {
        try {
            $config = GenericImportHelper::importConfigByAlias($alias);

            $data = [
                'type'        => $config['type'],
                'sampleFile'  => route('import.sample.download', ['alias' => $alias]),
                'headers'     => GenericImportHelper::getHeaderMap($alias),
                'user'        => Helper::getAuthenticatedUser(),
                'redirectUrl' => route($config['route'], ['alias' => $config['type']]),
            ];

            return response()->json($data);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function importSave(Request $request, string $alias)
    {
        try {
            $config = GenericImportHelper::importConfigByAlias($alias);
            $importer = new $config['importer']($alias);

            if (!$request->hasFile('attachment') || !$request->file('attachment')->isValid()) {
                throw new \Exception("Invalid file upload");
            }

            $file = $request->file('attachment');

            // ✅ Parse the file once
            Excel::import($importer, $file);

            $parsedData = method_exists($importer, 'getParsedRows') ? $importer->getParsedRows() : [];

            // ✅ Store parsed data in session (or cache, if file is huge)
            session([
                "import_invalid_rows_$alias" => collect($parsedData)->where('is_valid', false)->values()->toArray()
            ]);

            return response()->json([
                'data' => $parsedData,
                'headers' => GenericImportHelper::getHeaderMap($alias),
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to process file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadSample(string $alias)
    {
        try {
            $headers = GenericImportHelper::getHeaderMap($alias);

            if (empty($headers)) {
                throw new \Exception("Header mapping not found for alias: $alias");
            }

            $filename = "sample_import_{$alias}.xlsx";
            return Excel::download(new \App\Exports\GenericSampleExport($headers), $filename);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export invalid rows from last import
     */
    public function downloadInvalid(Request $request, string $alias)
    {
        try {
            $config = GenericImportHelper::importConfigByAlias($alias);
            $importer = new $config['importer']($alias);

            if (!$request->hasFile('attachment') || !$request->file('attachment')->isValid()) {
                return back()->withErrors(['error' => 'No file found for re-parse.']);
            }

            $file = $request->file('attachment');
            \Maatwebsite\Excel\Facades\Excel::import($importer, $file);
            $parsedData = method_exists($importer, 'getInvalidRows') ? $importer->getInvalidRows() : [];
            if (empty($parsedData)) {
                return back()->withErrors(['error' => 'No invalid rows found.']);
            }

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\GenericItemExport($parsedData,$headerMap = GenericImportHelper::getHeaderMap($alias)),
                "invalid_rows_$alias.xlsx"
            );

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to export invalid rows: ' . $e->getMessage()]);
        }
    }

}
