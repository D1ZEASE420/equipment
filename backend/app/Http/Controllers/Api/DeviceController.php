<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Device::with(['activeBorrowing.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $devices = $query->orderBy('name')->get()->map(function (Device $device) {
            $borrowing = $device->activeBorrowing;
            return [
                'id'            => $device->id,
                'name'          => $device->name,
                'serial_number' => $device->serial_number,
                'barcode'       => $device->barcode,
                'status'        => $device->status,
                'description'   => $device->description,
                'created_at'    => $device->created_at,
                'borrowing'     => $borrowing ? [
                    'id'           => $borrowing->id,
                    'borrowed_at'  => $borrowing->borrowed_at,
                    'due_date'     => $borrowing->due_date,
                    'status_color' => $borrowing->status_color,
                    'user'         => ['id' => $borrowing->user->id, 'name' => $borrowing->user->name],
                ] : null,
            ];
        });

        return response()->json($devices);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:255', 'unique:devices'],
            'barcode'       => ['required', 'string', 'max:255', 'unique:devices'],
            'description'   => ['nullable', 'string'],
        ]);

        $device = Device::create($validated);
        return response()->json($device, 201);
    }

    public function show(Device $device): JsonResponse
    {
        $device->load(['activeBorrowing.user', 'borrowings.user']);
        return response()->json($device);
    }

    public function update(Request $request, Device $device): JsonResponse
    {
        $validated = $request->validate([
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'serial_number' => ['sometimes', 'required', 'string', 'max:255', 'unique:devices,serial_number,' . $device->id],
            'barcode'       => ['sometimes', 'required', 'string', 'max:255', 'unique:devices,barcode,' . $device->id],
            'description'   => ['nullable', 'string'],
        ]);

        $device->update($validated);
        return response()->json($device);
    }

    public function destroy(Device $device): JsonResponse
    {
        if ($device->isBorrowed()) {
            return response()->json(['message' => 'Cannot delete a device that is currently borrowed.'], 422);
        }

        $device->delete();
        return response()->json(['message' => 'Device deleted successfully.']);
    }
}
