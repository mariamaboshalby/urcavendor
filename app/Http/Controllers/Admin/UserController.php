<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(15);

        $stats = [
            'total'   => User::count(),
            'admins'  => User::where('role', 'admin')->count(),
            'vendors' => User::where('role', 'vendor')->count(),
            'active'  => User::where('is_active', true)->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:admin,user,vendor,customer',
            'password' => ['required', Password::min(8)],
            'is_active' => 'boolean',
        ]);

        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إضافة المستخدم بنجاح');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:admin,user,vendor,customer',
            'password' => ['nullable', Password::min(8)],
            'is_active' => 'boolean',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $msg = $user->is_active ? 'تم تفعيل المستخدم' : 'تم تعطيل المستخدم';
        return back()->with('success', $msg);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }
}
