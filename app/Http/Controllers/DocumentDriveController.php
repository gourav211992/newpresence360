<?php

namespace App\Http\Controllers;


use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PPTIOFactory;
use PhpOffice\PhpPresentation\Exception\PhpPresentationException;
use PhpOffice\PhpWord\Exception\Exception as WordException;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\ErpDocumentDriveFile;
use App\Models\ErpDocumentDriveFolder; 
use App\Models\ErpDocumentDriveSharedResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Carbon\Carbon;
use App\Models\AuthUser;

class DocumentDriveController extends Controller
{
    public function index(Request $request)
    {
        $userData = Helper::getAuthenticatedUser();

        // Initialize query for files and folders
        $fileQuery = ErpDocumentDriveFile::where('created_by', $userData->auth_user_id)->whereNull('folder_id');
        $folderQuery = ErpDocumentDriveFolder::where('created_by', $userData->auth_user_id)->whereNull('parent_id');

        if (
            $request->filled('date_range') ||
            $request->filled('file_name') ||
            $request->filled('document_type') ||
            $request->filled('owner') ||
            $request->filled('tag_description') ||
            $request->filled('folder')
        ) {
            $fileQuery = ErpDocumentDriveFile::where('created_by', $userData->auth_user_id);
            $folderQuery = ErpDocumentDriveFolder::where('created_by', $userData->auth_user_id);
    
            if ($request->filled('document_type')) {
                $documentType = $request->input('document_type');
    
                // Map document types to extensions
                $extensions = [
                    // Categories
                    'Image' => ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'],
                    'Video' => ['mp4', 'avi', 'mkv', 'mov', 'wmv'],
                    'Zip' => ['zip', 'rar', '7z'],
    
                    // Specific Document Types
                    'MS Word' => ['doc', 'docx'],
                    'MS Excel' => ['xls', 'xlsx'],
                ];
    
                if (isset($extensions[$documentType])) {
                    $ext = $extensions[$documentType];
                    if (is_array($ext)) {
                        // Check for multiple extensions
                        $fileQuery->where(function ($query) use ($ext) {
                            foreach ($ext as $e) {
                                $query->orWhere('name', 'like', "%.{$e}");
                            }
                        });
                    } else {
                        // Single extension check
                        $fileQuery->where('name', 'like', "%.{$ext}");
                    }
                }
                if($request->filled('document_type')!=="Folder")
                $folderQuery=[];
            }
        
    
        // Apply filters if present
        if ($request->filled('date_range')) {
            $dateRange = explode(' to ', $request->input('date_range'));
            if (count($dateRange) === 2) {
                $startDate = Carbon::parse($dateRange[0]);
                $endDate = Carbon::parse($dateRange[1]);
                $fileQuery->whereBetween('created_at', [$startDate, $endDate]);
                $folderQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        if ($request->filled('file_name')) {
            $fileName = $request->input('file_name');
            $fileQuery->where('name', 'like', "%{$fileName}%");
            $folderQuery->where('name', 'like', "%{$fileName}%");
        
        }


        if ($request->filled('owner')) {
            $owner = $request->input('owner');
            $fileQuery->where('created_by', $owner);
            $folderQuery->where('created_by', $owner);
        }

        if ($request->filled('tag_description')) {
            $tagDescription = $request->input('tag_description');
            $fileQuery->where('tags', 'like', "%{$tagDescription}%");
            $folderQuery->where('tags', 'like', "%{$tagDescription}%");

        }

        if ($request->filled('folder')) {
            $folderId = $request->input('folder');
            $fileQuery->where('folder_id', $folderId);
            $folderQuery->where('id', $folderId);
        }
    }

        // Fetch filtered files and folders
        $files = $fileQuery?$fileQuery->get():[];
        $folders = $folderQuery?$folderQuery->get():[];

        // Add type attributes
        if($folders)
        $folders->each(function ($folder) {
            $folder->type = 'folder'; // Add a type 'folder' for folder items
        });
        
        if($files)
        $files->each(function ($file) {
            $file->type = 'file'; // Add a type 'file' for file items
        });

        if($folders)
        // Merge files and folders
        $items = $folders->merge($files)->sortBy('name');
        else
        $items=$files;
        

        // Fetch all folders and users for filter dropdowns
        $all_folders = ErpDocumentDriveFolder::where('created_by', $userData->auth_user_id)->get();
        $users = Helper::getOrgWiseUserAndEmployees($userData->organization_id)
        ->reject(fn($user) => in_array($user->id, [$userData->auth_user_id])) // Exclude specified IDs
        ->values();    
        $f_folders = ErpDocumentDriveFolder::where('created_by', $userData->auth_user_id)->get();
       

        return view('document-drive.index')->with([
            'all_folders' => $all_folders,
            'filter_folders'=>$f_folders,
            'files' => $files,
            'folders' => $folders,
            'items' => $items,
            'users' => $users,
            'filters' => $request->all(), // Pass filters back to the view
        ]);
    }

    public function downloadZip(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'selected_items' => 'required|array',
                'selected_items.*' => 'string', // Format: "type|id"
            ]);

            $selectedItems = $validatedData['selected_items'];

            $zip = new ZipArchive;
            $zipFileName = 'download-' . time() . '.zip';
            $zipFilePath = storage_path($zipFileName);

            if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
                foreach ($selectedItems as $item) {
                    [$type, $id] = explode('|', $item);

                    if ($type === 'file') {
                        $file = ErpDocumentDriveFile::find($id);
                        if ($file && file_exists(storage_path('app/public/' . $file->path))) {
                            $zip->addFile(storage_path('app/public/' . $file->path), $file->name);
                        }
                    } elseif ($type === 'folder') {
                        $folder = ErpDocumentDriveFolder::find($id);
                        if ($folder) {
                            $this->addFolderToZip($folder, $zip);
                        }
                    }
                }
                $zip->close();

                // Return the ZIP file response
                return response()->download($zipFilePath)->deleteFileAfterSend(true);
            } else {
                throw new \Exception('Unable to create ZIP file.');
            }
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Validation error.',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Handle general errors
            return response()->json([
                'error' => 'An error occurred.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recursively add folder contents to ZIP.
     */
    private function addFolderToZip($folder, $zip, $parentPath = '')
    {
        $folderPath = $parentPath ? $parentPath . '/' . $folder->name : $folder->name;

        $files = ErpDocumentDriveFile::where('folder_id', $folder->id)->get();
        foreach ($files as $file) {
            if (file_exists(storage_path('app/public/' . $file->path))) {
                $zip->addFile(storage_path('app/public/' . $file->path), $folderPath . '/' . $file->name);
            }
        }

        $subfolders = ErpDocumentDriveFolder::where('parent_id', $folder->id)->get();
        foreach ($subfolders as $subfolder) {
            $this->addFolderToZip($subfolder, $zip, $folderPath);
        }
    }

    public function downloadFolderAsZip($folderId)
    {
        $folder = ErpDocumentDriveFolder::find($folderId);
        if ($folder->files->isEmpty()) {
            Log::error("The folder {$folder->id} is empty. No files to include in the ZIP.");
            return response()->json(['error' => 'The folder is empty. No files to download.'], 400);
        }
        $zipFilePath = storage_path('app/public/' . $folder->name . '.zip');
        $directoryPath = storage_path('app/public/');

        if (!is_writable($directoryPath)) {
            Log::error("The directory is not writable: {$directoryPath}");
            return response()->json(['error' => 'Directory is not writable.'], 500);
        }
        // Create a new ZIP file
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            Log::error("Unable to create ZIP file at {$zipFilePath}");
            return response()->json(['error' => 'Unable to create ZIP file.'], 500);
        }

        // Add files to the ZIP
        foreach ($folder->files as $file) {
            $filePath = storage_path('app/public/' . $file->path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $file->path);
                Log::info("Added file to ZIP: {$filePath}");
            } else {
                Log::error("File does not exist: {$filePath}");
            }
        }

        // Close the ZIP file
        $zip->close();

        // If ZIP file is created successfully, return the file for download
        if (file_exists($zipFilePath)) {
            Log::info("ZIP file created successfully at: {$zipFilePath}");
            return response()->download($zipFilePath);
        } else {
            Log::error("Failed to create ZIP file.");
            return response()->json(['error' => 'Failed to create ZIP file.'], 500);
        }
    }

