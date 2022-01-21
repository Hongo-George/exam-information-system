<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubjectPolicy
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
        return $user->role->permissions->pluck('slug')->contains('subjects-browse')
            ? Response::allow()
            : Response::deny('You are not allowed to browse the subjects page');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Subject $subject)
    {
        return $user->role->permissions->pluck('slug')->contains('subjects-read')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to view {$subject->name} details");
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->pluck('slug')->contains('subjects-create')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to create a subject");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Subject $subject)
    {
        return $user->role->permissions->pluck('slug')->contains('subjects-update')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to update {$subject->name} details");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Subject $subject)
    {
        return $user->role->permissions->pluck('slug')->contains('subjects-delete')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to delete the subject, {$subject->name}");
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Subject $subject)
    {
        return $user->role->permissions->pluck('slug')->contains('subjects-restore')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to restore the subject, {$subject->name}");
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Subject $subject)
    {
        return $user->role->permissions->pluck('slug')->contains('subjects-destroy')
            ? Response::allow()
            : Response::deny("Woops! You are not allowed to destroy the subject, {$subject->name}");
    }

    /**
     * Determine whether a user can bulk delete subjects
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function bulkDelete(User $user)
    {
        return $user->role->permissions->pluck('slug')->contains('subjects-bulk-delete')
            ? Response::allow()
            : Response::deny('You are not allowed to bulk delete subjects');
        
    }

    /**
     * Determine whether the user can view trashed permisions.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */    
    public function viewTrashed(User $user)
    {
        return $user->role->permissions->pluck('slug')->contains('subjects-view-trashed')
            ? Response::allow()
            : Response::deny("Woops! You're not allowed to view trashed subjects");
        
    } 
}
