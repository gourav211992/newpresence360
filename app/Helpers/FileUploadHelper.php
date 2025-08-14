<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileUploadHelper
{
    public function handleFileUploads(Request $request, $model, array $fileConfigs): void
    {
        foreach ($fileConfigs as $attribute => $config) {
            $existingFiles = $model->{$attribute};
    
            if ($request->hasFile($attribute)) {
                $files = $request->file($attribute);
                $filePaths = $this->uploadFiles($files, $config['folder']);
    
                $this->clearFiles($existingFiles);
    
                if (count($filePaths) === 1) {
                    $model->update([$attribute => $filePaths[0]]);
                } else {
                    $model->update([$attribute => $filePaths]);
                }
            }
        }
    }
    
    public function uploadFiles($files, string $directory): array
    {
        $filePaths = [];
        $files = is_array($files) ? $files : [$files];

        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs($directory, $fileName, 'public');
            $filePaths[] = $filePath;
        }

        return $filePaths;
    }

    protected function clearFiles($files): void
    {
        $files = is_array($files) ? $files : [$files];

        foreach ($files as $file) {
            if (!empty($file)) {
                Storage::disk('public')->delete($file);
            } else {
                Log::error("Attempted to delete a file with an empty path.");
            }
        }
    }
}
