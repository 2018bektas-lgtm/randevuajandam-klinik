<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteFooterItem extends Model
{
    protected $table = 'site_footer_items';

    protected $fillable = [
        'key', 'label', 'route', 'url', 'aktif', 'sira',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'sira' => 'integer',
        ];
    }
}
