<?php

namespace App\Policies;

use App\Models\Retur;
use App\Models\User;

class ReturPolicy
{
    /**
     * Determine if the user can view any returs.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the retur.
     */
    public function view(User $user, Retur $retur): bool
    {
        return true;
    }

    /**
     * Determine if the user can create retur.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the retur.
     */
    public function update(User $user, Retur $retur): bool
    {
        return false; // Retur tidak bisa diupdate setelah dibuat
    }

    /**
     * Determine if the user can delete the retur.
     */
    public function delete(User $user, Retur $retur): bool
    {
        return $user->role === 'owner';
    }

    /**
     * Determine if the user can approve the retur.
     */
    public function approve(User $user, Retur $retur): bool
    {
        // Hanya owner yang bisa approve
        return $user->role === 'owner' && $retur->status === 'menunggu';
    }

    /**
     * Determine if the user can reject the retur.
     */
    public function reject(User $user, Retur $retur): bool
    {
        // Hanya owner yang bisa reject
        return $user->role === 'owner' && $retur->status === 'menunggu';
    }
}
