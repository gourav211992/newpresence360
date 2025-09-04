<?php

namespace App\Exports\Kaizen;

use App\Helpers\Helper;
use App\Models\Kaizen\ErpKaizen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class KaizenExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate, $toDate)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function collection()
    {
        $user = Helper::getAuthenticatedUser();

        return ErpKaizen::with([
            'department:id,name',
            'createdBy:id,designation_id,name',
            'createdBy.designation:id,marks',
            'cost:id,description',
            'delivery:id,description',
            'moral:id,description',
            'innovation:id,description',
            'safety:id,description',
            'quality:id,description',
            'productivity:id,description'
        ])
        ->where('organization_id', $user->organization_id)
        ->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
            ->orWhere('approver_id', $user->id);
        })
        // ->whereBetween('created_at', [$this->fromDate, $this->toDate])
        ->get();
    }

    public function map($kaizen): array
    {
            $description = new RichText();
            // "Problem: " label in red
            $problemLabel = $description->createTextRun("Problem: ");
            $problemLabel->getFont()->getColor()->setARGB('FFFF0000'); // Red

            // Problem content in default color (or any color you want)
            $problemContent = $description->createTextRun($kaizen->problem ?? '-');
            $problemContent->getFont()->getColor()->setARGB('FF000000'); // Black

            // New line
            $description->createTextRun("\n");

            // "Counter Measure: " label in green
            $counterLabel = $description->createTextRun("Counter Measure: ");
            $counterLabel->getFont()->getColor()->setARGB('FF00AA00'); // Green

            // Counter Measure content in default color
            $counterContent = $description->createTextRun($kaizen->counter_measure ?? '-');
            $counterContent->getFont()->getColor()->setARGB('FF000000'); // Black

        return [
            // Combine Problem + Counter Measure into one column
            $description,
            $kaizen->department?->name ?? '-',
            $kaizen->createdBy?->designation?->marks ?? '-',
            $kaizen->cost?->description ?? '-',
            $kaizen->delivery?->description ?? '-',
            $kaizen->moral?->description ?? '-',
            $kaizen->innovation?->description ?? '-',
            $kaizen->quality?->description ?? '-',
            $kaizen->safety?->description ?? '-',
            $kaizen->productivity?->description ?? '-',
            $kaizen->score ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Description',    
            'Department',
            'Level Merit',
            'Cost',
            'Delivery',
            'Moral',
            'Innovation',
            'Quality',
            'Safety',
            'Productivity',
            'Aggregated Score'
        ];
    }


    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Header style
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F81BD'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A2:A{$highestRow}")
            ->getAlignment()
            ->setWrapText(true);
        $sheet->getStyle("A2:A{$highestRow}")
            ->getFont()
            ->getColor()
            ->setARGB('FF0000FF');

        // Keep other columns auto-size
        foreach (range('A', 'K') as $col) {
            // dd($col);
            $sheet->getColumnDimension($col)->setAutoSize(false);
        }

        // Alternate row colors for B-K (exclude Description to keep text visible)
        for ($row = 2; $row <= $highestRow; $row++) {
            $fillColor = $row % 2 === 0 ? 'FFDCE6F1' : 'FFFFFFFF';
            $sheet->getStyle("B{$row}:K{$row}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB($fillColor);
        }

        // Auto-adjust row height for wrapped text
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(-1);
        }
    }
    public function chunkSize(): int
        {
            return 500;
        }

}
