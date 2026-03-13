<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Tax;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookingController extends CommonController
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);  // Default to 10 users per page
        $search = $request->input('search', ''); // Get the search query from request
        $startDate = $request->input('start_date');  // Get start date from the request
        $endDate = $request->input('end_date');  // Get end date from the request
        
        $query = Booking::when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('booking_number', 'like', '%' . $search . '%')
                        ->orWhere('contact_number', 'like', '%' . $search . '%')
                        ->orWhere('cnic_number', 'like', '%' . $search . '%');
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                // Filter by expense_date between startDate and endDate
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        
        if ($request->has('sort_by') && isset($request->sort_by) && $request->has('sort_direction')) {
            $query->orderBy($request->sort_by, $request->sort_direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $bookings = $query->paginate($perPage);

        $data = [
            'bookings' => BookingResource::collection($bookings),
            'total' => $bookings->total(),
            'per_page' => $bookings->perPage(),
        ];

        return $this->sendResponse($data, "Fetched bookings successfully");
    }

    public function tax()
    {
        $taxes = Tax::where('status', 1)->get();
        return $this->sendResponse($taxes, "Fetched taxes successfully");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'number_of_guests' => 'required|integer|min:1|max:100',
            'contact_number' => 'required|string|regex:/^\+?[0-9]{10,15}$/',
            'cnic_number' => 'nullable|min:6|max:15',
            'passport'     => 'nullable|string|min:6|max:20',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'approach_via' => 'nullable|string',
            'price_per_night' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'document_image' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
            'tax_id' => 'required|exists:taxes,id',
        ]);

        $tax = Tax::find($request->tax_id);
        
        if(!isset($tax)) {
            return $this->sendError("Tax not found");
        }

        // Calculate number of nights
        $checkIn = Carbon::parse($request->check_in_date);
        $checkOut = Carbon::parse($request->check_out_date);
        
        $selectedDates = $this->checkRoomAvailability($request->room_id,null, $checkIn, $checkOut, true);
        
        if(count($selectedDates) == 0) {
            return $this->sendError("Room is already booked for the selected dates");
        }

        $numberOfNights = count($selectedDates);
        
        if($numberOfNights == 0) {
            return $this->sendError("Room is already booked for the selected dates");
        }

        // Calculate total amount and net total
        $pricePerNight = $request->price_per_night;
        $totalAmount = $pricePerNight * $numberOfNights;
        $discount = $request->discount ?? 0;

        if ($tax->type === 'percentage') {
            $taxAmount = ($totalAmount - $discount) * floatval($tax->value) / 100;
        } else {
            $taxAmount = floatval($tax->value);
        }


        $netTotal = $totalAmount - $discount + $taxAmount;
        $status = 'draft';

        if($request->hasFile('document_image')) {
            $file = $request->file('document_image');
            $userId = auth()->id();

            // Generate a unique filename: userID_uniqid().extension
            $filename = 'user_' . $userId . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // ✅ Ensure directory exists
            Storage::disk('public')->makeDirectory('documents');
            // Store the file in storage/app/public/document_images/
            $path = $file->storeAs('documents', $filename, 'public');

            $validated['document_image'] = $path;
        }
        
        $latestId = Booking::max('id') ?? 0; // fallback to 0 if no record exists
        
        // Store booking
        $booking = Booking::create([
            ...$validated,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'booking_date' => Carbon::now(),
            'number_of_nights' => $numberOfNights,
            'total_amount' => $totalAmount,
            'net_total' => $netTotal,
            'status' => $status,
            'user_id' => auth()->id(),
            'booking_dates' => json_encode($selectedDates),
            'booking_number' => 'TES-' . $validated['room_id'] . '-' . Carbon::parse($validated['date'])->format('dmy'). '-' . $latestId+1,
        ]);

        return $this->sendResponse($booking, "Created booking successfully");
    }

    public function show(Booking $booking)
    {
        $booking = new BookingResource($booking);
        return $this->sendResponse($booking, "Fetched booking successfully");
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'contact_number' => 'required|string|regex:/^\+?[0-9]{10,15}$/',
            'number_of_guests' => 'required|integer|min:1|max:100',
            'cnic_number' => 'nullable|min:6|max:15',
            'passport'     => 'nullable|string|min:6|max:20',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'approach_via' => 'nullable|string',
            'price_per_night' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'document_image' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $tax = Tax::find($request->tax_id);
        
        if(!isset($tax)) {
            return $this->sendError("Tax not found");
        }

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);

        // Generate array of all dates in the range
        $bookingDates = $this->checkRoomAvailability($request->room_id,$booking->id, $checkIn, $checkOut, true);

        if(count($bookingDates) == 0) {
            return $this->sendError("Room is already booked for the selected dates");
        }

        $numberOfNights = count($bookingDates);
        
        if($numberOfNights == 0) {
            return $this->sendError("Room is already booked for the selected dates");
        }

        // Calculate total amount and net total
        $pricePerNight = $request->price_per_night;
        $totalAmount = $pricePerNight * $numberOfNights;
        $discount = $request->discount ?? 0;

        if ($tax->type === 'percentage') {
            $taxAmount = ($totalAmount - $discount) * floatval($tax->value) / 100;
        } else {
            $taxAmount = floatval($tax->value);
        }


        $netTotal = $totalAmount - $discount + $taxAmount;

        // Calculate values

        $validated['booking_dates'] = $bookingDates; // Store as JSON
        $validated['number_of_nights'] = $numberOfNights;
        $validated['total_amount'] = $totalAmount;
        $validated['net_total'] = $netTotal;

        $status = 'draft';

        if ($request->hasFile('document_image')) {
            $file = $request->file('document_image');
            $userId = auth()->id();
            $filename = 'user_' . $userId . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->makeDirectory('documents');
            $path = $file->storeAs('documents', $filename, 'public');
            $validated['document_image'] = $path;
            
            if($booking->advance_paid){
                $status = 'confirmed';
            }
        }

        $validated['status'] = $status;

        $booking->update($validated);

        return $this->sendResponse($booking, "Booking updated successfully.");
    }

    public function destroy(Booking $booking)
    {
        Room::where('id', $booking->room_id)->update(['status' => 'available']);
        $booking->delete();
        return $this->sendResponse('', "Deleted booking successfully");
    }

    public function checkRoomAvailability($roomId, $bookingId = null, $checkInDate = null, $checkOutDate = null, $returnArray = false)
    {
        $start = $checkInDate ?? Carbon::today();
        $end = $checkOutDate ?? Carbon::today()->addDays(1200);

        // Generate all dates including check-in and check-out
        $allDates = collect(CarbonPeriod::create($start, $end))
            ->map(fn($date) => $date->toDateString());

        // Get other bookings that overlap the date range
        $bookings = Booking::where('room_id', $roomId)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('check_in_date', [$start, $end])
                    ->orWhereBetween('check_out_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('check_in_date', '<', $start)
                            ->where('check_out_date', '>', $end);
                    });
            });

        // Exclude current booking if editing
        if ($bookingId) {
            $bookings->where('id', '!=', $bookingId);
        }

        $bookings = $bookings->get();

        // Gather all booked dates (excluding check-out dates)
        $bookedDates = $bookings->flatMap(function ($booking) {
            $start = Carbon::parse($booking->check_in_date);
            $end = Carbon::parse($booking->check_out_date); // Exclude checkout day
            return collect(CarbonPeriod::create($start, $end))
                ->map(fn($date) => $date->toDateString());
        })->unique();

        // If editing, preserve current booking's dates in the available list
        $currentBookingDates = collect();
        if ($bookingId) {
            $currentBooking = Booking::where('id',$bookingId)->where('status', 'confirmed')->first();
            if ($currentBooking) {
                $start = Carbon::parse($currentBooking->check_in_date);
                $end = Carbon::parse($currentBooking->check_out_date); // Exclude checkout
                $currentBookingDates = collect(CarbonPeriod::create($start, $end))
                    ->map(fn($date) => $date->toDateString());
            }
        }

        // Filter only the dates that are available (and include current booking's dates)
        $availableDates = $allDates
            ->diff($bookedDates)
            ->merge($currentBookingDates)
            ->unique()
            ->sort()
            ->values();

        if ($returnArray) {
            return $availableDates->toArray();
        }

        return $this->sendResponse($availableDates, "Fetched available booking dates successfully");
    }

    public function confirmed($bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return $this->sendError("Booking not found");
        }

        if($booking->document_image) {
            // Check if the document_image exists in storage
            if (!Storage::disk('public')->exists($booking->document_image)) {
                return $this->sendError("CNIC image not found");
            }
        } else {
            return $this->sendError("CNIC image is required to confirm booking");
        }

        if ($booking->status === 'confirmed') {
            return $this->sendError("Booking is already confirmed");
        }
        
        if ($booking->advance_paid) {
            // Update booking status and document_image
            $booking->update([
                'status' => 'confirmed',
            ]);
        } else {
            return $this->sendError("You have not paid 30% of the total booking amount");
        }

        return $this->sendResponse($booking, "Booking confirmed successfully");
    }

    public function export(Request $request, Booking $booking)
    {
        // return view('pdf.booking_form', compact('booking'));

        $pdf = Pdf::loadView('pdf.booking_form', compact('booking'));
        return $pdf->download("bookings-form-{$booking->booking_number}.pdf");
    }

    public function downloadInvoice($id)
    {
        $booking = Booking::with(['room', 'invoices', 'tax'])->findOrFail($id);
        $totalPaid = $booking->invoices->where('status', 'approved')->sum('amount_paid');
        $remaining = $booking->net_total - $totalPaid;

        // decode the stored array of dates from booking_dates column
        $bookedDates = collect(json_decode($booking->booking_dates ?? '[]'));

        // generate full date range from check-in to check-out
        $checkIn = Carbon::parse($booking->check_in_date);
        $checkOut = Carbon::parse($booking->check_out_date);
        $fullRange = collect();
        for ($date = $checkIn->copy(); $date <= $checkOut; $date->addDay()) {
            $fullRange->push($date->toDateString());
        }

        // find missing dates (unbooked in the range)
        $missingDates = $fullRange->diff($bookedDates)->values();

        return PDF::loadView('pdf.booking_invoice', [
            'booking' => $booking,
            'totalPaid' => $totalPaid,
            'remaining' => $remaining,
            'bookedDates' => $bookedDates,
            'missingDates' => $missingDates,
        ])->download("booking-invoice-{$booking->booking_number}.pdf");
    }
}
