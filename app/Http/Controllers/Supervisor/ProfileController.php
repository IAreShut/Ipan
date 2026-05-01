<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show supervisor profile page
     */
    public function show()
    {
        $user = Auth::user();
        $students = $user->students()->get();
        return view('supervisor.profile', compact('user', 'students'));
    }

    /**
     * Update supervisor profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Normalize phone: strip leading 0, prepend +60 before validation
        if ($request->filled('phone')) {
            $rawPhone = preg_replace('/[^0-9]/', '', $request->phone);
            $rawPhone = ltrim($rawPhone, '0');
            $request->merge(['phone' => '+60' . $rawPhone]);
        }

        $request->validate([
            'phone' => 'nullable|regex:/^\+60[0-9]{8,12}$/|unique:users,phone,' . $user->id,
            'employee_id' => 'nullable|string|max:50',
            'faculty' => 'nullable|string|max:255',
            'class' => 'nullable|array',
            'class.*' => 'string|max:255',
            'programme_code' => 'nullable|array',
            'programme_code.*' => 'string|max:50',
            'location' => 'nullable|string|max:255',
            'about' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'phone.regex' => 'Please enter a valid Malaysian phone number (digits only, 8-12 digits).',
            'phone.unique' => 'This phone number is already registered by another user.',
        ]);

        $data = $request->only([
            'phone', 'employee_id', 'faculty', 'location', 'about'
        ]);

        // Store class as JSON array
        if ($request->has('class')) {
            $classes = array_filter(array_map('trim', $request->class));
            $data['class'] = empty($classes) ? null : json_encode(array_values($classes));
        } else {
            $data['class'] = null;
        }

        // Store programme_code as JSON array
        if ($request->has('programme_code')) {
            $codes = array_filter(array_map('trim', $request->programme_code));
            $data['programme_code'] = empty($codes) ? null : json_encode(array_values($codes));
        } else {
            $data['programme_code'] = null;
        }

        // Handle avatar upload (dual-path: Cloudinary / Local)
        if ($request->hasFile('avatar')) {
            // Delete old avatar from Cloudinary if it's a Cloudinary URL
            if ($user->avatar && str_contains($user->avatar, 'cloudinary')) {
                try {
                    $publicId = pathinfo(parse_url($user->avatar, PHP_URL_PATH), PATHINFO_FILENAME);
                    cloudinary()->adminApi()->deleteAssets(['lims/avatars/' . $publicId]);
                } catch (\Exception $e) { /* ignore delete errors */ }
            } elseif ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            if (env('CLOUDINARY_URL')) {
                $uploaded = cloudinary()->uploadApi()->upload($request->file('avatar')->getRealPath(), [
                    'folder' => 'lims/avatars',
                ]);
                $data['avatar'] = $uploaded['secure_url'];
            } else {
                $data['avatar'] = asset('storage/' . $request->file('avatar')->store('avatars', 'public'));
            }
        }

        $user->update($data);

        return redirect()->route('supervisor.profile')->with('success', 'Profile updated successfully.');
    }
}
