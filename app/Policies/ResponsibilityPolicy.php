<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Responsibility;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResponsibilityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-browse')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to browse the responsibilities page");
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Responsibility  $responsibility
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Responsibility $responsibility)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-read')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to view the responsibility, {$responsibility->name}, page");
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-create')
            ? Response::allow()
            : Response::deny('Woops! You are not allowed to create a responsibility');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Responsibility  $responsibility
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Responsibility $responsibility)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-update')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to update the responsibility, {$responsibility->name}");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Responsibility  $responsibility
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateLocked(User $user, Responsibility $responsibility)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-update-locked')
            ? Response::allow()
            : Response::deny("The responsibility, {$responsibility->name} is locked, you can't update it");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Responsibility  $responsibility
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Responsibility $responsibility)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-delete')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to delete the responsibility, {$responsibility->name}");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Responsibility  $responsibility
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deleteLocked(User $user, Responsibility $responsibility)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-delete-locked')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to delete the locked responsibility, {$responsibility->name}");
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Responsibility  $responsibility
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Responsibility $responsibility)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-restore')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to restore the responsibility, {$responsibility->name}");
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Responsibility  $responsibility
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Responsibility $responsibility)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-destroy')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to permanently delete the responsibility, {$responsibility->name}");
    }

    /**
     * Determine whether a user is allowed to view trashed responsibilities
     * 
     * @param User $user
     * @return Response
     */
    public function viewTrashed(User $user)
    {
        return $user->role->permissions->pluck('slug')->contains('responsibilities-view-trashed')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to view trashed responsibilities");
    }
}
