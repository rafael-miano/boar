<?php

namespace App\Policies;

use App\Models\Boar;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BoarPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Boar $boar): bool
    {
        return $boar->user_id === $user->id || $user->role === 'customer';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'boar-raiser';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Boar $boar): bool
    {
        return $boar->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Boar $boar): bool
    {
        return $boar->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Boar $boar): bool
    {
        return $boar->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Boar $boar): bool
    {
        return false;
    }
}
