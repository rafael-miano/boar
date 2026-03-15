<?php

namespace App\Http\Controllers;

use App\Models\BoarReservation;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BoarReservationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'boar_id' => 'required|exists:boars,id',
            'address' => 'required|string|max:255',
            'service_date' => 'required|date|after:today',
            'service_fee_type' => 'required|in:pig,money',
            'service_fee_amount' => 'required|integer|min:1',
            'female_pig_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['reservation_status'] = 'pending';
        $data['service_status'] = 'pending';

        // Handle photo upload
        if ($request->hasFile('female_pig_photo')) {
            $file = $request->file('female_pig_photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('female-pig-photos', $filename, 'public');
            $data['female_pig_photo'] = $path;
        }

        $boarReservation = BoarReservation::create($data);
        $boarReservation->load(['boar', 'user']);

        // Notify admins so they can approve or reject the reservation
        $admins = User::where('role', 'admin')->get();
        $boarName = $boarReservation->boar->boar_name ?? 'Boar';
        $customerName = $boarReservation->user->name ?? 'Customer';
        foreach ($admins as $admin) {
            FilamentNotification::make()
                ->title('New reservation request')
                ->body("{$customerName} requested a reservation for {$boarName}. Go to Reservation Requests to approve or reject.")
                ->icon('heroicon-o-document-text')
                ->sendToDatabase($admin);
        }

        return response()->json([
            'success' => true,
            'message' => 'Boar reservation request submitted successfully! It will be reviewed by an administrator.',
            'data' => $boarReservation
        ]);
    }
}

