<?php

namespace App\Services;

use App\Models\Asset;
use Carbon\Carbon;

class InventoryNumberService
{
    /**
     * Generuje inventárne číslo na základe roku nadobudnutia a ID majetku
     * 
     * @param int $assetId ID majetku
     * @param string|Carbon $acquisitionDate Dátum nadobudnutia
     * @return string Inventárne číslo vo formáte YYYY-ID
     */
    public function generateInventoryNumber($assetId, $acquisitionDate): string
    {
        // Konverzia dátumu na Carbon objekt ak je potrebné
        if (!$acquisitionDate instanceof Carbon) {
            $acquisitionDate = Carbon::parse($acquisitionDate);
        }
        
        $year = $acquisitionDate->year;
        
        // Formát: YYYY-ID (napr. 2024-1, 2024-2, atď.)
        return sprintf('%d-%d', $year, $assetId);
    }

    /**
     * Generuje dočasné inventárne číslo pre nový majetok (pred uložením)
     * Používa sa pre preview v formulári
     * 
     * @param string|Carbon $acquisitionDate Dátum nadobudnutia
     * @return string Dočasné inventárne číslo vo formáte YYYY-XXXX
     */
    public function generateTemporaryInventoryNumber($acquisitionDate): string
    {
        if (!$acquisitionDate instanceof Carbon) {
            $acquisitionDate = Carbon::parse($acquisitionDate);
        }
        
        $year = $acquisitionDate->year;
        
        // Nájdeme najbližšie ID pre daný rok
        $nextId = $this->getNextIdForYear($year);
        
        return sprintf('%d-%d', $year, $nextId);
    }

    /**
     * Získa nasledujúce ID pre daný rok
     * 
     * @param int $year Rok
     * @return int Nasledujúce ID
     */
    private function getNextIdForYear($year): int
    {
        // Nájdeme najvyššie ID v systéme a pridáme 1
        $maxId = Asset::max('id') ?? 0;
        return $maxId + 1;
    }

    /**
     * Validuje formát inventárneho čísla
     * 
     * @param string $inventoryNumber Inventárne číslo na validáciu
     * @return bool True ak je formát správny
     */
    public function validateInventoryNumberFormat($inventoryNumber): bool
    {
        // Formát: YYYY-ID (4 číslice rok, pomlčka, jedna alebo viac číslic ID)
        return preg_match('/^\d{4}-\d+$/', $inventoryNumber) === 1;
    }

    /**
     * Extrahuje rok z inventárneho čísla
     * 
     * @param string $inventoryNumber Inventárne číslo
     * @return int|null Rok alebo null ak formát nie je správny
     */
    public function extractYearFromInventoryNumber($inventoryNumber): ?int
    {
        if (!$this->validateInventoryNumberFormat($inventoryNumber)) {
            return null;
        }
        
        $parts = explode('-', $inventoryNumber);
        return (int) $parts[0];
    }

    /**
     * Extrahuje ID z inventárneho čísla
     * 
     * @param string $inventoryNumber Inventárne číslo
     * @return int|null ID alebo null ak formát nie je správny
     */
    public function extractIdFromInventoryNumber($inventoryNumber): ?int
    {
        if (!$this->validateInventoryNumberFormat($inventoryNumber)) {
            return null;
        }
        
        $parts = explode('-', $inventoryNumber);
        return (int) $parts[1];
    }

    /**
     * Kontroluje, či inventárne číslo zodpovedá dátumu nadobudnutia majetku
     * 
     * @param string $inventoryNumber Inventárne číslo
     * @param string|Carbon $acquisitionDate Dátum nadobudnutia
     * @return bool True ak rok v inventárnom čísle zodpovedá roku nadobudnutia
     */
    public function isInventoryNumberValidForAcquisitionDate($inventoryNumber, $acquisitionDate): bool
    {
        $year = $this->extractYearFromInventoryNumber($inventoryNumber);
        
        if ($year === null) {
            return false;
        }
        
        if (!$acquisitionDate instanceof Carbon) {
            $acquisitionDate = Carbon::parse($acquisitionDate);
        }
        
        return $year === $acquisitionDate->year;
    }
}