<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryExportController extends Controller
{
    public function soupis($inventory_plan)
    {
        // ...implement export logic...
        return response('Export súpis not implemented.', 501);
    }

    public function zapis($inventory_plan)
    {
        // ...implement export logic...
        return response('Export zápis not implemented.', 501);
    }
}
