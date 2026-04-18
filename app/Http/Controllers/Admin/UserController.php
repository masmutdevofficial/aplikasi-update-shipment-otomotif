<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();

        if ($currentUser->isAdmin()) {
            $users = $this->userService->getAllUsersByLevel('vendor');
        } else {
            $users = $this->userService->getAllUsers();
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request): View
    {
        $levels = $this->userService->getAvailableLevels($request->user());

        return view('admin.users.create', compact('levels'));
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser(
            $request->validated(),
            $request->user()->id,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(Request $request, User $user): RedirectResponse|View
    {
        $currentUser = $request->user();

        // Admin cannot edit non-vendor users
        if ($currentUser->isAdmin() && $user->level !== 'vendor') {
            abort(403, 'Akses ditolak.');
        }

        $levels = $this->userService->getAvailableLevels($currentUser);

        return view('admin.users.edit', compact('user', 'levels'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser(
            $user,
            $request->validated(),
            $request->user()->id,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Toggle user active/inactive status.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        // Admin cannot toggle non-vendor users
        if ($currentUser->isAdmin() && $user->level !== 'vendor') {
            abort(403, 'Akses ditolak.');
        }

        // Cannot deactivate yourself
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $this->userService->toggleStatus($user, $currentUser->id);

        $status = $user->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User berhasil {$status}.");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        // Admin cannot delete non-vendor users
        if ($currentUser->isAdmin() && $user->level !== 'vendor') {
            abort(403, 'Akses ditolak.');
        }

        // Cannot delete yourself
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $result = $this->userService->deleteUser($user);

        return redirect()
            ->route('admin.users.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
