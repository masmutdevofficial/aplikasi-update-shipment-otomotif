<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get paginated list of users with optional search.
     */
    public function getUsers(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get users filtered by level (for admin who can only manage vendors).
     */
    public function getUsersByLevel(string $level, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::where('level', $level)->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get all users without pagination (for DataTables).
     */
    public function getAllUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()->orderBy('name')->get();
    }

    /**
     * Get all users filtered by level without pagination (for DataTables).
     */
    public function getAllUsersByLevel(string $level): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('level', $level)->orderBy('name')->get();
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data, string $createdBy): User
    {
        return User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'level' => $data['level'],
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $createdBy,
            'updated_by' => $createdBy,
        ]);
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data, string $updatedBy): User
    {
        $updateData = [
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'level' => $data['level'],
            'is_active' => $data['is_active'] ?? $user->is_active,
            'updated_by' => $updatedBy,
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user->fresh();
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user, string $updatedBy): User
    {
        $user->update([
            'is_active' => ! $user->is_active,
            'updated_by' => $updatedBy,
        ]);

        // If user was deactivated, invalidate their sessions
        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return $user->fresh();
    }

    /**
     * Delete a user (with business rule: cannot delete last superadmin).
     *
     * @return array{success: bool, message: string}
     */
    public function deleteUser(User $user): array
    {
        // Business rule: cannot delete last superadmin
        if ($user->isSuperadmin()) {
            $superadminCount = User::where('level', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat menghapus superadmin terakhir dalam sistem.',
                ];
            }
        }

        // Remove sessions before deleting
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->delete();

        return [
            'success' => true,
            'message' => 'User berhasil dihapus.',
        ];
    }

    /**
     * Get available levels based on current user's level.
     */
    public function getAvailableLevels(User $currentUser): array
    {
        if ($currentUser->isSuperadmin()) {
            return ['superadmin', 'admin', 'vendor'];
        }

        // Admin can only manage vendor-level users
        return ['vendor'];
    }
}
