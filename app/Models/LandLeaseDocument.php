<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Deletable;

class LandLeaseDocument extends Model
{
    use HasFactory,Deletable;

    protected $table = "erp_land_lease_documents";

    protected $fillable = [
        "lease_id",
        "document_name",
        "file_path",
    ];
    public $referencingRelationships = [
        'lease' => 'lease_id',
    ];
    public function lease()
    {
        return $this->belongsTo(LandLease::class, 'lease_id');
    }

    public static function createUpdateDocument($request, $lease)
    {
        try {
            DB::beginTransaction();
            if ($request->hasFile('attachments')) {
                //$documentsData = [];

                foreach ($request->file('attachments') as $key => $file) {
                    if ($file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();

                        // Generate a unique filename
                        $filename = Str::uuid() . '.' . $extension;

                        // Store the file
                        $documentPath = $file->storeAs('lease_documents', $filename, 'public');

                        // Full URL if needed
                        $documentUrl = Storage::url($documentPath);

                        // Prepare data for bulk insert
                        LandLeaseDocument::create([
                            'lease_id' => $lease->id,
                            'document_name' => $request->documentname[$key],
                            'file' => $documentPath,
                        ]);
                    }
                }
                DB::commit();
                // Insert all documents in one query
                //$document = LandLeaseDocument::insert($documentsData);

                return true;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            //dd($e->getMessage());
            return false;
        }
    }
}
