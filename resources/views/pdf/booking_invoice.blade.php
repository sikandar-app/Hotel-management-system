<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Booking Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.6;
        }

        h1,
        h2,
        h3 {
            text-align: center;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        td,
        th {
            padding: 4px 6px;
            vertical-align: top;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .bold {
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .underline {
            border-bottom: 1px solid #000;
            margin: 10px 0;
        }

        .totals {
            font-weight: bold;
            font-size: 15px;
            text-align: right;
            margin-top: 10px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 10px;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>{{$settings['name']}}</h2>
        <p>{{$settings['address']}}</p>
        <p>Email: {{$settings['email']}}</p>
        <p>{{$settings['phone']}}</p>
        <h2>INVOICE</h2>
    </div>

    <table>
        <tr>
            <td class="bold">Guest Information:</td>
            <td class="bold right">
                Invoice No: {{ $booking->invoice_number }}
            </td>
        </tr>
        <tr>
            <td>Guest Name: {{ $booking->name }}</td>
            <td class="right">Date Issued: {{ \Carbon\Carbon::now()->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td>Contact No: {{ $booking->contact_number }}</td>
        </tr>
        @if($booking->passport || $booking->cnic_number)
        <tr>
            <td colspan="2">
                Passport/CNIC: {{ $booking->passport ?? $booking->cnic_number }}
            </td>
        </tr>
        @endif
    </table>

    <div class="underline"></div>

    <p class="section-title">Booking Details:</p>
    <table>
        <tr>
            <td>Apartment No:</td>
            <td class="right">{{ $booking->room->room_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Check-in Date:</td>
            <td class="right">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td>Check-out Date:</td>
            <td class="right">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td>Duration:</td>
            <td class="right">{{ $booking->number_of_nights }} night(s)</td>
        </tr>
    </table>

    <div class="underline"></div>

    <p class="section-title">Payment Summary:</p>
    <table>
        <tr>
            <td>Rate per night:</td>
            <td class="right">PKR {{ number_format($booking->price_per_night) }}</td>
        </tr>
        <tr>
            <td>Subtotal:</td>
            <td class="right">PKR {{ number_format($booking->total_amount) }}</td>
        </tr>
        <tr>
            <td>Discount:</td>
            <td class="right">- PKR {{ number_format($booking->discount) }}</td>
        </tr>
        <tr>
            <td>
                Tax
                @if($booking->tax)
                ({{ $booking->tax->value }} {{ $booking->tax->type === 'percentage' ? '%' : 'PKR' }})
                @endif:
            </td>
            <td class="right">
                PKR {{ number_format($booking->net_total - $booking->total_amount + $booking->discount) }}
            </td>
        </tr>
        <tr class="bold">
            <td>Total (Incl. Tax):</td>
            <td class="right">PKR {{ number_format($booking->net_total) }}</td>
        </tr>
        <tr>
            <td>Total Paid:</td>
            <td class="right">PKR {{ number_format($totalPaid) }}</td>
        </tr>
        <tr>
            <td>Remaining Balance:</td>
            <td class="right">PKR {{ number_format($remaining) }}</td>
        </tr>
    </table>

    <div class="underline"></div>

    <div class="totals">
        Total Payable: PKR {{ number_format($remaining) }}
    </div>
    <div class="underline"></div>

    <p class="section-title">Date Coverage Overview:</p>
    <table>
        <tr>
            <td class="bold">Booked Dates:</td>
            <td>
                @foreach($bookedDates as $d)
                <span>{{ \Carbon\Carbon::parse($d)->format('d M Y') }}</span>@if(!$loop->last), @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <td class="bold">Missing Dates:</td>
            <td>
                @if($missingDates->isNotEmpty())
                @foreach($missingDates as $d)
                <span style="color:red">{{ \Carbon\Carbon::parse($d)->format('d M Y') }}</span>@if(!$loop->last), @endif
                @endforeach
                @else
                <span style="color:green">None</span>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>