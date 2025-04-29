<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Display the attendance form.
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $officeLocations = OfficeLocation::where('is_active', true)->get();

        return view('attendance.index', compact('attendance', 'officeLocations'));
    }

    /**
     * Verify if user is within office location.
     */
    public function verifyLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid location data provided.',
                'errors' => $validator->errors()
            ], 422);
        }

        $isWithinOffice = $this->checkLocationProximity(
            $request->latitude,
            $request->longitude
        );

        if (!$isWithinOffice) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, you are not within the office location.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Location verified successfully.'
        ]);
    }

    /**
     * Record check-in.
     */
    public function checkIn(Request $request)
    {
        // Check if using alternative method
        $isAlternativeMethod = $request->has('alternative_method') && $request->alternative_method === 'true';

        // Validate request based on method
        if (!$isAlternativeMethod) {
            $request->validate([
                'photo' => 'required|image|max:2048',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);
        }

        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->format('H:i:s');

        // Check if already checked in
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && $attendance->check_in) {
            return redirect()->back()->with('error', 'You have already checked in today.');
        }

        // For standard method, verify location
        if (!$isAlternativeMethod) {
            $isWithinOffice = $this->checkLocationProximity($request->latitude, $request->longitude);

            if (!$isWithinOffice) {
                return redirect()->back()->with('error', 'Sorry, you are not within the office location.');
            }
        }

        // Prepare attendance data
        $attendanceData = [
            'check_in' => $now,
            'check_in_method' => $isAlternativeMethod ? 'alternative' : 'standard',
            'status' => Carbon::now()->format('H') >= 9 ? 'late' : 'present',
        ];

        // For standard method, store photo and location
        if (!$isAlternativeMethod) {
            $photoPath = $request->file('photo')->store('attendance_photos', 'public');
            $attendanceData['check_in_photo'] = $photoPath;
            $attendanceData['check_in_location'] = [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ];
        }

        // Create or update attendance record
        if (!$attendance) {
            $attendance = new Attendance(array_merge([
                'user_id' => $user->id,
                'date' => $today,
            ], $attendanceData));
            $attendance->save();
        } else {
            $attendance->update($attendanceData);
        }

        $message = $isAlternativeMethod
            ? 'Check-in recorded successfully using alternative method.'
            : 'Check-in recorded successfully.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Record check-out.
     */
    public function checkOut(Request $request)
    {
        // Check if using alternative method
        $isAlternativeMethod = $request->has('alternative_method') && $request->alternative_method === 'true';

        // Validate request based on method
        if (!$isAlternativeMethod) {
            $request->validate([
                'photo' => 'required|image|max:2048',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);
        }

        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->format('H:i:s');

        // Check if already checked out
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return redirect()->back()->with('error', 'You need to check in first.');
        }

        if ($attendance->check_out) {
            return redirect()->back()->with('error', 'You have already checked out today.');
        }

        // For standard method, verify location
        if (!$isAlternativeMethod) {
            $isWithinOffice = $this->checkLocationProximity($request->latitude, $request->longitude);

            if (!$isWithinOffice) {
                return redirect()->back()->with('error', 'Sorry, you are not within the office location.');
            }
        }

        // Prepare attendance data
        $attendanceData = [
            'check_out' => $now,
            'check_out_method' => $isAlternativeMethod ? 'alternative' : 'standard',
        ];

        // For standard method, store photo and location
        if (!$isAlternativeMethod) {
            $photoPath = $request->file('photo')->store('attendance_photos', 'public');
            $attendanceData['check_out_photo'] = $photoPath;
            $attendanceData['check_out_location'] = [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ];
        }

        // Update attendance record
        $attendance->update($attendanceData);

        $message = $isAlternativeMethod
            ? 'Check-out recorded successfully using alternative method.'
            : 'Check-out recorded successfully.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Alternative check-in method (no verification).
     */
    public function alternativeCheckIn(Request $request)
    {
        $request->merge(['alternative_method' => 'true']);
        return $this->checkIn($request);
    }

    /**
     * Alternative check-out method (no verification).
     */
    public function alternativeCheckOut(Request $request)
    {
        $request->merge(['alternative_method' => 'true']);
        return $this->checkOut($request);
    }

    /**
     * Verify if the user is within office premises.
     */
    private function checkLocationProximity($latitude, $longitude)
    {
        $officeLocations = OfficeLocation::where('is_active', true)->get();

        if ($officeLocations->isEmpty()) {
            // If no office locations are defined, allow check-in from anywhere
            return true;
        }

        foreach ($officeLocations as $location) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $location->latitude,
                $location->longitude
            );

            if ($distance <= $location->radius) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate distance between two coordinates in meters.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * Display user's attendance history.
     */
    public function history()
    {
        $user = auth()->user();
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('attendance.history', compact('attendances'));
    }
}
