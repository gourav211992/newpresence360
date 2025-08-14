<?php

namespace App\Lib\Services;

use App\Models\InspChecklist;

/**
 * Service class for handling ERP Inspection Checklists.
 */
class ErpInspChecklistService
{
    // Properties to store contextual data for the checklist
    protected $type;      // Type of inspection checklist
    protected $headerId;  // Reference to the main inspection record
    protected $itemId;    // Reference to the inspected item
    protected $detailId;  // Reference to the inspection detail record

    /**
     * Constructor to initialize checklist context.
     *
     * @param string $type     The type/category of the inspection checklist
     * @param int    $headerId The ID of the inspection header record
     * @param int    $detailId The ID of the inspection detail record
     * @param int    $itemId   The ID of the inspection item
     */
    public function __construct(string $type, int $headerId, int $detailId, int $itemId)
    {
        $this->type = $type;
        $this->headerId = $headerId;
        $this->detailId = $detailId;
        $this->itemId = $itemId;
    }

    /**
     * Stores inspection checklist records in the database.
     *
     * Iterates over each provided checklist data entry and creates
     * a new InspChecklist record with contextual IDs and parameter details.
     *
     * @param  object $inspectionData An iterable object containing checklist entries
     * @return bool   Returns true after all records are saved
     */
    public function sync(array $inspectionData)
    {
        foreach ($inspectionData as $key => $data) {
            // Create a new checklist record instance
            // $inspChecklist = new InspChecklist();
            $inspChecklist = InspChecklist::find($data['insp_checklist_id']) ?? new InspChecklist();

            // Assign contextual information
            $inspChecklist->type = $this->type;
            $inspChecklist->header_id = $this->headerId;
            $inspChecklist->detail_id = $this->detailId;
            $inspChecklist->item_id = $this->itemId;

            // Assign checklist-specific details from the input data
            $inspChecklist->checklist_id = $data['checkList_id'];
            $inspChecklist->checklist_name = $data['checkList_name'];
            $inspChecklist->checklist_detail_id = $data['detail_id'];
            $inspChecklist->name = $data['parameter_name'];
            $inspChecklist->value = $data['parameter_value'];
            $inspChecklist->result = $data['result'];

            // Save the record to the database
            $inspChecklist->save();
        }

        // Indicate that the operation completed successfully
        return true;
    }
}
