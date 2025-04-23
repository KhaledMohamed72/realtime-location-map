<?php

namespace App\Http\Controllers;

use App\Events\LocationUpdated;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LocationController extends Controller
{

    public function index(Request $request)
{
    $query = Location::query()->orderBy('created_at', 'asc');

    if ($request->has('start') && $request->has('end')) {
        $query->whereBetween('created_at', [
            Carbon::parse($request->start),
            Carbon::parse($request->end)
        ]);
    }

    return $query->get();
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $location = Location::create($validated);
        // Broadcast the event
        broadcast(new LocationUpdated($location->latitude, $location->longitude));
        return response()->json($location, 201);
    }
}