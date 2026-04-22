<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BorrowingController extends Controller
{
    public function borrow(Request $request): JsonResponse
    {
        $request->validate([
            'identifier'    => ['required', 'string'],
            'due_date'      => ['required', 'date', 'after:today'],
            'due_time'      => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'student_name'  => ['nullable', 'string', 'max:255'],
            'student_email' => ['nullable', 'email'],
        ]);

        $identifier = trim($request->identifier);

        $device = Device::where('barcode', $identifier)
                        ->orWhere('serial_number', $identifier)
                        ->first();

        if (!$device) {
            return response()->json(['message' => 'Seadet ei leitud. Kontrolli vöökood või seerianumber.'], 404);
        }

        if ($device->isBorrowed()) {
            $activeBorrowing = $device->activeBorrowing()->with('user')->first();
            return response()->json([
                'message'  => 'See seade on juba laenutatud.',
                'borrower' => $activeBorrowing?->user?->name,
                'due_date' => $activeBorrowing?->due_date,
            ], 422);
        }

        $borrowing = Borrowing::create([
            'user_id'       => $request->user()->id,
            'device_id'     => $device->id,
            'borrowed_at'   => Carbon::now(),
            'due_date'      => Carbon::parse($request->due_date)->endOfDay(),
            'due_time'      => $request->due_time ?? '08:30',
            'student_name'  => $request->student_name,
            'student_email' => $request->student_email,
        ]);

        $device->update(['status' => 'borrowed']);
        $borrowing->load(['device', 'user']);

        return response()->json([
            'message'   => 'Seade edukalt laenutatud.',
            'borrowing' => [
                'id'            => $borrowing->id,
                'device'        => $borrowing->device,
                'student_name'  => $borrowing->student_name,
                'student_email' => $borrowing->student_email,
                'borrowed_at'   => $borrowing->borrowed_at,
                'due_date'      => $borrowing->due_date,
                'due_time'      => $borrowing->due_time,
                'status_color'  => $borrowing->status_color,
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
            return response()->json(['message' => 'Seadet ei leitud.'], 404);
        }

        if ($device->isAvailable()) {
            return response()->json(['message' => 'See seade ei ole praegu laenutatud.'], 422);
        }

        $borrowing = Borrowing::where('device_id', $device->id)
                              ->whereNull('returned_at')
                              ->latest()
                              ->first();

        if (!$borrowing) {
            return response()->json(['message' => 'Aktiivset laenutust ei leitud.'], 404);
        }

        $user = $request->user();
        if ($user->isStudent() && $borrowing->user_id !== $user->id) {
            return response()->json(['message' => 'Saad tagastada ainult enda laenutusi.'], 403);
        }

        $borrowing->update(['returned_at' => Carbon::now()]);
        $device->update(['status' => 'available']);

        return response()->json([
            'message'   => 'Seade edukalt tagastatud.',
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

        // Change booking due date (admin only)
        $borrowings = $query->orderByDesc('borrowed_at')->get()->map(function (Borrowing $b) {
            return [
                'id'            => $b->id,
                'user'          => ['id' => $b->user->id, 'name' => $b->user->name, 'email' => $b->user->email],
                'device'        => [
                    'id'            => $b->device->id,
                    'name'          => $b->device->name,
                    'serial_number' => $b->device->serial_number,
                    'barcode'       => $b->device->barcode,
                ],
                'student_name'  => $b->student_name,
                'student_email' => $b->student_email,
                'borrowed_at'   => $b->borrowed_at,
                'due_date'      => $b->due_date,
                'due_time'      => $b->due_time,
                'returned_at'   => $b->returned_at,
                'status_color'  => $b->status_color,
            ];
        });

        return response()->json($borrowings);
    }

    // PATCH /borrowings/{id}/due-date — update booking due date
    public function updateDueDate(Request $request, Borrowing $borrowing): JsonResponse
    {
        $request->validate([
            'due_date' => ['required', 'date'],
            'due_time' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        $borrowing->update([
            'due_date' => Carbon::parse($request->due_date)->endOfDay(),
            'due_time' => $request->due_time ?? $borrowing->due_time,
        ]);

        return response()->json(['message' => 'Tagastamise tähtaeg uuendatud.', 'borrowing' => $borrowing]);
    }

    // POST /borrowings/{id}/notify — send notification to student
    public function notify(Request $request, Borrowing $borrowing): JsonResponse
    {
        if (!$borrowing->student_email) {
            return response()->json(['message' => 'Sellel laenutamisel ei ole õpilase meili.'], 422);
        }

        $deviceName = $borrowing->device?->name ?? 'seade';
        $dueDate    = Carbon::parse($borrowing->due_date)->format('d.m.Y');
        $dueTime    = $borrowing->due_time ?? '08:30';

        // Simple mail send (uses Laravel's default mail config)
        try {
            Mail::raw(
                "Tere {$borrowing->student_name},\n\nPalun too laenutatud seade \"{$deviceName}\" tagasi hiljemalt {$dueDate} kell {$dueTime}.\n\nTäname!",
                function ($message) use ($borrowing, $deviceName) {
                    $message->to($borrowing->student_email, $borrowing->student_name)
                            ->subject("Meeldetuletus: tagasta seade \"{$deviceName}\"");
                }
            );
            $borrowing->update(['notification_sent' => true]);
            return response()->json(['message' => 'Teavitus saadetud.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Teavituse saatmine ebaõnnestus: ' . $e->getMessage()], 500);
        }
    }
}
