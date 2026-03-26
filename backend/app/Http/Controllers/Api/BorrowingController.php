<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function borrow(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'due_date'   => ['required', 'date', 'after:today'],
        ]);

        $identifier = trim($request->identifier);

        $device = Device::where('barcode', $identifier)
                        ->orWhere('serial_number', $identifier)
                        ->first();

        if (!$device) {
            return response()->json(['message' => 'Device not found. Please check the barcode or serial number.'], 404);
        }

        if ($device->isBorrowed()) {
            $activeBorrowing = $device->activeBorrowing()->with('user')->first();
            return response()->json([
                'message'  => 'This device is already borrowed.',
                'borrower' => $activeBorrowing?->user?->name,
                'due_date' => $activeBorrowing?->due_date,
            ], 422);
        }

        $borrowing = Borrowing::create([
            'user_id'    => $request->user()->id,
            'device_id'  => $device->id,
            'borrowed_at' => Carbon::now(),
            'due_date'   => Carbon::parse($request->due_date)->endOfDay(),
        ]);

        $device->update(['status' => 'borrowed']);
        $borrowing->load(['device', 'user']);

        return response()->json([
            'message'   => 'Device borrowed successfully.',
            'borrowing' => [
                'id'           => $borrowing->id,
                'device'       => $borrowing->device,
                'borrowed_at'  => $borrowing->borrowed_at,
                'due_date'     => $borrowing->due_date,
                'status_color' => $borrowing->status_color,
            ],
        ], 201);
    }

    public function returnDevice(Request $request): JsonResponse
    {
        $request->validate(['identifier' => ['required', 'string']]);

        $identifier = trim($request->identifier);

        $device = Device::where('barcode', $identifier)
                        ->orWhere('serial_number', $identifier)
                        ->first();

        if (!$device) {
            return response()->json(['message' => 'Device not found.'], 404);
        }

        if ($device->isAvailable()) {
            return response()->json(['message' => 'This device is not currently borrowed.'], 422);
        }

        $borrowing = Borrowing::where('device_id', $device->id)
                              ->whereNull('returned_at')
                              ->latest()
                              ->first();

        if (!$borrowing) {
            return response()->json(['message' => 'No active borrowing found for this device.'], 404);
        }

        $user = $request->user();
        if ($user->isStudent() && $borrowing->user_id !== $user->id) {
            return response()->json(['message' => 'You can only return devices you have borrowed.'], 403);
        }

        $borrowing->update(['returned_at' => Carbon::now()]);
        $device->update(['status' => 'available']);

        return response()->json([
            'message'   => 'Device returned successfully.',
            'borrowing' => ['id' => $borrowing->id, 'device' => $device, 'returned_at' => $borrowing->returned_at],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Borrowing::with(['user', 'device']);

        if ($user->isStudent()) {
            $query->where('user_id', $user->id);
        }

        if ($request->boolean('active')) {
            $query->whereNull('returned_at');
        }

        $borrowings = $query->orderByDesc('borrowed_at')->get()->map(function (Borrowing $b) {
            return [
                'id'           => $b->id,
                'user'         => ['id' => $b->user->id, 'name' => $b->user->name, 'email' => $b->user->email],
                'device'       => ['id' => $b->device->id, 'name' => $b->device->name, 'serial_number' => $b->device->serial_number, 'barcode' => $b->device->barcode],
                'borrowed_at'  => $b->borrowed_at,
                'due_date'     => $b->due_date,
                'returned_at'  => $b->returned_at,
                'status_color' => $b->status_color,
            ];
        });

        return response()->json($borrowings);
    }
}
