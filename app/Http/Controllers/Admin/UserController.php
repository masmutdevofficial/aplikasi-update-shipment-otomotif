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
        $data = $request->validated();
        $requestedStatus = $data['status']
            ?? (array_key_exists('is_active', $data)
                ? ($data['is_active'] ? User::STATUS_ACTIVE : User::STATUS_INACTIVE)
                : $user->status);

        if ($user->id === $request->user()->id
            && $requestedStatus !== User::STATUS_ACTIVE) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun sendiri menjadi Pending atau Nonaktif.');
        }

        $this->userService->updateUser(
            $user,
            $data,
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

        $status = $user->fresh()->status === User::STATUS_ACTIVE ? 'diaktifkan' : 'dinonaktifkan';

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

    /**
     * Remove the selected users.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'uuid', 'distinct', 'exists:users,id'],
        ]);

        $currentUser = $request->user();
        $users = User::whereIn('id', $data['user_ids'])->get();

        if ($currentUser->isAdmin() && $users->contains(fn (User $user) => $user->level !== 'vendor')) {
            abort(403, 'Akses ditolak.');
        }

        if ($users->contains('id', $currentUser->id)) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $result = $this->userService->deleteUsers($users);

        return redirect()
            ->route('admin.users.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
