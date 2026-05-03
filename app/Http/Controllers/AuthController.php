<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show login/register page
     */
    public function showLoginForm()
    {
        $supervisors = User::where('role', 'supervisor')
            ->whereNotNull('faculty')
            ->whereNotNull('groups')
            ->get();

        // Build distinct values for cascading dropdowns
        $faculties = $supervisors->pluck('faculty')->filter()->unique()->sort()->values();

        // Pass full supervisor criteria as JSON for JS cascading filter
        $supervisorCriteria = $supervisors->map(function ($sv) {
            return [
                'id' => $sv->id,
                'name' => $sv->name,
                'faculty' => $sv->faculty,
                'groups' => $sv->groups,
            ];
        })->values();

        return view('auth.login', compact('supervisors', 'faculties', 'supervisorCriteria'));
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required', // Removed 'email' rule to allow ID
            'password' => 'required',
        ]);

        $fieldType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'matrix_id';

        if (Auth::attempt([$fieldType => $request->email, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect based on role
            $user = Auth::user();
            if ($user->role === 'supervisor') {
                return redirect()->intended(route('supervisor.dashboard'));
            } elseif ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }
            return redirect()->intended(route('student.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'matrix_id' => 'required|numeric|unique:users',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'phone' => ['required', 'regex:/^01[0-9]{8,9}$/', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:student,supervisor',
        ];

        $messages = [
            'phone.regex' => 'Please enter a valid Malaysian phone number (e.g., 0123456789).',
            'phone.unique' => 'This phone number is already registered.',
            'matrix_id.numeric' => 'Student / Supervisor ID must contain numbers only.',
            'matrix_id.unique' => 'This ID is already registered.',
            'email.regex' => 'Please enter a valid email address (e.g., name@example.com).',
        ];

        // Student-specific fields
        if ($request->role === 'student') {
            $rules['company'] = 'required|string|max:255';
            $rules['reg_faculty'] = 'required|string|max:255';
            $rules['reg_class'] = 'required|string|max:255';
            $rules['reg_programme_code'] = 'required|string|max:100';
            $messages['reg_faculty.required'] = 'Please select a faculty.';
            $messages['reg_class.required'] = 'Please select a class.';
            $messages['reg_programme_code.required'] = 'Please select a programme code.';
        } else {
            $rules['company'] = 'nullable|string|max:255';
        }

        $request->validate($rules, $messages);

        // Auto-assign supervisor for students
        $supervisorId = null;
        if ($request->role === 'student') {
            $supervisor = User::where('role', 'supervisor')
                ->where('faculty', $request->reg_faculty)
                ->get()
                ->first(function ($sv) use ($request) {
                    return $sv->matchesCriteria(
                        $request->reg_faculty,
                        $request->reg_class,
                        $request->reg_programme_code
                    );
                });

            if (!$supervisor) {
                return back()->withErrors([
                    'reg_faculty' => 'No supervisor is assigned for your Faculty / Class / Programme Code combination. Please contact your faculty administrator.',
                ])->withInput();
            }

            $supervisorId = $supervisor->id;
        }

        $user = User::create([
            'name' => $request->name,
            'matrix_id' => $request->matrix_id,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'company' => $request->company,
            'supervisor_id' => $supervisorId,
            'faculty' => $request->role === 'student' ? $request->reg_faculty : null,
            'class' => $request->role === 'student' ? $request->reg_class : null,
            'programme_code' => $request->role === 'student' ? $request->reg_programme_code : null,
        ]);

        return redirect()->route('login')->with('success', 'Registration successful! Please login to continue.');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
