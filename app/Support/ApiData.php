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

            // Soft-cast common date strings so ->format() works in blades.
            // ICI ICE de yapilir: eskiden yalnizca ust seviye donusturuluyordu,
            // bu yuzden `$odeme->kalemler[0]->tarih` duz metin kaliyor ve
            // blade'deki ->format() cagrisi olumcul hata veriyordu (HTTP 500).
            self::tarihleriDonustur($obj);

            // nested hasta ad_soyad convenience
            if (isset($obj->hasta) && is_object($obj->hasta) && ! isset($obj->hasta->ad_soyad)) {
                $obj->hasta->ad_soyad = trim(($obj->hasta->ad ?? '').' '.($obj->hasta->soyad ?? ''));
            }

            return $obj;
        }

        return $data;
    }

    /** Tarih gibi davranan alan adlari. */
    private const TARIH_ALANLARI = [
        'created_at', 'updated_at', 'odeme_tarihi', 'tarih',
        'expires_at', 'baslangic_at', 'bitis_at', 'basvuru_bitis_at',
    ];

    /**
     * Nesne agacindaki tarih alanlarini yerinde Carbon'a cevirir.
     *
     * JSON'dan gelen yapi sonlu oldugu icin ozyineleme guvenli; yine de
     * makul bir derinlik siniri konuldu.
     */
    private static function tarihleriDonustur(mixed $dugum, int $derinlik = 0): void
    {
        if ($derinlik > 6) {
            return;
        }

        if (is_array($dugum)) {
            foreach ($dugum as $eleman) {
                self::tarihleriDonustur($eleman, $derinlik + 1);
            }

            return;
        }

        if (! is_object($dugum)) {
            return;
        }

        foreach (get_object_vars($dugum) as $ad => $deger) {
            if (in_array($ad, self::TARIH_ALANLARI, true)
                && is_string($deger) && $deger !== '') {
                try {
                    $dugum->$ad = \Illuminate\Support\Carbon::parse($deger);
                } catch (\Throwable) {
                    // cozulemezse metin olarak birak
                }

                continue;
            }

            if (is_array($deger) || is_object($deger)) {
                self::tarihleriDonustur($deger, $derinlik + 1);
            }
        }
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
