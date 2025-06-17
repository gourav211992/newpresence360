<?php

namespace App\View\Components\PoBulk;

use App\Helpers\ItemHelper;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Rate extends Component
{
    public $row, $rowCount, $currencyId, $documentDate;
    /**
     * Create a new component instance.
     */
    public function __construct($row, $rowCount, $currencyId, $documentDate)   
    {
        $this->row = $row;
        $this->rowCount = $rowCount;
        $this->currencyId = $currencyId;
        $this->documentDate = $documentDate;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $itemCost = ItemHelper::getItemCostPrice($this?->row?->item_id, [], $this?->row?->uom_id, $this->currencyId, $this->documentDate, $this?->row?->vendor_id);
        return view('components.po-bulk.rate',
        [
            'row' => $this->row,
            'rowCount' => $this->rowCount,
            'itemCost' => $itemCost,
        ]);
    }
}
