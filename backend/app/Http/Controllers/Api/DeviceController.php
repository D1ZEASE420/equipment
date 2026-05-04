<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

        if ($request->has('capacity') && $request->capacity !== '') {
            $query->where('capacity', $request->capacity);
        }

        // loanable filter: ?loanable=0 shows non-loanable, ?loanable=1 or omitted shows all
        if ($request->has('loanable') && $request->loanable !== '') {
            $query->where('loanable', (bool) $request->loanable);
        }

        // Available first, then alphabetical
        $devices = $query
            ->orderByRaw("CASE WHEN status = 'available' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->map(fn(Device $d) => $this->formatDevice($d));

        return response()->json($devices);
    }

    public function export(Request $request): Response
    {
        $query = Device::with(['activeBorrowing']);

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $devices = $query
            ->orderByRaw("CASE WHEN status = 'available' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $rows   = [];
        $rows[] = implode(',', ['Nimi', 'Kategooria', 'Seerianumber', 'Vöökood', 'Olek', 'Laenutatav', 'Maht', 'Kirjeldus']);

        foreach ($devices as $d) {
            $rows[] = implode(',', array_map(
                fn($v) => '"' . str_replace('"', '""', (string) $v) . '"',
                [
                    $d->name,
                    $d->category ?? '',
                    $d->serial_number,
                    $d->barcode,
                    $d->status === 'available' ? 'Saadaval' : 'Laenutatud',
                    $d->loanable ? 'Jah' : 'Ei',
                    $d->capacity ?? '',
                    $d->description ?? '',
                ]
            ));
        }

        $csv = "\xEF\xBB\xBF" . implode("\n", $rows);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="seadmed.csv"',
        ]);
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
            'loanable'      => ['sometimes', 'boolean'],
            'capacity'      => ['nullable', 'string', 'max:50'],
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
            'loanable'      => ['sometimes', 'boolean'],
            'capacity'      => ['nullable', 'string', 'max:50'],
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

    public function categories(): JsonResponse
    {
        $fromDevices = Device::whereNotNull('category')
            ->distinct()->pluck('category')->toArray();
        $fromTable = \App\Models\Category::pluck('name')->toArray();
        $merged = array_values(array_unique(array_merge($fromDevices, $fromTable)));
        sort($merged);
        return response()->json($merged);
    }

    public function capacities(): JsonResponse
    {
        $caps = Device::whereNotNull('capacity')
            ->distinct()
            ->orderBy('capacity')
            ->pluck('capacity');

        return response()->json($caps);
    }

    private function formatDevice(Device $d): array
    {
        $borrowing = $d->activeBorrowing;
        return [
            'id'            => $d->id,
            'name'          => $d->name,
            'serial_number' => $d->serial_number,
            'barcode'       => $d->barcode,
            'status'        => $d->status,
            'description'   => $d->description,
            'category'      => $d->category,
            'loanable'      => $d->loanable,
            'capacity'      => $d->capacity,
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
    }
}