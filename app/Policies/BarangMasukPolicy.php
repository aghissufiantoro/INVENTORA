<?php

namespace App\Policies;

use App\Models\BarangMasuk;
use App\Models\User;

class BarangMasukPolicy
{
    /**
     * Determine if the user can view any barang masuk.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the barang masuk.
     */
    public function view(User $user, BarangMasuk $barangMasuk): bool
    {
        // Owner dan admin bisa lihat semua
        if (in_array($user->role, ['owner', 'admin'])) {
            return true;
        }

        // Karyawan hanya bisa lihat miliknya
        return $barangMasuk->user_id === $user->id;
    }

    /**
     * Determine if the user can create barang masuk.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the barang masuk.
     */
    public function update(User $user, BarangMasuk $barangMasuk): bool
    {
        // Owner dan admin bisa update semua
        if (in_array($user->role, ['owner', 'admin'])) {
            return true;
        }

        // Karyawan hanya bisa update miliknya yang belum verified
        return $barangMasuk->user_id === $user->id
            && $barangMasuk->status !== 'verified';
    }

    /**
     * Determine if the user can delete the barang masuk.
     */
    public function delete(User $user, BarangMasuk $barangMasuk): bool
    {
        // Hanya owner yang bisa hapus
        return $user->role === 'owner';
    }

    /**
     * Determine if the user can verify the barang masuk.
     */
    public function verify(User $user, BarangMasuk $barangMasuk): bool
    {
        // Hanya owner yang bisa verifikasi
        return $user->role === 'owner' && $barangMasuk->status !== 'verified';
    }

    /**
     * Determine if the user can update harga before verification.
     */
    public function updateHarga(User $user, BarangMasuk $barangMasuk): bool
    {
        // Hanya owner yang bisa update harga sebelum verifikasi
        return $user->role === 'owner' && $barangMasuk->status !== 'verified';
    }
}
