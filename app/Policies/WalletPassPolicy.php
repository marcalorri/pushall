<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WalletPass;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Traits\Macroable;

class WalletPassPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || true; // dashboard users can list their own; scoping is applied at query level
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WalletPass $walletPass): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $walletPass->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || true; // allow dashboard users to create their own passes if UI allows
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WalletPass $walletPass): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $walletPass->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WalletPass $walletPass): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $walletPass->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WalletPass $walletPass): bool
    {
        return $this->update($user, $walletPass);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WalletPass $walletPass): bool
    {
        return $user->hasRole('admin');
    }
}
