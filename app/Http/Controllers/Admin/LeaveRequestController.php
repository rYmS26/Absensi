<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the leave requests.
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with('user');

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        $users = User::where('role', 'user')->get();

        return view('admin.leave.index', compact('leaveRequests', 'users'));
    }

    /**
     * Show the specified leave request.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        return view('admin.leave.show', compact('leaveRequest'));
    }

    /**
     * Update the status of the specified leave request.
     */
    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'nullable|string',
        ]);

        $leaveRequest->update([
            'status' => $request->status,
            'admin_remarks' => $request->admin_remarks,
        ]);

        return redirect()->route('admin.leave.index')->with('success', 'Leave request status updated successfully.');
    }
}
