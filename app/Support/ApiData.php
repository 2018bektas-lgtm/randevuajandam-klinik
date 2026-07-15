<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Convert API JSON arrays into object-like structures for Blade views
 * copied from the main hekim panel ($model->field style).
 */
class ApiData
{
    public static function obj(mixed $data): mixed
    {
        if ($data === null) {
            return null;
        }
        if (is_array($data)) {
            $obj = json_decode(json_encode($data, JSON_UNESCAPED_UNICODE));
            // Soft-cast common date strings so ->format() works in blades
            foreach (['created_at', 'updated_at', 'odeme_tarihi', 'tarih', 'expires_at', 'baslangic_at', 'bitis_at', 'basvuru_bitis_at'] as $field) {
                if (isset($obj->$field) && is_string($obj->$field) && $obj->$field !== '') {
                    try {
                        $obj->$field = \Illuminate\Support\Carbon::parse($obj->$field);
                    } catch (\Throwable) {
                        // leave as string
                    }
                }
            }
            // nested hasta ad_soyad convenience
            if (isset($obj->hasta) && is_object($obj->hasta) && ! isset($obj->hasta->ad_soyad)) {
                $obj->hasta->ad_soyad = trim(($obj->hasta->ad ?? '').' '.($obj->hasta->soyad ?? ''));
            }

            return $obj;
        }

        return $data;
    }

    public static function collection(mixed $items): Collection
    {
        $arr = is_array($items) ? $items : (array) $items;

        return collect($arr)->map(fn ($i) => self::obj($i));
    }

    public static function paginate(array $items, array $meta = [], int $perPage = 20): LengthAwarePaginator
    {
        $page = (int) ($meta['current_page'] ?? 1);
        $total = (int) ($meta['total'] ?? count($items));
        $last = (int) ($meta['last_page'] ?? max(1, (int) ceil($total / max(1, $perPage))));

        return new LengthAwarePaginator(
            self::collection($items),
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public static function emptyPage(int $perPage = 20): LengthAwarePaginator
    {
        return self::paginate([], ['current_page' => 1, 'total' => 0, 'last_page' => 1], $perPage);
    }
}
