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
        $supervisors = User::where('role', 'supervisor')->get();
        return view('auth.login', compact('supervisors'));
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
        $request->validate([
            'name' => 'required|string|max:255',
            'matrix_id' => 'required|numeric|unique:users',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'phone' => ['required', 'regex:/^01[0-9]{8,9}$/', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:student,supervisor',
            'company' => 'required|string|max:255',
            'supervisor_id' => $request->role === 'student' ? 'required|exists:users,id' : 'nullable|exists:users,id',
        ], [
            'phone.regex' => 'Please enter a valid Malaysian phone number (e.g., 0123456789).',
            'phone.unique' => 'This phone number is already registered.',
            'matrix_id.numeric' => 'Student / Supervisor ID must contain numbers only.',
            'matrix_id.unique' => 'This ID is already registered.',
            'supervisor_id.required' => 'Please select a supervisor.',
            'email.regex' => 'Please enter a valid email address (e.g., name@example.com).',
        ]);

        $user = User::create([
            'name' => $request->name,
            'matrix_id' => $request->matrix_id,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'company' => $request->company,
            'supervisor_id' => $request->role === 'student' ? $request->supervisor_id : null,
        ]);

        Auth::login($user);

        if ($user->role === 'supervisor') {
            return redirect()->route('supervisor.dashboard');
        }
        return redirect()->route('student.dashboard');
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
