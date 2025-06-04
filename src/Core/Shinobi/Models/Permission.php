<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Raptor\Core\Shinobi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Callcocam\Raptor\Core\Shinobi\Concerns\RefreshesPermissionCache;
use Callcocam\Raptor\Core\Shinobi\Contracts\Permission as PermissionContract;
use Callcocam\Raptor\Models\AbstractModel;

class Permission extends AbstractModel implements PermissionContract
{
    use RefreshesPermissionCache;
     

    /**
     * Create a new Permission instance.
     * 
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('shinobi.tables.permissions'));
    }

    /**
     * Permissions can belong to many roles.
     *
     * @return Model
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('shinobi.models.role'))->withTimestamps();
    }
}
