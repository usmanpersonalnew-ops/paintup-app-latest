<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ProjectVisibilityScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!Auth::check()) {
            // No logged in user → no access
            $builder->whereRaw('1 = 0');
            return;
        }

        $user = Auth::user();

        // Admin can see all projects
        if ($user->hasRole('admin')) {
            return;
        }

        // Supervisor can see only assigned projects
        if ($user->hasRole('supervisor')) {
            $builder->where('supervisor_id', $user->id);
            return;
        }

        // Any other role OR user without role → no access
        $builder->whereRaw('1 = 0');
    }
}