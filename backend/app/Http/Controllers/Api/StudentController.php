<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // GET /students — list all students
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        if ($request->has('search')) {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return response()->json($query->orderBy('name')->get());
    }

    // POST /students — create one student
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:students,email'],
            'group' => ['nullable', 'string', 'max:100'],
        ]);

        $student = Student::create($data);

        return response()->json($student, 201);
    }

    // PUT /students/{id} — update student
    public function update(Request $request, Student $student): JsonResponse
    {
        $data = $request->validate([
            'name'   => ['sometimes', 'string', 'max:255'],
            'email'  => ['sometimes', 'email', 'unique:students,email,' . $student->id],
            'group'  => ['nullable', 'string', 'max:100'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $student->update($data);

        return response()->json($student);
    }

    // DELETE /students/{id} — delete one student
public function destroy(Student $student): JsonResponse
{
    // Hard backend check: block delete if student has active borrowings
    $activeBorrowings = Borrowing::where('student_email', $student->email)
        ->whereNull('returned_at')
        ->count();

    if ($activeBorrowings > 0) {
        return response()->json([
            'message'         => "Õpilasel on {$activeBorrowings} aktiivne laenatus. Kustutamine blokeeritud.",
            'active_count'    => $activeBorrowings,
        ], 422);
    }

    $student->delete();
    return response()->json(['message' => 'Kustutatud.']);
}

    // POST /students/import — bulk import from JSON array
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'students'         => ['required', 'array'],
            'students.*.name'  => ['required', 'string'],
            'students.*.email' => ['required', 'email'],
            'students.*.group' => ['nullable', 'string'],
        ]);

        $created = 0;
        $skipped = 0;

        foreach ($request->students as $row) {
            $exists = Student::where('email', $row['email'])->exists();
            if ($exists) {
                $skipped++;
                continue;
            }
            Student::create([
                'name'  => $row['name'],
                'email' => $row['email'],
                'group' => $row['group'] ?? null,
            ]);
            $created++;
        }

        return response()->json(['created' => $created, 'skipped' => $skipped]);
    }

    // DELETE /students/group/{group} — delete whole group
    public function destroyGroup(string $group): JsonResponse
    {
        $count = Student::where('group', $group)->count();
        Student::where('group', $group)->delete();
        return response()->json(['message' => "Kustutati {$count} õpilast rühmast '{$group}'."]);
    }

    // GET /students/{id}/borrowings — borrowing history for a specific student
    public function borrowingHistory(Student $student): JsonResponse
    {
        // Match by student_email stored on borrowings (set when borrowing was created)
        $borrowings = Borrowing::with(['device', 'user'])
            ->where('student_email', $student->email)
            ->orderByDesc('borrowed_at')
            ->get()
            ->map(function (Borrowing $b) {
                $overdue = $b->returned_at === null
                    && now()->greaterThan($b->due_date);

                return [
                    'id'           => $b->id,
                    'device'       => $b->device ? ['id' => $b->device->id, 'name' => $b->device->name] : null,
                    'borrowed_by'  => $b->user ? $b->user->name : null,
                    'borrowed_at'  => $b->borrowed_at,
                    'due_date'     => $b->due_date,
                    'due_time'     => $b->due_time,
                    'returned_at'  => $b->returned_at,
                    'status_color' => $b->status_color,
                    'overdue'      => $overdue,
                    'was_late'     => $b->returned_at !== null
                        && \Carbon\Carbon::parse($b->returned_at)->greaterThan($b->due_date),
                ];
            });

        // Summary stats
        $total   = $borrowings->count();
        $late    = $borrowings->where('was_late', true)->count();
        $active  = $borrowings->whereNull('returned_at')->count();

        return response()->json([
            'student'   => $student,
            'borrowings' => $borrowings,
            'stats'     => [
                'total'          => $total,
                'returned_late'  => $late,
                'currently_out'  => $active,
            ],
        ]);
    }
}
