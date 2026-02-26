<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $roles = Role::all();

        return view('users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function settings()
    {
        return view('settings.index');
    }

    public function changePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Current password is required',
            'password.required' => 'New password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        // Update password
        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully!');
    }

    public function store(Request $request)
    {
        // Check if this is an update operation (has id field)
        $isUpdate = $request->filled('id');
        $userId = $isUpdate ? $request->id : null;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => $isUpdate ? 'required|email|unique:users,email,' . $userId : 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'password' => $isUpdate ? 'nullable|string|min:8' : 'required|string|min:8',
            'is_active' => 'required|boolean',
        ];

        $request->validate($rules);

        try {
            if ($isUpdate) {
                // Update existing user
                $user = User::findOrFail($userId);

                $data = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'role_id' => $request->role_id,
                    'is_active' => $request->is_active,
                ];

                if ($request->filled('password')) {
                    $data['password'] = Hash::make($request->password);
                }

                $user->update($data);
                $message = 'User updated successfully!';
            } else {
                // Create new user
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'role_id' => $request->role_id,
                    'password' => Hash::make($request->password),
                    'is_active' => $request->is_active,
                ]);
                $message = 'User created successfully!';
            }

            // For AJAX requests, return JSON
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $user,
                ]);
            }

            // For regular form submissions, redirect with flash message
            return redirect()->route('users.index')->with('success', $message);
        } catch (\Exception $e) {
            // For AJAX requests, return JSON error
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save user: ' . $e->getMessage(),
                ], 500);
            }

            // For regular form submissions, redirect back with error
            return redirect()->back()
                ->withErrors(['error' => 'Failed to save user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            // For AJAX requests, return JSON
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $user,
                ]);
            }

            // For regular requests, redirect or show view
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            // For AJAX requests, return JSON error
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // For regular requests, show 404 page
            abort(404, 'User not found');
        }
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8',
            'is_active' => 'required|boolean',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role_id' => $request->role_id,
                'is_active' => $request->is_active,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // For AJAX requests, return JSON
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'data' => $user,
                ]);
            }

            // For regular form submissions, redirect with flash message
            return redirect()->route('users.index')->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            // For AJAX requests, return JSON error
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: ' . $e->getMessage(),
                ], 500);
            }

            // For regular form submissions, redirect back with error
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(User $user)
    {
        try {
            // Prevent deleting own account
            if ($user->id === Auth::id()) {
                // For AJAX requests, return JSON error
                if (request()->expectsJson() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot delete your own account',
                    ], 403);
                }

                // For regular requests, redirect with error
                return redirect()->route('users.index')
                    ->withErrors(['error' => 'You cannot delete your own account']);
            }

            $userName = $user->name;
            $user->delete();

            // For AJAX requests, return JSON success
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully',
                ]);
            }

            // For regular requests, redirect with success message
            return redirect()->route('users.index')
                ->with('success', "User '{$userName}' deleted successfully!");
        } catch (\Exception $e) {
            // For AJAX requests, return JSON error
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage(),
                ], 500);
            }

            // For regular requests, redirect with error
            return redirect()->route('users.index')
                ->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }
}
