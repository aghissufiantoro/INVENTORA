<?php

namespace App\Policies;

use App\Models\BarangRusak;
use App\Models\User;

class BarangRusakPolicy
{
    /**
     * Determine if the user can view any barang rusak.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the barang rusak.
     */
    public function view(User $user, BarangRusak $barangRusak): bool
    {
        return true;
    }

    /**
     * Determine if the user can create barang rusak.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the barang rusak.
     */
    public function update(User $user, BarangRusak $barangRusak): bool
    {
        return false; // Barang rusak tidak bisa diupdate
    }

    /**
     * Determine if the user can delete the barang rusak.
     */
    public function delete(User $user, BarangRusak $barangRusak): bool
    {
        return $user->role === 'owner';
    }

    /**
     * Determine if the user can approve the barang rusak.
     */
    public function approve(User $user, BarangRusak $barangRusak): bool
    {
        // Hanya owner yang bisa approve
        return $user->role === 'owner' && $barangRusak->status === 'menunggu';
    }

    /**
     * Determine if the user can reject the barang rusak.
     */
    public function reject(User $user, BarangRusak $barangRusak): bool
    {
        // Hanya owner yang bisa reject
        return $user->role === 'owner' && $barangRusak->status === 'menunggu';
    }
}
