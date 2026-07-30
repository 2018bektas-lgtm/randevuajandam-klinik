<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteMenuItem extends Model
{
    protected $table = 'site_menu_items';

    protected $fillable = [
        'parent_id', 'key', 'label', 'route', 'url', 'aktif', 'sira',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'sira' => 'integer',
            'parent_id' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sira')->orderBy('id');
    }
}
