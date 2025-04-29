<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function index(Request $request)
    {
        $query = Attendance::with('user');

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('user_id')
            ->paginate(15);

        $users = User::where('role', 'user')->get();

        return view('admin.attendance.index', compact('attendances', 'users'));
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create()
    {
        $users = User::where('role', 'user')->get();
        return view('admin.attendance.create', compact('users'));
    }

    /**
     * Store a newly created attendance record in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late',
            'notes' => 'nullable|string',
        ]);

        // Check if attendance record already exists
        $exists = Attendance::where('user_id', $request->user_id)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Attendance record already exists for this user on this date.');
        }

        Attendance::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.attendance.index')->with('success', 'Attendance record created successfully.');
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance)
    {
        return view('admin.attendance.edit', compact('attendance'));
    }

    /**
     * Update the specified attendance record in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late',
            'notes' => 'nullable|string',
        ]);

        $attendance->update([
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.attendance.index')->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Generate attendance report.
     */
    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'required|in:daily,summary',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        if ($request->report_type === 'daily') {
            $report = Attendance::with('user')
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date')
                ->orderBy('user_id')
                ->get()
                ->groupBy('date');

            return view('admin.attendance.reports.daily', compact('report', 'startDate', 'endDate'));
        } else {
            $report = User::where('role', 'user')
                ->withCount([
                    'attendances as present_count' => function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('date', [$startDate, $endDate])
                              ->where('status', 'present');
                    },
                    'attendances as late_count' => function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('date', [$startDate, $endDate])
                              ->where('status', 'late');
                    },
                    'attendances as absent_count' => function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('date', [$startDate, $endDate])
                              ->where('status', 'absent');
                    },
                ])
                ->get();

            // Calculate working days in the period
            $workingDays = $this->calculateWorkingDays($startDate, $endDate);

            return view('admin.attendance.reports.summary', compact('report', 'startDate', 'endDate', 'workingDays'));
        }
    }

    /**
     * Calculate the number of working days between two dates.
     */
    private function calculateWorkingDays($startDate, $endDate)
    {
        $days = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Skip weekends (Saturday and Sunday)
            if ($current->dayOfWeek !== 0 && $current->dayOfWeek !== 6) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }
}
