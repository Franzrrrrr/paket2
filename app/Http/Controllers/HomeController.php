<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Booking;
use App\Models\AreaParkir;

class HomeController extends Controller
{
    /**
     * Display booking page
     */
    public function bookingPage(): View
    {
        $areas = AreaParkir::get();
        return view('booking.index', compact('areas'));
    }

    /**
     * Display user's bookings page
     */
    public function myBookings(): View
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $bookings = Booking::where('user_id', $user->id)
            ->with('parkingArea')
            ->with('parkingSession')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('booking.my-bookings', compact('bookings'));
    }

    /**
     * Display QR codes page
     */
    public function qrCodesPage(): View
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // For now, return empty collection since QR codes are generated on-demand
        $qrCodes = collect([]);

        return view('qr-codes.index', compact('qrCodes'));
    }
}
