<?php
namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;

class OccupancyController extends Controller
{
    public function getOccupancyData(Request $request)
    {
        $status = $request->input('status', 'any');
        $room = $request->input('room', 'all');
        $monthYear = $request->input('monthYear', date('Y-m'));
        [$year, $month] = explode('-', $monthYear);
        $start = date("$year-$month-01");
        $end = date("Y-m-t", strtotime($start));

        $query = Booking::query()
            ->where('check_in_date', '<=', $end)
            ->where('check_out_date', '>=', $start);

        if ($status !== 'any') {
            $query->where('status', $status);
        }

        if ($room !== 'all') {
            $query->where('room_id', $room);
        }

        $occupancy = $query->get(['id','booking_number','name','room_id', 'check_in_date', 'check_out_date','status']);
        $rooms = Room::all(['id', 'room_number']);

        return response()->json([
            'occupancy' => $occupancy,
            'rooms' => $rooms
        ]);
    }
}