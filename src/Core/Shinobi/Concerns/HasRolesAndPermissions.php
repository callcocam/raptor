<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Shinobi\Concerns;

trait HasRolesAndPermissions
{
    use HasRoles, HasPermissions;

    /**
     * Run through the roles assigned to the permission and
     * checks if the user has any of them assigned.
     * 
     * @param  \Callcocam\Raptor\Shinobi\Models\Permission  $permission
     * @return boolean
     */
    protected function hasPermissionThroughRole($permission): bool
    {
        if ($this->hasRoles()) {
            foreach ($permission->roles as $role) {
                if ($this->roles->contains($role)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function hasPermissionThroughRoleFlag(): bool
    {
        if ($this->hasRoles()) {
            return ! ($this->roles
                ->filter(function ($role) {
                    return   $role->special;
                })
                ->pluck('special')
                ->contains('no-access'));
        }

        return false;
    }
}