    public function downloadFolder($id)
    {

        // Get the folder and its files
        $folder = ErpDocumentDriveFolder::findOrFail($id);

        // Prepare a temporary file for the ZIP
        $zipFileName = $folder->name . '.zip';
        $zipFilePath = storage_path($zipFileName);
        if ($folder->files->isNotEmpty()) {
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($folder->files as $file) {
                    $filePath = storage_path('app/public/' . $file->path);
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, $folder->name . '/' . $file->name);
                    }
                }
                if ($folder->children->isNotEmpty()) {
                    $this->addSubfolderFilesToZip($folder, $zip);
                }
                $zip->close();
                return response()->download($zipFilePath)->deleteFileAfterSend(true);
            } else {
                return back()->with('error', 'Failed to create ZIP file.');
            }
        } else {
            return back()->with('error', 'The folder is empty. No files to download.'); // Return an error response

        }
    }

    // Helper function to add files from subfolders
    public function addSubfolderFilesToZip($folder, $zip, $parentPath = '')
    {
        $currentPath = $parentPath . $folder->name . '/';

        foreach ($folder->children as $subfolder) {
            // Add files in the current subfolder
            foreach ($subfolder->files as $file) {
                $filePath = storage_path('app/public/' . $file->path); // Adjust as per storage setup
                $zip->addFile($filePath, $currentPath . $subfolder->name . '/' . basename($file->path));
            }

            // Recursively process nested folders
            if ($subfolder->children->isNotEmpty()) {
                $this->addSubfolderFilesToZip($subfolder, $zip, $currentPath . $subfolder->name . '/');
            }
        }
    }
    public function show(string $id,Request $request)
    {
        $userData = Helper::getAuthenticatedUser();

        $fileQuery = ErpDocumentDriveFile::where('folder_id', $id)
            ->where('created_by', $userData->auth_user_id);
        $folderQuery = ErpDocumentDriveFolder::where('parent_id', $id)
            ->where('created_by', $userData->auth_user_id);

        // Apply filters if present
        if (
            $request->filled('date_range') ||
            $request->filled('file_name') ||
            $request->filled('document_type') ||
            $request->filled('owner') ||
            $request->filled('tag_description') ||
            $request->filled('folder')
        ) {
            $fileQuery = ErpDocumentDriveFile::where('created_by', $userData->auth_user_id);
            $folderQuery = ErpDocumentDriveFolder::where('created_by', $userData->auth_user_id);
    
            if ($request->filled('document_type')) {
                $documentType = $request->input('document_type');
    
                // Map document types to extensions
                $extensions = [
                    // Categories
                    'Image' => ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'],
                    'Video' => ['mp4', 'avi', 'mkv', 'mov', 'wmv'],
                    'Zip' => ['zip', 'rar', '7z'],
    
                    // Specific Document Types
                    'MS Word' => ['doc', 'docx'],
                    'MS Excel' => ['xls', 'xlsx'],
                ];
    
                if (isset($extensions[$documentType])) {
                    $ext = $extensions[$documentType];
                    if (is_array($ext)) {
                        // Check for multiple extensions
                        $fileQuery->where(function ($query) use ($ext) {
                            foreach ($ext as $e) {
                                $query->orWhere('name', 'like', "%.{$e}");
                            }
                        });
                    } else {
                        // Single extension check
                        $fileQuery->where('name', 'like', "%.{$ext}");
                    }
                }
                if($request->filled('document_type')!=="Folder")
                $folderQuery=[];
            }
        
    
        // Apply filters if present
        if ($request->filled('date_range')) {
            $dateRange = explode(' to ', $request->input('date_range'));
            if (count($dateRange) === 2) {
                $startDate = Carbon::parse($dateRange[0]);
                $endDate = Carbon::parse($dateRange[1]);
                $fileQuery->whereBetween('created_at', [$startDate, $endDate]);
                $folderQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        if ($request->filled('file_name')) {
            $fileName = $request->input('file_name');
            $fileQuery->where('name', 'like', "%{$fileName}%");
            $folderQuery->where('name', 'like', "%{$fileName}%");
        
        }


        if ($request->filled('owner')) {
            $owner = $request->input('owner');
            $fileQuery->where('created_by', $owner);
            $folderQuery->where('created_by', $owner);
        }

        if ($request->filled('tag_description')) {
            $tagDescription = $request->input('tag_description');
            $fileQuery->where('tags', 'like', "%{$tagDescription}%");
            $folderQuery->where('tags', 'like', "%{$tagDescription}%");

        }

        if ($request->filled('folder')) {
            $folderId = $request->input('folder');
            $fileQuery->where('folder_id', $folderId);
            $folderQuery->where('id', $folderId);
        }
    }
        // Fetch filtered files and folders
        $files = $fileQuery?$fileQuery->get():[];
        $folders = $folderQuery?$folderQuery->get():[];


        if($folders)
        $folders->each(function ($folder) {
            $folder->type = 'folder'; // Add a type 'folder' for folder items
        });

        if($files)
        $files->each(function ($file) {
            $file->type = 'file'; // Add a type 'file' for file items
        });

        $parent_folder = ErpDocumentDriveFolder::find($id);

        $parentFolders = $this->getParentFolders($parent_folder);
        if($folders)
        $items = $folders->merge($files)->sortBy('name');
        else  
        $items=$files;
        $all_folders = ErpDocumentDriveFolder::where('created_by', $userData->auth_user_id)
        ->where('id','!=',$id)->get();
        $f_folders = ErpDocumentDriveFolder::where('created_by', $userData->auth_user_id)->get();
       
        $users = Helper::getOrgWiseUserAndEmployees($userData->organization_id)
        ->reject(fn($user) => in_array($user->id, [$userData->auth_user_id])) // Exclude specified IDs
        ->values();  

        return view('document-drive.index')->with([
            'parentFolders' => $parentFolders,
            'all_folders' => $all_folders,
            'filter_folders' =>$f_folders,
            'users' => $users,
            'files' => $files,
            'folders' => $folders,
            'parent' => $id,
            'parent_folder' => $parent_folder,
            'items' => $items
        ]);
    }
    public function sharedWithMe(Request $request, $id = null)
    {
        $userData = Helper::getAuthenticatedUser();

        // Get shared resources for the current user
        $sharedResources = ErpDocumentDriveSharedResource::where('shared_with', $userData->auth_user_id)->get();

        // Filter files and folders from shared resources
        $sharedFileIds = $sharedResources->where('entity_type', 'file')->pluck('entity_id');
        $sharedFolderIds = $sharedResources->where('entity_type', 'folder')->pluck('entity_id');

        // Base queries for files and folders
        $fileQuery = ErpDocumentDriveFile::whereIn('id', $sharedFileIds);
        $folderQuery = ErpDocumentDriveFolder::whereIn('id', $sharedFolderIds);

        // Apply folder filter
        if ($request->filled('folder_name')) {
            $folderQuery->where('name', 'like', '%' . $request->input('folder_name') . '%');
        }

        // Apply file filter
        if ($request->filled('file_name')) {
            $fileQuery->where('name', 'like', '%' . $request->input('file_name') . '%');
        }

        // Apply file type filter (extension-based)
        if ($request->filled('document_type')) {
            if ($request->filled('document_type')) {
                $documentType = $request->input('document_type');
                $extensions = [
                    'Image' => ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'],
                    'Video' => ['mp4', 'avi', 'mkv', 'mov', 'wmv'],
                    'Zip' => ['zip', 'rar', '7z'],
                    'MS Word' => ['doc', 'docx'],
                    'MS Excel' => ['xls', 'xlsx'],
                ];

                if (isset($extensions[$documentType])) {
                    $fileExtensions = $extensions[$documentType];
                    $fileQuery->where(function ($query) use ($fileExtensions) {
                        foreach ((array)$fileExtensions as $ext) {
                            $query->orWhere('name', 'like', "%.{$ext}");
                        }
                    });
                }
            }
        }

        // Apply date filter
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $fileQuery->whereBetween('created_at', [$request->input('date_from'), $request->input('date_to')]);
        }
        if ($request->filled('owner')) {
            $owner = $request->input('owner');
            $fileQuery->where('created_by', $owner);
            $folderQuery->where('created_by', $owner);
        }

        if ($request->filled('tag_description')) {
            $tagDescription = $request->input('tag_description');
            $fileQuery->where('tags', 'like', "%{$tagDescription}%");
            $folderQuery->where('tags', 'like', "%{$tagDescription}%");

        }

        if ($request->filled('folder')) {
            $folderId = $request->input('folder');
            $fileQuery->where('folder_id', $folderId);
            $folderQuery->where('id', $folderId);
        }

        // Fetch files and folders based on filters
        if ($id != null) {
            $files = $fileQuery->where('folder_id', $id)->get();
            $folders = $folderQuery->where('parent_id', $id)->get();
        } else {
            $files = $fileQuery->get();
            $folders = $folderQuery->get();
        }

        // Assign types for proper identification
        $folders->each(function ($folder) {
            $folder->type = 'folder';
        });

        $files->each(function ($file) {
            $file->type = 'file';
        });

        // Merge and sort items
        $items = $folders->merge($files)->sortBy('name');

        // Get all folders and users
        $all_folders = ErpDocumentDriveFolder::where('created_by', $userData->auth_user_id)->get();

        $users = Helper::getOrgWiseUserAndEmployees($userData->organization_id)
        ->reject(fn($user) => in_array($user->id, [$userData->auth_user_id])) // Exclude specified IDs
        ->values();  
        // If a specific folder ID is passed, get its parent folder details
        if ($id != null) {
            $parent_folder = ErpDocumentDriveFolder::find($id);
            $parentFolders = $this->getSharedFolders($parent_folder);

            return view('document-drive.share-with-me')->with([
                'parentFolders' => $parentFolders,
                'all_folders' => $all_folders,
                'users' => $users,
                'files' => $files,
                'folders' => $folders,
                'parent' => $id,
                'parent_folder' => $parent_folder,
                'items' => $items
            ]);
        } else {
            return view('document-drive.share-with-me')->with([
                'all_folders' => $all_folders,
                'users' => $users,
                'files' => $files,
                'folders' => $folders,
                'parent' => $id,
                'items' => $items
            ]);
        }
    }

    // Helper method to get file extensions based on the document type
    private function getFileExtensionsByType($type)
    {
        $extensions = [
            'Image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp'],
            'Video' => ['mp4', 'mkv', 'avi', 'mov'],
            'Zip' => ['zip', 'rar', 'tar', 'gz'],
            'MS Word' => ['doc', 'docx'],
            'MS Excel' => ['xls', 'xlsx'],
            // Add more types if necessary
        ];

        return isset($extensions[$type]) ? $extensions[$type] : [];
    }

    public function sharedDrive(Request $request,$id=null)
    {
        $userData = Helper::getAuthenticatedUser();

        // Get shared resources for the current user
        $sharedResources = ErpDocumentDriveSharedResource::where('shared_by', $userData->auth_user_id)->get();

        // Filter files and folders from shared resources
        $sharedFileIds = $sharedResources->where('entity_type', 'file')->pluck('entity_id');
        $sharedFolderIds = $sharedResources->where('entity_type', 'folder')->pluck('entity_id');

        $fileQuery = ErpDocumentDriveFile::whereIn('id', $sharedFileIds);
        $folderQuery = ErpDocumentDriveFolder::whereIn('id', $sharedFolderIds);

        // If a specific folder ID is provided, filter by that folder
        if ($id != null) {
            $fileQuery->where('folder_id', $id);
            $folderQuery->where('parent_id', $id);
        } else {
            $fileQuery->whereIn('id', $sharedFileIds);
            $folderQuery->whereIn('id', $sharedFolderIds);
        }

        // Apply filters based on request inputs
        if ($request->filled('file_name')) {
            $fileName = $request->input('file_name');
            $fileQuery->where('name', 'like', "%{$fileName}%");
            $folderQuery->where('name', 'like', "%{$fileName}%");
        }

        if ($request->filled('document_type')) {
            $documentType = $request->input('document_type');
            $extensions = [
                'Image' => ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'],
                'Video' => ['mp4', 'avi', 'mkv', 'mov', 'wmv'],
                'Zip' => ['zip', 'rar', '7z'],
                'MS Word' => ['doc', 'docx'],
                'MS Excel' => ['xls', 'xlsx'],
            ];

            if (isset($extensions[$documentType])) {
                $fileExtensions = $extensions[$documentType];
                $fileQuery->where(function ($query) use ($fileExtensions) {
                    foreach ((array)$fileExtensions as $ext) {
                        $query->orWhere('name', 'like', "%.{$ext}");
                    }
                });
            }
            
        }

        if ($request->filled('date_range')) {
            $dateRange = explode(' to ', $request->input('date_range'));
            if (count($dateRange) === 2) {
                $startDate = $dateRange[0];
                $endDate = $dateRange[1];
                $fileQuery->whereBetween('created_at', [$startDate, $endDate]);
                $folderQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        if ($request->filled('folder_name')) {
            $folderName = $request->input('folder_name');
            $folderQuery->where('name', 'like', "%{$folderName}%");
        }
        if ($request->filled('owner')) {
            $owner = $request->input('owner');
            $fileQuery->where('created_by', $owner);
            $folderQuery->where('created_by', $owner);
        }

        if ($request->filled('tag_description')) {
            $tagDescription = $request->input('tag_description');
            $fileQuery->where('tags', 'like', "%{$tagDescription}%");
            $folderQuery->where('tags', 'like', "%{$tagDescription}%");

        }

        if ($request->filled('folder')) {
            $folderId = $request->input('folder');
            $fileQuery->where('folder_id', $folderId);
            $folderQuery->where('id', $folderId);
        }

        // Fetch the filtered files and folders
        $files = $fileQuery->get();
        $folders = $folderQuery->get();

        // Assign types for proper identification
        $folders->each(function ($folder) {
            $folder->type = 'folder';
        });

        $files->each(function ($file) {
            $file->type = 'file';
        });

        // Merge and sort
        $items = $folders->merge($files)->sortBy('name');
        $all_folders = ErpDocumentDriveFolder::where('created_by', $userData->auth_user_id)->get();
        $users = Helper::getOrgWiseUserAndEmployees($userData->organization_id)
        ->reject(fn($user) => in_array($user->id, [$userData->auth_user_id])) // Exclude specified IDs
        ->values(); 
         
        if ($id != null) {
            $parent_folder = ErpDocumentDriveFolder::find($id);
            $parentFolders = $this->getSharedFolders($parent_folder);

            return view('document-drive.shared-drive')->with([
                'parentFolders' => $parentFolders,
                'all_folders' => $all_folders,
                'users' => $users,
                'files' => $files,
                'folders' => $folders,
                'parent' => $id,
                'parent_folder' => $parent_folder,
                'items' => $items
            ]);
        } else {
            return view('document-drive.shared-drive')->with([
                'all_folders' => $all_folders,
                'users' => $users,
                'files' => $files,
                'folders' => $folders,
                'parent' => $id,
                'items' => $items
            ]);
        }
    }


public function upload(Request $request, $parentId = null)
{
    Log::info('Upload method started.', ['parentId' => $parentId]); // Log method start
    $uploadedFiles = [];
    $skippedFiles = [];
    $validator = [];

    $messages = [
        'files.*.mimes' => 'Only PDF, Word, Excel, Image, Text, and Video files are allowed.',
        'files.*.max' => 'Each file must not exceed 10 MB.',
        'files.file' => 'Each item must be a valid file.',
        'files.mimes' => 'Only PDF, Word, Excel, Image, Text, and Video files are allowed.',
        'files.max' => 'Each file must not exceed 10 MB.',
    ];

    try {
        Log::info('Starting validation of uploaded files.');

        // Perform the validation
        $request->validate([
            'files' => 'required|array', // Ensure that the 'files' field is always an array
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,gif,mp4|max:10240', // Max file size of 10MB for each file
        ], $messages);

        Log::info('Validation passed successfully.');

    } catch (ValidationException $e) {
        Log::error('Validation failed.', ['errors' => $e->errors()]);

        $errors = $e->errors();

        // Extract filenames from the request
        $filenames = [];
        if ($request->has('files')) {
            foreach ($request->file('files') as $file) {
                $filenames[] = $file->getClientOriginalName();
            }
        }

        Log::info('Extracted filenames for validation errors.', ['filenames' => $filenames]);

        return response()->json([
            'message' => $e->errors(),
            'uploaded_files' => $uploadedFiles,
            'skipped_files' => $skippedFiles,
            'errors' => $filenames
        ], 200);
    }

    $userData = Helper::getAuthenticatedUser();

    foreach ($request->file('files') as $file) {
        try {
            if ($file->isValid()) {
                Log::info('Processing valid file.', ['filename' => $file->getClientOriginalName()]);

                $fileName = $file->getClientOriginalName();

                // Check for existing file
                $existingFile = ErpDocumentDriveFile::where('name', $fileName)
                    ->when($parentId, function ($query) use ($parentId) {
                        $query->where('folder_id', $parentId);
                    }, function ($query) use ($userData) {
                        $query->whereNull('folder_id')
                            ->where('created_by', $userData->auth_user_id);
                    })
                    ->first();

                if ($existingFile) {
                    Log::warning('File already exists and will be skipped.', ['filename' => $fileName]);
                    $skippedFiles[] = $fileName;
                    continue;
                }

                // Store the file
                $path = $file->store('document-drive', 'public');
                Log::info('File stored successfully.', ['filename' => $fileName, 'path' => $path]);

                // Save file record in the database
                ErpDocumentDriveFile::create([
                    'name' => $fileName,
                    'folder_id' => $parentId,
                    'path' => $path,
                    'organization_id' => $userData->organization_id,
                    'created_by' => $userData->auth_user_id,
                    'created_by_type' => $userData->authenticable_type,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);

                $uploadedFiles[] = $fileName;
            } else {
                Log::error('File is not valid.', ['filename' => $file->getClientOriginalName()]);
                $validator[] = $file->getClientOriginalName();
            }
        } catch (\Exception $ex) {
            Log::error('Error while processing a file.', [
                'filename' => $file->getClientOriginalName(),
                'error' => $ex->getMessage()
            ]);
            $validator[] = $file->getClientOriginalName();
        }
    }

    Log::info('File upload process completed.', [
        'uploadedFiles' => $uploadedFiles,
        'skippedFiles' => $skippedFiles,
        'errors' => $validator
    ]);

    return response()->json([
        'message' => 'File upload completed.',
        'uploaded_files' => $uploadedFiles,
        'skipped_files' => $skippedFiles,
        'errors' => $validator
    ], 200);
}

    public function uploadFolder(Request $request, $parentId = null)
    {

        $uploadedFiles = [];
        $skippedFiles = [];
        $validator = [];


        $messages = [
            'files.*.mimes' => 'Only ZIP, RAR, and 7Z archive files are allowed.',
            'files.*.max' => 'Each file must not exceed 10 MB.',
            'files.file' => 'Each item must be a valid file.',
            'files.mimes' => 'Only ZIP, RAR, and 7Z archive files are allowed.',
            'files.max' => 'Each file must not exceed 10 MB.',
        ];

        try {
            // Perform the validation
            $request->validate([
                'files' => 'required|array', // Ensure that the 'files' field is always an array
                'files.*' => 'file|mimes:zip,rar,7z|max:10240', // Allow ZIP, RAR, 7Z files and max file size of 10MB
            ], $messages);

            // If validation passes, continue with the logic
            // Example: Handle the file upload logic here
            // ...

        } catch (ValidationException $e) {

            $errors = $e->errors();

            // Extract filenames from the request
            $filenames = [];
            if ($request->has('files')) {
                foreach ($request->file('files') as $file) {
                    $filenames[] = $file->getClientOriginalName();
                }
            }

            // Add filenames to error messages
            $customErrors = [];
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $fileIndex = preg_replace('/[^0-9]/', '', $field); // Extract index from field name (e.g., files.0)
                    $filename = $filenames[$fileIndex] ?? 'unknown file';
                    $customErrors[$field][] = "File '$filename': $message";
                }
            }

            return response()->json([
                'message' => $e->errors(),
                'uploaded_files' => $uploadedFiles,
                'skipped_files' => $skippedFiles,
                'errors' => $filenames
            ], 200);
        }

        $userData = Helper::getAuthenticatedUser(); // Fetch user-related data
        $authUser = Helper::getAuthenticatedUser(); // Fetch authenticated user

        foreach ($request->file('files') as $file) {
            if ($file->isValid()) {
                // Check if the file name already exists in the folder
                $fileName = $file->getClientOriginalName();

                // Check for existing file
                $existingFile = ErpDocumentDriveFile::where('name', $fileName)
                    ->when($parentId, function ($query) use ($parentId) {
                        $query->where('folder_id', $parentId);
                    }, function ($query) use ($userData) {
                        $query->whereNull('folder_id')
                            ->where('created_by', $userData->auth_user_id);
                    })
                    ->first();

                if ($existingFile) {
                    $skippedFiles[] = $fileName;
                    continue; // Skip this file
                }

                // Store the file
                $path = $file->store('document-drive', 'public');

                // Save file record in the database
                ErpDocumentDriveFile::create([
                    'name' => $fileName,
                    'folder_id' => $parentId,
                    'path' => $path,
                    'organization_id' => $authUser->organization_id,
                    'created_by' => $userData->auth_user_id,
                    'created_by_type' => $userData->authenticable_type,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);

                $uploadedFiles[] = $fileName;
            } else $validator[] = $fileName;
        }

        // Return a response indicating the upload results
        return response()->json([
            'message' => 'File upload completed.',
            'uploaded_files' => $uploadedFiles,
            'skipped_files' => $skippedFiles,
            'errors' => $validator
        ], 200);
    }

    public function create_folder(Request $request, $parentId = null)
    {
        $userData = Helper::getAuthenticatedUser();
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        // Check if folder with the same name already exists based on the parent_id
        if ($parentId) {
            // If parent_id is provided, check for folder with same parent_id and name
            $existingFolder = ErpDocumentDriveFolder::where('parent_id', $parentId)
                ->where('name', $request->input('name'))
                ->where('created_by', $userData->auth_user_id)
                ->first();
        } else {
            // If parent_id is null, check for folder with the same name and null parent_id
            $existingFolder = ErpDocumentDriveFolder::whereNull('parent_id')
                ->where('name', $request->input('name'))
                ->where('created_by', $userData->auth_user_id)
                
                ->first();
        }

        if ($existingFolder) {
            // If the folder already exists, return an alert message
            return redirect()->back()->with('error', 'A folder with the same name already exists.');
        }else{

        // Create the folder if not already existing
        $folder = new ErpDocumentDriveFolder();
        $folder->name = $request->input('name');
        $folder->organization_id = Helper::getAuthenticatedUser()->organization_id;
        $folder->created_by = $userData->auth_user_id;
        $folder->created_by_type = $userData->authenticable_type;

        $folder->status = $request->input('status');

        // Set the parent_id if provided
        if ($parentId) {
            $folder->parent_id = $parentId;
        }

        $folder->save();
            // Redirect to document-drive.index with success message
            return redirect()->back()->with('success', 'Folder created successfully.');

    }

    }


    /**
     * Display the specified resource.
     */

    private function getParentFolders($folder)
    {
        $parents = [];

        while ($folder->parent_id !== null) {
            $folder = ErpDocumentDriveFolder::find($folder->parent_id);
            $parents[] = $folder;
        }

        return array_reverse($parents); // Reverse to show from root to current folder
    }

    private function getSharedFolders($folder)
    {
        $parents = [];


        while ($folder->parent_id !== null) {
            $shared = ErpDocumentDriveSharedResource::where('folder_id', $folder->parent_id)->first();
            if ($shared)
                break;
            $folder = ErpDocumentDriveFolder::find($folder->parent_id);
            $parents[] = $folder;
        }


        return array_reverse($parents); // Reverse to show from root to current folder
    }
    public function file_destroy($id)
    {
        try {
            $file = ErpDocumentDriveFile::findOrFail($id);
            $referenceTables = [
                'erp_document_drive_shared_resources' => ['file_id'],
                'erp_document_drive_actions_log' => ['file_id'],

            ];
            $result = $file->deleteWithReferences($referenceTables);

            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'File deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the file: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function folder_destroy($id)
    {

        try {
            $folder = ErpDocumentDriveFolder::findOrFail($id);
            $this->deleteChildFolders($folder);
            $referenceTables = [
                'erp_document_drive_shared_resources' => ['folder_id'],
                'erp_document_drive_actions_log' => ['folder_id'],
                'erp_document_drive_folders' => ['parent_id'],
                'erp_document_drive_files' => ['folder_id'],
            ];
            $result = $folder->deleteWithReferences($referenceTables);

            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'Folder deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the file: ' . $e->getMessage(),
            ], 500);
        }
    }
    private function deleteChildFolders($folder)
    {
        $childFolders = ErpDocumentDriveFolder::where('parent_id', $folder->id)->get();

        foreach ($childFolders as $childFolder) {
            $referenceTables = [
                'erp_document_drive_shared_resources' => ['folder_id'],
                'erp_document_drive_actions_log' => ['folder_id'],
                'erp_document_drive_folders' => ['parent_id'],
                'erp_document_drive_files' => ['folder_id'],
            ];


            // Recursively delete subchild folders
            $this->deleteChildFolders($childFolder);

            // Delete the current child folder
            $childFolder->deleteWithReferences($referenceTables);

        }
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'selected_items' => 'required|array',
            'selected_items.*' => 'string' // Format: "type|id"
        ]);

        $errors = [];
        $successCount = 0;

        foreach ($validated['selected_items'] as $item) {
            [$type, $id] = explode('|', $item);

            try {
                if ($type === 'file') {
                    $this->file_destroy($id); // Call file_destroy
                } elseif ($type === 'folder') {
                    $this->folder_destroy($id); // Call folder_destroy
                }
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = [
                    'type' => $type,
                    'id' => $id,
                    'error' => $e->getMessage()
                ];
            }
        }

        if (count($errors) > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Some items could not be deleted.',
                'success_count' => $successCount,
                'errors' => $errors
            ], 207); // 207 Multi-Status
        }

        return response()->json([
            'status' => true,
            'message' => 'All selected items deleted successfully.',
            'success_count' => $successCount
        ], 200);
    }


    public function download($id)
    {
        // Find the file by ID
        $file = ErpDocumentDriveFile::findOrFail($id);

        // Path where the file is stored in the `public` disk
        $filePath = $file->path;

        // Check if the file exists
        if (!Storage::disk('public')->exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Serve the file for download
        return response()->download(storage_path('app/public/' . $filePath), $file->name);
    }
    public function showFile($id)
    {
        $userData = Helper::getAuthenticatedUser();
        $user = $userData->auth_user_id;

        // Retrieve the file record from the database
        $file = ErpDocumentDriveFile::findOrFail($id);


        // Check if the file belongs to the authenticated user
        if ($file->created_by !== $user) {
            abort(403, 'Unauthorized access to the file.');
        }

        // Construct the full file path
        $filePath = storage_path('app/public/' . $file->path);

        // Check if the file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Get the file extension to handle different file types
        $pathInfo = pathinfo($file->name);
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : null;

        // Handle Excel files (xls, xlsx)
        if (in_array($extension, ['xls', 'xlsx'])) {
            try {
                $spreadsheet = IOFactory::load($filePath);  // Load the Excel file
                $htmlWriter = new Html($spreadsheet);  // Create an HTML writer for the spreadsheet
                $htmlOutput = $htmlWriter->save('php://output');  // Output the HTML content directly

                return response($htmlOutput, 200)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'inline; filename="' . $file->name . '"');
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                abort(500, 'Error reading the Excel file.');
            }
        }

        // Handle Word documents (doc, docx)
        if (in_array($extension, ['doc', 'docx'])) {
            try {
                if ($extension === 'doc') {
                    $phpWord = WordIOFactory::load($filePath, 'MsDoc');  // Load the Word file
                } else {
                    $phpWord = WordIOFactory::load($filePath);  // Load the Word file
                }
                $htmlWriter = WordIOFactory::createWriter($phpWord, 'HTML');  // Convert Word to HTML
                $htmlOutput = $htmlWriter->save('php://output');  // Output the HTML content directly

                return response($htmlOutput, 200)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'inline; filename="' . $file->name . '"');
            } catch (WordException $e) {
                abort(500, 'Error reading the Word file.');
            }
        }

        // Handle PowerPoint files (ppt, pptx)
        if (in_array($extension, ['ppt', 'pptx'])) {
            try {
                $ppt = PPTIOFactory::load($filePath);  // Load the PowerPoint file
                $htmlWriter = PPTIOFactory::createWriter($ppt, 'HTML');  // Convert PPT to HTML
                $htmlOutput = $htmlWriter->save('php://output');  // Output the HTML content directly

                return response($htmlOutput, 200)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'inline; filename="' . $file->name . '"');
            } catch (PhpPresentationException $e) {
                abort(500, message: 'Error reading the PowerPoint file.');
            }
        }

        // For unsupported file types, return them as is
        return response()->file($filePath, [
            'Content-Type' => $file->mime_type,  // Default MIME type for unknown files
            'Content-Disposition' => 'inline; filename="' . $file->name . '"',
        ]);
    }

    public function showFile2($id)
    {
        $userData = Helper::getAuthenticatedUser();
        $user = $userData->auth_user_id;

        // Retrieve the file record from the database
        $file = ErpDocumentDriveFile::findOrFail($id);

        // Check if the file belongs to the authenticated user
        if ($file->created_by !== $user) {
            abort(403, 'Unauthorized access to the file.');
        }

        // Construct the full file path
        $filePath = storage_path('app/public/' . $file->path);

        // Check if the file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Get the file extension to handle different file types
        $pathInfo = pathinfo($file->name);
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : null;

        // For Excel files (XLS, XLSX)
        if (in_array($extension, ['xls', 'xlsx'])) {
            try {
                $spreadsheet = IOFactory::load($filePath);  // Load the Excel file
                $htmlWriter = new Html($spreadsheet);  // Create an HTML writer for the spreadsheet
                $htmlOutput = $htmlWriter->save('php://output');  // Output the HTML content directly

                return response($htmlOutput, 200)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'inline; filename="' . $file->name . '"');
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                abort(500, 'Error reading the Excel file.');
            }
        }

        return response()->file($filePath, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $file->name . '"',
        ]);
    }


    public function rename(Request $request, $parentId = null)
    {
        $userData = Helper::getAuthenticatedUser();
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        if ($request->type == "file") {
            $file = ErpDocumentDriveFile::findOrFail($request->id);
            $ext = pathinfo($file->name, PATHINFO_EXTENSION);
            $name = $request->input('name') . '.' . $ext;
            if ($file->name != $name) {

                // Check if folder with the same name already exists based on the parent_id
                if ($parentId) {
                    // If parent_id is provided, check for folder with same parent_id and name
                    $existingFile = ErpDocumentDriveFile::where('folder_id', $parentId)
                    ->where('created_by', $userData->auth_user_id)
                        ->where('name', $name)
                        ->first();
                } else {
                    // If parent_id is null, check for folder with the same name and null parent_id
                    $existingFile = ErpDocumentDriveFile::whereNull('folder_id')
                        ->where('name', $name)
                        ->where('created_by', $userData->auth_user_id)
                        
                        ->first();
                }

                if ($existingFile) {
                    // If the folder already exists, return an alert message
                    return redirect()->back()->with('error', 'A file with the same name already exists.');
                } else {
                    $file->name = $name;
                    $file->save();

                    return redirect()->back()->with('success', 'File renamed succesfully .');
                }
            } else {
                return redirect()->back()->with('success', 'File renamed succesfully .');
            }
        } else {
            $folder = ErpDocumentDriveFolder::findOrFail($request->id);
            if ($folder->name != $request->input('name')) {
                // Check if folder with the same name already exists based on the parent_id
                if ($parentId) {
                    // If parent_id is provided, check for folder with same parent_id and name
                    $existingFolder = ErpDocumentDriveFolder::where('parent_id', $parentId)
                    ->where('created_by', $userData->auth_user_id)
                        ->where('name', $request->input('name'))
                        ->first();
                } else {
                    // If parent_id is null, check for folder with the same name and null parent_id
                    $existingFolder = ErpDocumentDriveFolder::whereNull('parent_id')
                        ->where('name', $request->input('name'))
                        ->where('created_by', $userData->auth_user_id)
                        
                        ->first();
                }

                if ($existingFolder) {
                    // If the folder already exists, return an alert message
                    return redirect()->back()->with('error', 'A folder with the same name already exists.');
                } else {
                    $folder = ErpDocumentDriveFolder::findOrFail($request->id);
                    $folder->name = $request->name;
                    $folder->save();

                    return redirect()->back()->with('success', 'Folder renamed succesfully .');
                }
            } else {
                return redirect()->back()->with('success', 'Folder renamed succesfully .');
            }
        }
    }
    public function moveFolder(Request $request)
    {
        $userData = Helper::getAuthenticatedUser();
        $sourceId = $request->input('source_id');
        $destinationFolderId = $request->input('destination_folder_id');
        if($destinationFolderId!="root")
            $destination = ErpDocumentDriveFolder::findOrFail($destinationFolderId);
        $sourceType = $request->input('source_type');
        if ($sourceType == "folder") {
            $source = ErpDocumentDriveFolder::findOrFail($sourceId);
        } else {
            $source = ErpDocumentDriveFile::findOrFail($sourceId);
        }

if (isset($destination) && $destination->id) {
            // If parent_id is provided, check for folder with same parent_id and name
            $existingFolder = ErpDocumentDriveFolder::where('parent_id', $destination->id)
            ->where('created_by', $userData->auth_user_id)
                ->where('name', $source->name)
                ->first();
            $existingFile = ErpDocumentDriveFile::where('folder_id', $destination->id)
            ->where('created_by', $userData->auth_user_id)
                ->where('name', $source->name)
                ->first();
        } else {
            // If parent_id is null, check for folder with the same name and null parent_id
            $existingFolder = ErpDocumentDriveFolder::whereNull('parent_id')
                ->where('name', $source->name)
                ->where('created_by', $userData->auth_user_id)->first();
            $existingFile = ErpDocumentDriveFile::whereNull('folder_id')
            ->where('created_by', $userData->auth_user_id)
                ->where('name', $source->name)
                ->first();
        }


        // Fetch source and destination folders from the database
        if ($sourceType == "folder") {
            if ($existingFolder && $existingFolder->count() > 0) {
                return redirect()->back()->with('error', 'A Folder with same name in destination already exist.');
            }else{
                $source->parent_id = $destination->id??null;
                $source->save();
                return redirect()->back()->with('success', 'Folder moved successfully.');

            }
        } else {
            if ($existingFile && $existingFile->count() > 0) {
                return redirect()->back()->with('error', 'A File with same name in destination already exist.');
            }else{
                $source->folder_id = $destination->id ?? null;
                $source->save();
                return redirect()->back()->with('success', 'File moved successfully.');

            }
        }

        // Return success response
    }
    public function moveFolderMultiple(Request $request)
{
    $userData = Helper::getAuthenticatedUser();

    // Validate the request
    $validatedData = $request->validate(
        [
            'selected_items' => 'required|array',
            'selected_items.*' => 'string', // Format: "type|id"
        ],
        [
            'selected_items.required' => 'Please select at least one item to move.',
        ]
    );

    $selectedItems = $validatedData['selected_items'];
    $destinationFolderId = $request->input('destination_folder_id');

    $destination = null;
    if($destinationFolderId!="root")
    $destination = ErpDocumentDriveFolder::findOrFail($destinationFolderId);


    $errors = [];
    $movedItems = 0;

    foreach ($selectedItems as $item) {

        if($destination!=null){

            // Parse "type|id" to get the type and ID
        [$sourceType, $sourceId] = explode('|', $item);

        if ($sourceType === 'folder') {
            $source = ErpDocumentDriveFolder::find($sourceId);
            if (!$source) {
                $errors[] = "Folder with ID $sourceId not found.";
                continue;
            }

            // Check for an existing folder with the same name in the destination
            $existingFolder = ErpDocumentDriveFolder::where('parent_id', $destination->id)
            ->where('created_by', $userData->auth_user_id)
                ->where('name', $source->name)
                ->first();

            if ($existingFolder) {
                $errors[] = "A folder named '{$source->name}' already exists in the destination";
                continue;
            }

            // Move the folder
            $source->parent_id = $destination->id;
            $source->save();
            $movedItems++;
        } elseif ($sourceType === 'file') {
            $source = ErpDocumentDriveFile::find($sourceId);
            if (!$source) {
                $errors[] = "File with ID $sourceId not found.";
                continue;
            }

            // Check for an existing file with the same name in the destination
            $existingFile = ErpDocumentDriveFile::where('folder_id', $destination->id)
            ->where('created_by', $userData->auth_user_id)
                ->where('name', $source->name)
                ->first();

            if ($existingFile) {
                $errors[] = "A file named '{$source->name}' already exists in the destination.";
                continue;
            }

            // Move the file
            $source->folder_id = $destination->id;
            $source->save();
            $movedItems++;
        } else {
            $errors[] = "Invalid item type for '$item'.";
        }
    }else{  // Parse "type|id" to get the type and ID
        [$sourceType, $sourceId] = explode('|', $item);

        if ($sourceType === 'folder') {
            $source = ErpDocumentDriveFolder::find($sourceId);
            if (!$source) {
                $errors[] = "Folder with ID $sourceId not found.";
                continue;
            }

            // Check for an existing folder with the same name in the destination
            $existingFolder = ErpDocumentDriveFolder::whereNull('parent_id')
            ->where('created_by', $userData->auth_user_id)
                ->where('name', $source->name)
                ->first();

            if ($existingFolder) {
                $errors[] = "A folder named '{$source->name}' already exists in the destination.";
                continue;
            }

            // Move the folder
            $source->parent_id = null;
            $source->save();
            $movedItems++;
        } elseif ($sourceType === 'file') {
            $source = ErpDocumentDriveFile::find($sourceId);
            if (!$source) {
                $errors[] = "File with ID $sourceId not found.";
                continue;
            }

            // Check for an existing file with the same name in the destination
            $existingFile = ErpDocumentDriveFile::whereNull('folder_id')
            ->where('created_by', $userData->auth_user_id)
                ->where('name', $source->name)
                ->first();

            if ($existingFile) {
                $errors[] = "A file named '{$source->name}' already exists in the destination. $destinationFolderId";
                continue;
            }

            // Move the file
            $source->folder_id = null;
            $source->save();
            $movedItems++;
        } else {
            $errors[] = "Invalid item type for '$item'.";
        }
    }
    }

    // Prepare response message
    $response = [
        'success' => true,
        'message' => $movedItems!=0? $movedItems."item(s) moved successfully in .":"",
        'errors' => $errors,
    ];

    return response()->json($response);
}

    public function shareMultiple(Request $request)
    {
        $userData = Helper::getAuthenticatedUser();

        // Validate the request
        $validatedData = $request->validate(
            [
                'selected_users' => 'required|array', // List of user IDs to share with
                'selected_items' => 'required|array', // List of items to share in the "type|id" format
                'selected_items.*' => 'string', // Ensure each item is a string
            ],
            [
                'selected_users.required' => 'Please select at least one user to share with.',
                'selected_users.array' => 'The selected users must be an array.',
                'selected_items.required' => 'Please select at least one item to share.',
                'selected_items.array' => 'The selected items must be an array.',
            ]
        );

        $userIds = $validatedData['selected_users'];
        $selectedItems = $validatedData['selected_items'];

        $alreadyShared = []; // To track users already shared with items
        $newShares = [];     // To track newly shared items for users

        foreach ($selectedItems as $item) {
            // Split "type|id" into type and ID
            [$shareType, $shareId] = explode('|', $item);

            foreach ($userIds as $userId) {
                // Check if this item is already shared with this user
                $existingShare = ErpDocumentDriveSharedResource::where([
                    'entity_type' => $shareType,
                    'entity_id' => $shareId,
                    'shared_with' => $userId,
                ])->first();

                if ($existingShare) {
                    $alreadyShared[] = ['type' => $shareType, 'id' => $shareId, 'user_id' => $userId];
                } else {
                    // Create a new sharing entry
                    $newShareData = [
                        'entity_type' => $shareType,
                        'entity_id' => $shareId,
                        'shared_with' => $userId,
                        'shared_by' => $userData->auth_user_id,
                        'permissions' => json_encode(['read', 'write']), // Example permissions
                    ];

                    if ($shareType === 'folder') {
                        $newShareData['folder_id'] = $shareId;
                    } elseif ($shareType === 'file') {
                        $newShareData['file_id'] = $shareId;
                    }

                    ErpDocumentDriveSharedResource::create($newShareData);
                    $newShares[] = ['type' => $shareType, 'id' => $shareId, 'user_id' => $userId];
                }
            }
        }

        // Prepare feedback messages
        $message = '';
        if (!empty($newShares)) {
            $message .= count($newShares) . ' new item(s) successfully shared.';
        }
        if (!empty($alreadyShared)) {
            $message .= ' ' . count($alreadyShared) . ' item(s) were already shared.';
        }

        return response()->json([
            'message' => trim($message),
        ], 200);
    }

    public function share(Request $request)
    {
        $userData = Helper::getAuthenticatedUser();
        $validatedData = $request->validate(
            [
                'share_id' => 'required|integer', // ID of the document or folder being shared
                'share_type' => 'required|string', // Type: "folder" or "file"
                'users' => 'required|array', // List of user IDs to share with
            ],
            [
                'share_id.required' => 'The item to be shared is required.',
                'share_id.integer' => 'The shared item ID must be a valid number.',
                'share_type.required' => 'The type of the item (folder or file) is required.',
                'share_type.string' => 'The share type must be a valid string.',
                'users.required' => 'Please select at least one user to share with.',
                'users.array' => 'The selected users must be in an array format.',
            ]
        );

        $shareId = $validatedData['share_id'];
        $shareType = $validatedData['share_type'];
        $userIds = $validatedData['users'];

        $alreadyShared = []; // To track users already shared with
        $newShares = [];     // To track users being newly shared with

        foreach ($userIds as $userId) {
            $existingShare = ErpDocumentDriveSharedResource::where([
                'entity_type' => $shareType,
                'entity_id' => $shareId,
                'shared_with' => $userId,
            ])->first();

            if ($existingShare) {
                $alreadyShared[] = $userId; // Add to the already shared list
            } else {
                if ($shareType == "folder") {
                    ErpDocumentDriveSharedResource::create([
                        'entity_type' => $shareType,
                        'entity_id' => $shareId,
                        'folder_id' => $shareId,
                        'shared_with' => $userId,
                        'shared_by' => $userData->auth_user_id,
                        'permissions' => json_encode(['read', 'write']), // Example permissions
                    ]);

                    $newShares[] = $userId; // Add to the newly shared list
                } else {
                    ErpDocumentDriveSharedResource::create([
                        'entity_type' => $shareType,
                        'entity_id' => $shareId,
                        'file_id' => $shareId,
                        'shared_with' => $userId,
                        'shared_by' => $userData->auth_user_id,
                        'permissions' => json_encode(['read', 'write']), // Example permissions
                    ]);

                    $newShares[] = $userId; // Add to the newly shared list
                }
                // Create a new sharing entry

            }
        }

        // Prepare feedback messages
        $message = '';
        if (!empty($newShares)) {
            $message .= count($newShares) . ' user(s) successfully shared.';
        }
        if (!empty($alreadyShared)) {
            $message .= ' ' . count($alreadyShared) . ' user(s) were already shared.';
        }

        return redirect()->back()->with('success', trim($message));
    }
    public function addTagsToItems(Request $request)
    {
        $validated = $request->validate([
            'tags' => 'required|string', // Comma-separated tags
            'page' => 'required|string', // Page context
            'selected_items' => 'required|array', // Array of items
            'selected_items.*' => 'string' // Format: "type|id"
        ]);

        $tags = explode(',', $validated['tags']); // Convert tags into an array

        foreach ($validated['selected_items'] as $item) {
            [$type, $id] = explode('|', $item);

            if ($type === 'file') {
                $file = ErpDocumentDriveFile::find($id);
                if ($file) {
                    // Get existing tags
                    $existingTags = $file->tags ?? [];

                    // Update tags only for the given page
                    $existingTags[$validated['page']] = array_unique(
                        array_merge($existingTags[$validated['page']] ?? [], $tags)
                    );

                    $file->tags = $existingTags;
                    $file->save();
                }
            } elseif ($type === 'folder') {
                $folder = ErpDocumentDriveFolder::find($id);
                if ($folder) {
                    // Get existing tags
                    $existingTags = $folder->tags ?? [];

                    // Update tags only for the given page
                    $existingTags[$validated['page']] = array_unique(
                        array_merge($existingTags[$validated['page']] ?? [], $tags)
                    );

                    $folder->tags = $existingTags;
                    $folder->save();
                }
            }
        }

        return response()->json(['message' => 'Tags added successfully.']);
    }
}
