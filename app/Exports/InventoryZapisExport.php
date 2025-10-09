<?php
namespace App\Exports;

use App\Models\InventoryPlan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryZapisExport implements FromCollection, WithHeadings
{
    protected $plan;

    public function __construct(InventoryPlan $plan)
    {
        $this->plan = $plan;
    }

    public function collection()
    {
        $rows = [];
        foreach ($this->plan->differences ?? [] as $diff) {
            $rows[] = [
                'Inventárne číslo' => $diff->asset->inventory_number ?? '',
                'Názov' => $diff->asset->name ?? '',
                'Skutočný stav' => $diff->real_value ?? '',
                'Účtovný stav' => $diff->accounting_value ?? '',
                'Rozdiel' => $diff->difference ?? '',
                'Príčina rozdielu' => $diff->reason ?? '',
                'Návrh na vysporiadanie' => $diff->settlement_proposal ?? '',
                'Podpis zodpovednej osoby' => $this->plan->responsiblePerson->name ?? '',
            ];
        }
        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Inventárne číslo',
            'Názov',
            'Skutočný stav',
            'Účtovný stav',
            'Rozdiel',
            'Príčina rozdielu',
            'Návrh na vysporiadanie',
            'Podpis zodpovednej osoby',
        ];
    }
}
