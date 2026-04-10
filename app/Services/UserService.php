<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get all users
     */
    public function getAll(): Collection
    {
        return User::latest('created_at')->get();
    }

    /**
     * Get user by ID
     */
    public function getById(string $id): ?User
    {
        return User::findOrFail($id);
    }

    /**
     * Get user by email
     */
    public function getByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create user
     */
    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'editor';

        return User::create($data);
    }

    /**
     * Update user
     */
    public function update(User $user, array $data): User
    {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return $user;
    }

    /**
     * Delete user
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Search users
     */
    public function search(string $term): Collection
    {
        return User::search($term)->get();
    }

    /**
     * Get all admins
     */
    public function getAdmins(): Collection
    {
        return User::admins()->get();
    }

    /**
     * Get all editors
     */
    public function getEditors(): Collection
    {
        return User::editors()->get();
    }

    /**
     * Promote user to admin
     */
    public function promoteToAdmin(User $user): User
    {
        $user->update(['role' => 'admin']);
        return $user;
    }

    /**
     * Demote admin to editor
     */
    public function demoteToEditor(User $user): User
    {
        $user->update(['role' => 'editor']);
        return $user;
    }
}
