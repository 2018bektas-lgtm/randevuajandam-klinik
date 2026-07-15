<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSliderSlide extends Model
{
    protected $table = 'site_slider_slides';

    protected $fillable = [
        'baslik', 'alt', 'etiket', 'badge', 'image', 'thumb',
        'cta', 'cta_url', 'cta2', 'cta2_url', 'meta', 'aktif', 'sira',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'aktif' => 'boolean',
            'sira' => 'integer',
        ];
    }
}
