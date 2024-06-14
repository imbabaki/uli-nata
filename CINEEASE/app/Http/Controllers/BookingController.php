<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Movie;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function showBookingPage($id)
    {
        $movie = Movie::findOrFail($id);
        return view('movies.book', compact('movie'));
    }

    public function reserveSeat(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'seatArrangement' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        // Fetch movie details from the database
        $movie = Movie::findOrFail($request->movie_id);

        $totalAmount = $request->quantity * 150; // Assuming ticket price is 150 pesos

        $bookingId = uniqid();

        session([
            'booking' => [
                'id' => $bookingId,
                'user_id' => auth()->id(),
                'movie_id' => $request->movie_id,
                'movie_title' => $movie->title, // Set movie_title from fetched movie details
                'poster' => $movie->poster, // Set poster from fetched movie details
                'seatArrangement' => $request->seatArrangement,
                'quantity' => $request->quantity,
                'total_amount' => $totalAmount,
            ]
        ]);

        return redirect()->route('movies.proceed');
    }

    public function proceed()
    {
        $booking = session('booking');
        
        if (!$booking) {
            return redirect()->route('movies.index')->with('error', 'No booking data found.');
        }
    
        // Ensure that $booking is an array of bookings
        if (!is_array($booking)) {
            return redirect()->route('movies.index')->with('error', 'Invalid booking data.');
        }
    
        // Fetch movie title from the database based on the movie_id in the booking data
        $movieTitle = Movie::findOrFail($booking['movie_id'])->title;
    
        // Add movie_title to the booking array
        $booking['movie_title'] = $movieTitle;
    
        return view('movies.proceed', compact('booking'));
    }

    public function confirmBooking(Request $request)
    {
        $booking = session('booking');
    
        if (!$booking) {
            return redirect()->route('movies.index')->with('error', 'No booking data found.');
        }
    
        DB::beginTransaction();
    
        try {
            // Create a new booking record
            Booking::create([
                'user_id' => auth()->id(),
                'movie_id' => $booking['movie_id'],
                'movie_title' => $booking['movie_title'],
                'poster' => $booking['poster'],
                'seatArrangement' => $booking['seatArrangement'],
                'seats_booked' => $booking['quantity'], // Use quantity as seats booked
                'total_amount' => $booking['total_amount'],
                'payment_method' => $request->payment_method,
            ]);
    
            // Clear booking data from session
            session()->forget('booking');
    
            DB::commit();
    
            return redirect()->route('movies.print.ticket')->with('success', 'Booking confirmed!');
        } catch (\Exception $e) {
            DB::rollBack();
    
            // Log the error message for debugging
            \Log::error('Error storing booking: ' . $e->getMessage());
    
            return redirect()->route('movies.index')->with('error', 'Failed to store booking.');
        }
    
        $request->validate([
            'payment_method' => 'required|string|in:credit_card,debit_card,paypal',
        ]);

        Booking::create([
            'user_id' => auth()->id(),
            'movie_id' => $booking['movie_id'],
            'movie_title' => $booking['movie_title'],
            'poster' => $booking['poster'],
            'seatArrangement' => $booking['seatArrangement'],
            'seats_booked' => $booking['quantity'],
            'total_amount' => $booking['total_amount'],
            'payment_method' => $request->payment_method,
        ]);

        session()->forget('booking');

        return redirect()->route('movies.print.ticket')->with('success', 'Booking confirmed!');
    }

    public function printTicket()
    {
        $booking = session('booking');

        if (!$booking) {
            return redirect()->route('movies.print.ticket')->with('error', 'No booking data found.');
        }

        return view('movies.print-ticket', compact('booking'));
    }
}
