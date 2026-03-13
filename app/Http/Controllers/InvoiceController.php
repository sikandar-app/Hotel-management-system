<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends CommonController
{
    public function index(Request $request, $bookingId)
    {
        $perPage = $request->input('per_page', 10);  // Default to 10 users per page
        $search = $request->input('search', ''); // Get the search query from request
        $startDate = $request->input('start_date');  // Get start date from the request
        $endDate = $request->input('end_date');  // Get end date from the request
        $status = $request->input('status');
        $query = Invoice::where('booking_id', $bookingId)->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('amount_paid', 'like', '%' . $search . '%');
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('payment_date', [$startDate, $endDate]);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            });
        
        if ($request->has('sort_by') && isset($request->sort_by) && $request->has('sort_direction')) {
            $query->orderBy($request->sort_by, $request->sort_direction);
        }
        
        $sqlquery = clone $query;
        $paidAmount = $sqlquery->where('status', 'approved')->sum('amount_paid');

        $invoices = $query->paginate($perPage);
        
        $data = [
            'invoices' => InvoiceResource::collection($invoices),
            'total' => $invoices->total(),
            'per_page' => $invoices->perPage(),
            'paid_amount' => $paidAmount
        ];

        return $this->sendResponse($data, "Fetched invoices successfully");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount_paid' => 'required|numeric',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $latestId = Invoice::max('id') ?? 0; // fallback to 0 if no record exists

        $invoice = Invoice::create([
            ...$validated,
            'user_id' => auth()->user()->id,
            'status' => 'pending',
            'invoice_number' => 'TES-' . $validated['booking_id'] . '-' . Carbon::parse($validated['payment_date'])->format('dmy'). '-' . $latestId+1,
        ]);

        return $this->sendResponse($invoice, "Created invoice successfully");
    }

    public function show(Invoice $invoice)
    {
        return $this->sendResponse($invoice, "Fetched invoice successfully");
    }

    public function approved(Invoice $invoice)
    {
        $invoice->update(['status' => 'approved']);
        return $this->sendResponse($invoice, "Updated invoice status successfully");
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount_paid' => 'required|numeric',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        if($invoice->status == 'approved') return;

        $invoice->update($validated);
        return $this->sendResponse($invoice, "Updated invoice successfully");
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        
        if($invoice->status == 'approved') return;

        return $this->sendResponse('', "Deleted invoice successfully");
    }

    public function downloadInvoice($id)
    {
        $invoice = Invoice::with('booking')->where('id', $id)->firstOrFail();
        $booking   = $invoice->booking;
        
        // Sum all paid amounts for this booking up to and including this invoice
        $totalPaid = Invoice::where('booking_id', $booking->id)
            ->where('id', '<=', $invoice->id) // include current and prior invoices
            ->sum('amount_paid');

        // $totalPaid = $invoice->amount_paid;
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
