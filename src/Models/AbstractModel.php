<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Models;

use App\Models\User;
use Callcocam\Raptor\Core\Concerns\Sluggable\HasSlug;
use Callcocam\Raptor\Core\Concerns\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AbstractModel extends Model
{
    use HasUlids, HasSlug;


    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return SlugOptions
     */
    public function getSlugOptions()
    {
        if (is_string($this->slugTo())) {
            return SlugOptions::create()
                ->generateSlugsFrom($this->slugFrom())
                ->saveSlugsTo($this->slugTo());
        }
    }
}
