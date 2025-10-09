<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        // Zobrazíme len budovy s počtom miestností a majetku
        $locations = Location::budovy()
            ->withCount(['children as miestnosti_count', 'assets'])
            ->with(['createdBy', 'updatedBy'])
            ->orderBy('name')
            ->paginate(15);
        
        $currentLocation = null; // Pre index view budov je null
        
        return view('locations.index', compact('locations', 'currentLocation'));
    }

    public function create()
    {
        $budovy = Location::budovy()->get(); // Pre výber parent budovy pri vytváraní miestnosti
        return view('locations.create', compact('budovy'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'type' => 'required|in:budova,miestnost',
            'notes' => 'nullable|string',
        ];

        // Ak je typ miestnosť, vyžadujeme parent_id a room_number
        if ($request->type === 'miestnost') {
            $rules['parent_id'] = 'required|exists:locations,id';
            $rules['room_number'] = 'required|max:50';
            $rules['room_description'] = 'nullable|max:255';
            
            // Unique constraint pre číslo miestnosti v rámci budovy
            $rules['room_number'] .= '|unique:locations,room_number,NULL,id,parent_id,' . $request->parent_id;
        } else {
            // Pre budovu je name unique
            $rules['name'] .= '|unique:locations,name';
        }

        $validated = $request->validate($rules);
        $validated['created_by'] = auth()->id();
        
        Location::create($validated);

        $message = $validated['type'] === 'budova' ? 'Budova bola pridaná.' : 'Miestnosť bola pridaná.';
        return redirect()->route('locations.index')->with('success', $message);
    }

    public function show(Location $location)
    {
        // Ak je budova, zobrazíme jej miestnosti v index view
        if ($location->isBudova()) {
            $locations = $location->children()
                ->with(['assets', 'createdBy', 'updatedBy'])
                ->withCount('assets')
                ->orderBy('name')
                ->paginate(15);
            
            $currentLocation = $location->load(['createdBy', 'updatedBy']);
            
            return view('locations.index', compact('locations', 'currentLocation'));
        }
        
        // Ak je miestnosť, zobrazíme detail miestnosti
        $location->load(['assets', 'createdBy', 'updatedBy', 'parent']);
        return view('locations.show_miestnost', compact('location'));
    }

    public function edit(Location $location)
    {
        $budovy = Location::budovy()->get();
        return view('locations.edit', compact('location', 'budovy'));
    }

    public function update(Request $request, Location $location)
    {
        // Debug: Log the incoming request data
        \Log::info('Location update request', [
            'location_id' => $location->id,
            'request_data' => $request->all(),
            'location_type' => $location->type
        ]);

        $rules = [
            'type' => 'required|in:budova,miestnost',
            'notes' => 'nullable|string',
        ];

        // Ak je typ miestnosť, vyžadujeme parent_id a room_number
        if ($request->type === 'miestnost') {
            $rules['parent_id'] = 'required|exists:locations,id';
            $rules['room_number'] = 'required|max:50';
            $rules['room_description'] = 'nullable|max:255';
            
            // Unique constraint pre číslo miestnosti v rámci budovy (okrem súčasného záznamu)
            $rules['room_number'] .= '|unique:locations,room_number,' . $location->id . ',id,parent_id,' . $request->parent_id;
        } else {
            // Pre budovu vyžadujeme name a description
            $rules['name'] = 'required|max:255|unique:locations,name,' . $location->id;
            $rules['description'] = 'nullable|string';
        }

        \Log::info('Validation rules', ['rules' => $rules]);

        $validated = $request->validate($rules);
        $validated['updated_by'] = auth()->id();
        
        $location->update($validated);

        $message = $validated['type'] === 'budova' ? 'Budova bola upravená.' : 'Miestnosť bola upravená.';
        
        // Explicit redirect to locations index instead of just route name
        return redirect()->to('/locations')->with('success', $message);
    }

    public function destroy(Location $location)
    {
        // Kontrola, či lokácia nemá majetok
        if ($location->assets()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nie je možné vymazať lokáciu s majetkom.'
                ]);
            }
            return redirect()->route('locations.index')->with('error', 'Nie je možné vymazať lokáciu s majetkom.');
        }

        // Kontrola, či budova nemá miestnosti
        if ($location->isBudova() && $location->children()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nie je možné vymazať budovu, ktorá obsahuje miestnosti.'
                ]);
            }
            return redirect()->route('locations.index')->with('error', 'Nie je možné vymazať budovu, ktorá obsahuje miestnosti.');
        }

        $type = $location->type;
        $location->delete();
        
        $message = $type === 'budova' ? 'Budova bola úspešne vymazaná.' : 'Miestnosť bola úspešne vymazaná.';
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }
        
        return redirect()->route('locations.index')->with('success', $message);
    }

    /**
     * AJAX metóda pre rýchlu inline editáciu
     */
    public function quickUpdate(Request $request, Location $location)
    {
        $rules = [
            'name' => 'nullable|max:255',
            'description' => 'nullable|string',
            'room_number' => 'nullable|max:50',
            'room_description' => 'nullable|max:255',
        ];

        // Ak editujeme name budovy, musí byť unique
        if ($request->has('name') && $location->isBudova()) {
            $rules['name'] = 'required|max:255|unique:locations,name,' . $location->id;
        }

        // Ak editujeme room_number miestnosti, musí byť unique v rámci budovy
        if ($request->has('room_number') && $location->isMiestnost()) {
            $rules['room_number'] = 'required|max:50|unique:locations,room_number,' . $location->id . ',id,parent_id,' . $location->parent_id;
        }

        $validated = $request->validate($rules);
        $validated['updated_by'] = auth()->id();
        
        $location->update($validated);

        return response()->json([
            'success' => true,
            'message' => $location->isBudova() ? 'Budova bola upravená.' : 'Miestnosť bola upravená.',
            'location' => $location->fresh(['createdBy', 'updatedBy'])
        ]);
    }
}
