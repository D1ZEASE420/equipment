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

        if ($request->has('categories') && $request->categories !== '') {
            $cats = explode(',', $request->categories);
            $query->whereIn('category', $cats);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('serial_number', 'like', "%{$q}%")
                   ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        $devices = $query->orderBy('name')->get()->map(function (Device $d) {
            $borrowing = $d->activeBorrowing;
            return [
                'id'            => $d->id,
                'name'          => $d->name,
                'serial_number' => $d->serial_number,
                'barcode'       => $d->barcode,
                'status'        => $d->status,
                'description'   => $d->description,
                'category'      => $d->category,
                'borrowing'     => $borrowing ? [
                    'id'            => $borrowing->id,
                    'user'          => $borrowing->user ? ['id' => $borrowing->user->id, 'name' => $borrowing->user->name] : null,
                    'student_name'  => $borrowing->student_name,
                    'student_email' => $borrowing->student_email,
                    'due_date'      => $borrowing->due_date,
                    'due_time'      => $borrowing->due_time,
                    'status_color'  => $borrowing->status_color,
                ] : null,
            ];
        });

        return response()->json($devices);
    }

    public function show(Device $device): JsonResponse
    {
        $device->load('activeBorrowing.user');
        return response()->json($device);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'unique:devices'],
            'barcode'       => ['required', 'string', 'unique:devices'],
            'description'   => ['nullable', 'string'],
            'category'      => ['nullable', 'string', 'max:100'],
        ]);

        $device = Device::create($data);
        return response()->json($device, 201);
    }

    public function update(Request $request, Device $device): JsonResponse
    {
        $data = $request->validate([
            'name'          => ['sometimes', 'string', 'max:255'],
            'serial_number' => ['sometimes', 'string', 'unique:devices,serial_number,' . $device->id],
            'barcode'       => ['sometimes', 'string', 'unique:devices,barcode,' . $device->id],
            'description'   => ['nullable', 'string'],
            'category'      => ['nullable', 'string', 'max:100'],
        ]);

        $device->update($data);
        return response()->json($device);
    }

    public function destroy(Device $device): JsonResponse
    {
        if ($device->isBorrowed()) {
            return response()->json(['message' => 'Laenutatud seadet ei saa kustutada.'], 422);
        }
        $device->delete();
        return response()->json(['message' => 'Seade kustutatud.']);
    }

    // GET /devices/categories — list all unique categories
    public function categories(): JsonResponse
    {
        $cats = Device::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json($cats);
    }
}
