<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SitePage extends Model
{
    protected $table = 'site_pages';

    protected $fillable = [
        'baslik',
        'slug',
        'icerik',
        'meta_baslik',
        'meta_aciklama',
        'meta_anahtar_kelimeler',
        'aktif',
        'footer_goster',
        'sira',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'footer_goster' => 'boolean',
            'sira' => 'integer',
        ];
    }

    public static function makeSlug(string $baslik, ?int $exceptId = null): string
    {
        $base = Str::slug($baslik) ?: 'sayfa';
        $slug = $base;
        $i = 2;
        while (static::query()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /** Menü dropdown anahtarı: page.kvkk */
    public function menuRouteKey(): string
    {
        return 'page.'.$this->slug;
    }

    public function publicUrl(): string
    {
        try {
            return route('frontend.sayfa', ['slug' => $this->slug]);
        } catch (\Throwable) {
            return url('/sayfa/'.$this->slug);
        }
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeFooter($query)
    {
        return $query->where('footer_goster', true)->where('aktif', true)->orderBy('sira')->orderBy('id');
    }
}
