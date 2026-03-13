<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Expense;
use Carbon\Carbon;

class BookingDashboardController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $bookingQuery = Booking::query();
        if ($start && $end) {
            $bookingQuery->whereBetween('date', [$start, $end]);
        }
        $bookings = $bookingQuery->with('room')->get();

        // Booking stats
        $totalBookings = $bookings->count();
        $confirmedBookings = $bookings->where('status', 'confirmed')->count();
        $draftBookings = $bookings->where('status', 'draft')->count();
        $cancelledBookings = $bookings->where('status', 'cancelled')->count();

        // Total Revenue (sum of total_amount for confirmed bookings)
        $totalRevenue = $bookings->where('status', 'confirmed')->sum(function($b) {
            return floatval($b->total_amount);
        });

        // Expenses
        $expenseQuery = Expense::query();
        if ($start && $end) {
            $expenseQuery->whereBetween('expense_date', [$start, $end]);
        }
        $expenses = $expenseQuery->get();
        $totalExpenses = $expenses->sum('amount');

        // Net Profit
        $netProfit = $totalRevenue - $totalExpenses;

        // Occupancy Rate
        $roomCount = Room::count();
        if ($start && $end) {
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);
            $days = $startDate->diffInDays($endDate) + 1;
        } else {
            // fallback: use current month
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
            $days = $startDate->diffInDays($endDate) + 1;
        }
        $totalAvailableRoomNights = $roomCount * $days;

        // Calculate total booked nights (for confirmed bookings)
        $totalBookedNights = $bookings->where('status', 'confirmed')->sum(function($b) {
            $checkIn = Carbon::parse($b->check_in_date);
            $checkOut = Carbon::parse($b->check_out_date);
            return $checkIn->diffInDays($checkOut);
        });

        $occupancyRate = $totalAvailableRoomNights > 0
            ? round(($totalBookedNights / $totalAvailableRoomNights) * 100, 2)
            : 0;

        // Chart data: Bookings per month by status
        $labels = [];
        $confirmedData = [];
        $draftData = [];
        $cancelledData = [];
        $grouped = $bookings->groupBy(function($b) {
            return Carbon::parse($b->date)->format('Y-m');
        });
        foreach ($grouped as $month => $group) {
            $labels[] = $month;
            $confirmedData[] = $group->where('status', 'confirmed')->count();
            $cancelledData[] = $group->where('status', 'cancelled')->count();
            $draftData[] = $group->where('status', 'draft')->count();
        }

        return response()->json([
            'total_bookings' => $totalBookings,
            'confirmed_bookings' => $confirmedBookings,
            'draft_bookings' => $draftBookings,
            'cancelled_bookings' => $cancelledBookings,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'occupancy_rate' => $occupancyRate,
            'bookings' => $bookings->map(function($b) {
                return [
                    'id' => $b->id,
                    'booking_number' => $b->booking_number,
                    'name' => $b->name,
                    'contact_number' => $b->contact_number,
                    'room_number' => optional($b->room)->room_number,
                    'check_in_date' => $b->check_in_date,
                    'check_out_date' => $b->check_out_date,
                    'status' => $b->status,
                    'total_amount' => $b->total_amount,
                ];
            }),
            'expenses' => $expenses->map(function($e) {
                return [
                    'id' => $e->id,
                    'amount' => $e->amount,
                    'note' => $e->note,
                    'expense_date' => $e->expense_date,
                    'status' => $e->status,
                ];
            }),
            'chart' => [
                'labels' => $labels,
                'series' => [
                    ['name' => 'Confirmed', 'data' => $confirmedData],
                    ['name' => 'Cancelled', 'data' => $cancelledData],
                    ['name' => 'Draft', 'data' => $draftData],
                ]
            ]
        ]);
    }
}