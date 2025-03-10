<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Models\Traits;

use Callcocam\Raptor\Models\Tenant; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasTenant
{
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
