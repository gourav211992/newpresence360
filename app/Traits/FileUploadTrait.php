<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

trait FileUploadTrait
{
    public function uploadDocuments($files, string $collectionName, bool $clearExisting = false)
    {
         $mediaFiles = [];
        // Clear existing documents if specified
        if ($clearExisting) {
            $this->clearExistingDocuments($collectionName);
        }
        // Validate the file type as needed, or you can handle it in the controller
        if ($files instanceof UploadedFile) {
            $files = [$files]; // Wrap a single file in an array
        } elseif (!is_array($files)) {
            return $mediaFiles; // Return empty if not a valid file input
        }

        foreach ($files as $file) {
            // Validate that $file is an instance of UploadedFile
            if ($file instanceof UploadedFile) {
                $media = $this->media()->create([
                    'uuid' => (string) Str::uuid(),
                    'model_name' => class_basename($this), // Optional, if you need just the base name
                    'collection_name' => $collectionName,
                    'name' => $file->getClientOriginalName(),
                    'file_name' => $file->store('uploads/' . $collectionName, 'public'), // Store the file
                    'mime_type' => $file->getMimeType(),
                    'disk' => config('filesystems.default'), // Default disk or environment-specified disk
                    'size' => $file->getSize(),
                    'manipulations' => json_encode([]),
                    'custom_properties' => json_encode([]),
                    'generated_conversions' => json_encode([]),
                    'responsive_images' => json_encode([])
                ]);
                $mediaFiles[] = $media;
            }
        }
        return $mediaFiles;
    }

    public function clearExistingDocuments(string $collectionName, array $filesToKeep = [])
    {
        // Get the existing documents for the specified collection
        $existingDocuments = $this->media()->where('model_type', get_class($this))
            ->where('model_id',$this->id)
            // ->where('collection_name', $collectionName)
            ->get();
            foreach ($existingDocuments as $document) {
                // Check if the document is in the filesToKeep array
                if (!in_array($document->file_name, $filesToKeep)) {
                    // Delete the file from storage
                    Storage::delete($document->file_name);
                    // Delete the document record
                    $document->delete();
                }
        }
    }

    public function getDocuments(string $collectionName = ''): \Illuminate\Database\Eloquent\Collection
    {
        return $this->media()->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            // ->where('collection_name', $collectionName)
            ->get();
    }

    public function getDocumentUrl($media): string
    {
        $filePath = '';
        if (isset($media->file_name)) {
            $filePath = Storage::url($media->file_name);
        }
        return $filePath;
    }
    /*For render image on the PDF*/
    public function getPdfDocumentUrl($media): string
    {
        $filePath = '';
        if (isset($media->file_name)) {
            $filePath = Storage::url($media->file_name);
            return $filePath ? public_path($filePath) : '';
        }
        return '';
    }
}
