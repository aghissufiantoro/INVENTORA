<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine if the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Determine if the user can create products.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        return in_array($user->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->role === 'owner';
    }

    /**
     * Determine if the user can adjust stock.
     */
    public function adjustStock(User $user, Product $product): bool
    {
        return in_array($user->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can auto-calculate all ROP/SS.
     */
    public function autoCalculateAll(User $user): bool
    {
        // Karyawan bisa hitung, tapi butuh approval owner
        return true;
    }
}
