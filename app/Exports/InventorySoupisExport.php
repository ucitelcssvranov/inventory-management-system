<?php
namespace App\Exports;

use App\Models\InventoryPlan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventorySoupisExport implements FromCollection, WithHeadings
{
    protected $plan;

    public function __construct(InventoryPlan $plan)
    {
        $this->plan = $plan;
    }

    public function collection()
    {
        $rows = [];
        foreach ($this->plan->items as $item) {
            $rows[] = [
                'Inventárne číslo' => $item->asset->inventory_number ?? '-',
                'Názov' => $item->asset->name ?? '-',
                'Kategória' => $item->asset->category->name ?? '-',
                'Lokácia' => $item->asset->location->name ?? '-',
                'Množstvo' => $item->expected_qty ?? 1,
                'Cena' => $item->asset->acquisition_cost ?? '-',
                'Miesto uloženia' => $this->plan->storage_place,
                'Hmotne zodpovedná osoba' => $this->plan->responsiblePerson ? $this->plan->responsiblePerson->name : '-',
            ];
        }
        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Inventárne číslo',
            'Názov',
            'Kategória',
            'Lokácia',
            'Množstvo',
            'Cena',
            'Miesto uloženia',
            'Hmotne zodpovedná osoba',
        ];
    }
}
