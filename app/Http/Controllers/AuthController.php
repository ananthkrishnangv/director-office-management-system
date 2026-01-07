<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        $labs = Lab::active()->select('id', 'lab_code', 'lab_name', 'full_name')->get();

        return Inertia::render('Auth/Login', [
            'labs' => $labs
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'lab_id' => 'required|exists:labs,id',
        ]);

        // Find user with matching lab
        $user = User::where('email', $credentials['email'])
            ->where('lab_id', $credentials['lab_id'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect or user does not belong to this lab.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact administrator.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->last_login_at = now();
        $user->save();

        // Store lab in session
        $request->session()->put('current_lab_id', $user->lab_id);

        if ($request->wantsJson()) {
            return response()->json([
                'user' => $user->load('lab'),
                'token' => $user->createToken('auth-token')->plainTextToken,
            ]);
        }

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Logged out']);
        }

        return redirect('/login');
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('lab'));
    }

    public function showProfile()
    {
        return Inertia::render('Profile/Index');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
        ]);

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['user' => $user->fresh()]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($validated['password'])]);
        return response()->json(['message' => 'Password updated']);
    }
}
