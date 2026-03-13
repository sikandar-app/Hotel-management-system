<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomController extends CommonController
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);  // Default to 10 users per page
        $search = $request->input('search', ''); // Get the search query from request
        $startDate = $request->input('start_date');  // Get start date from the request
        $endDate = $request->input('end_date');  // Get end date from the request
        
        $rooms = Room::with('bookings')->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('building', 'like', '%' . $search . '%')
                        ->orWhere('floor', 'like', '%' . $search . '%')
                        ->orWhere('room_number', 'like', '%' . $search . '%');
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc') // Optional: default sorting
            ->paginate($perPage);  // Paginate results;
        return $this->sendResponse($rooms, "Fetched rooms successfully");
    }

    public function getRooms($status = 'available')
    {
        $rooms = Room::get();  // Paginate results;
        return $this->sendResponse($rooms, "Fetched rooms successfully");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:255',
            'floor' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'price_per_night' => 'required|numeric|min:0',
            'status' => 'required|in:available,booked',
        ]); 

        // Store room
        $room = Room::create($validated);
                
        return $this->sendResponse($room, "Created room successfully");
    }

    public function show(Room $room)
    {
        return $this->sendResponse($room, "Fetched room successfully");
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:255',
            'floor' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'price_per_night' => 'required|numeric|min:0',
            'status' => 'required|in:available,booked',
        ]);

        $room->update($validated);
        return $this->sendResponse($room, "Updated room successfully");
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return $this->sendResponse('', "Deleted room successfully");
    }
}
