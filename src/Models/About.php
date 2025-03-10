<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Models;

use Callcocam\Raptor\Enums\AboutStatus;
use Callcocam\Raptor\Landlord\BelongsToTenants;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\SoftDeletes;


class About extends AbstractModel
{
    use HasFactory, HasUlids, SoftDeletes, BelongsToTenants; 

    protected $guarded = ['id'];

    protected $casts = [
        'status' => AboutStatus::class,
    ];
} 