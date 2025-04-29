<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Get today's date
        $today = Carbon::today()->toDateString();

        // Count total users
        $totalUsers = User::where('role', 'user')->count();

        // Count today's attendance
        $todayPresent = Attendance::where('date', $today)
            ->where('status', 'present')
            ->count();

        $todayLate = Attendance::where('date', $today)
            ->where('status', 'late')
            ->count();

        $todayAbsent = $totalUsers - ($todayPresent + $todayLate);

        // Count pending leave requests
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();

        // Get attendance stats for the last 7 days
        $lastWeek = Carbon::today()->subDays(6);
        $attendanceStats = Attendance::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(CASE WHEN status = "present" THEN 1 END) as present_count'),
                DB::raw('COUNT(CASE WHEN status = "late" THEN 1 END) as late_count'),
                DB::raw('COUNT(CASE WHEN status = "absent" THEN 1 END) as absent_count')
            )
            ->where('date', '>=', $lastWeek)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get recent leave requests
        $recentLeaves = LeaveRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'todayPresent',
            'todayLate',
            'todayAbsent',
            'pendingLeaves',
            'attendanceStats',
            'recentLeaves'
        ));
    }
}
