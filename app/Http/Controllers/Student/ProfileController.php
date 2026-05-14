<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show profile page
     */
    public function show()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();

        return view('student.profile', compact('user', 'internship'));
    }

    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Normalize phone: strip leading 0, prepend +60 before validation
        if ($request->filled('phone')) {
            $rawPhone = preg_replace('/[^0-9]/', '', $request->phone); // digits only
            $rawPhone = ltrim($rawPhone, '0');
            $request->merge(['phone' => '+60'.$rawPhone]);
        }

        $request->validate([
            'phone' => 'nullable|regex:/^\+60[0-9]{8,12}$/|unique:users,phone,'.$user->id,
            'faculty' => 'nullable|string|max:255',
            'class' => 'nullable|string|max:255',
            'programme_code' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'about' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'phone.regex' => 'Please enter a valid Malaysian phone number (digits only, 8-12 digits).',
            'phone.unique' => 'This phone number is already registered by another user.',
            'end_date.after_or_equal' => 'End date must be after or equal to the start date.',
        ]);

        $data = $request->only([
            'phone', 'faculty', 'class', 'programme_code', 'location', 'about',
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar from Cloudinary if it's a Cloudinary URL
            if ($user->avatar && str_contains($user->avatar, 'cloudinary')) {
                try {
                    $publicId = pathinfo(parse_url($user->avatar, PHP_URL_PATH), PATHINFO_FILENAME);
                    cloudinary()->adminApi()->deleteAssets(['lims/avatars/'.$publicId]);
                } catch (\Exception $e) { /* ignore delete errors */
                }
            } elseif ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            if (env('CLOUDINARY_URL')) {
                $uploaded = cloudinary()->uploadApi()->upload($request->file('avatar')->getRealPath(), [
                    'folder' => 'lims/avatars',
                ]);
                $data['avatar'] = $uploaded['secure_url'];
            } else {
                $data['avatar'] = asset('storage/'.$request->file('avatar')->store('avatars', 'public'));
            }
        }

        $user->update($data);

        // Update or create Internship dates
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $totalWeeks = ceil($startDate->diffInDays($endDate) / 7);

            Internship::updateOrCreate(
                ['student_id' => $user->id],
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_weeks' => $totalWeeks,
                    'company_name' => $user->company ?? 'Not Set',
                    'company_address' => $request->location ?? '-',
                ]
            );
        }

        return redirect()->route('student.profile')->with('success', 'Profile updated successfully.');
    }
}
