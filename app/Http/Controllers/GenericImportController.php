<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\GenericImport\GenericImportHelper;
use App\Helpers\Helper;
use Exception;

class GenericImportController extends Controller
{
    public function import(Request $request, string $alias)
    {
        try {
            $config = GenericImportHelper::importConfigByAlias($alias);

            $data = [
                'type' => $config['type'],
                'sampleFile' => route('import.sample.download', ['alias' => $alias]),
                'headers' => GenericImportHelper::getHeaderMap($alias),
                'user' => Helper::getAuthenticatedUser(),
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

            // ✅ Pass UploadedFile directly 
            Excel::import($importer, $file);

            $parsedData = method_exists($importer, 'getParsedRows') ? $importer->getParsedRows() : [];
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
}
