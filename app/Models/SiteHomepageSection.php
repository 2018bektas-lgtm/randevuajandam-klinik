<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteHomepageSection extends Model
{
    protected $table = 'site_homepage_sections';

    protected $fillable = [
        'key', 'label', 'baslik', 'alt_metin', 'aktif', 'sira',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'sira' => 'integer',
        ];
    }
}
