<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Services\SiteContentService;
use App\Support\ApiData;
use Illuminate\Http\Request;
use RuntimeException;

class HizmetController extends Controller
{
    public function __construct(
        protected PlatformApiClient $api,
        protected SiteContentService $siteContent,
    ) {}

    public function index()
    {
        try {
            $items = $this->api->get('/hizmetler')['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $hizmetler = ApiData::paginate(
            is_array($items) ? array_values($items) : [],
            ['current_page' => 1, 'total' => is_array($items) ? count($items) : 0, 'last_page' => 1],
            max(count($items ?: [1]), 1)
        );

        return view('panel.hizmet.index', compact('hizmetler'));
    }

    public function create()
    {
        return view('panel.hizmet.ekle');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ad' => ['required', 'string', 'max:255'],
            'aciklama' => ['nullable', 'string'],
            'sure' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'fiyat' => ['nullable', 'numeric', 'min:0'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:500'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:500'],
            'aktif_mi' => ['nullable'],
            'resim' => ['nullable', 'image', 'max:10240'],
        ]);
        $fields = [
            'ad' => $data['ad'],
            'aciklama' => $data['aciklama'] ?? null,
            'sure' => $data['sure'] ?? 30,
            'fiyat' => $data['fiyat'] ?? 0,
            'meta_baslik' => $data['meta_baslik'] ?? null,
            'meta_aciklama' => $data['meta_aciklama'] ?? null,
            'meta_anahtar_kelimeler' => $data['meta_anahtar_kelimeler'] ?? null,
            'aktif_mi' => $request->boolean('aktif_mi', true),
        ];

        try {
            $files = $request->hasFile('resim') ? ['resim' => $request->file('resim')] : [];
            if ($files) {
                $this->api->postMultipart('/hizmetler', $fields, $files);
            } else {
                $this->api->post('/hizmetler', $fields);
            }
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        return redirect()->route('panel.hizmetler')->with('basari', 'Hizmet eklendi (ana platform ile senkron).');
    }

    public function edit(int $id)
    {
        try {
            $items = $this->api->get('/hizmetler')['data'] ?? [];
            $found = collect($items)->firstWhere('id', $id);
            if (! $found) {
                return redirect()->route('panel.hizmetler')->with('hata', 'Hizmet bulunamadı.');
            }
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        return view('panel.hizmet.duzenle', ['hizmet' => ApiData::obj($found)]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'ad' => ['required', 'string', 'max:255'],
            'aciklama' => ['nullable', 'string'],
            'sure' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'fiyat' => ['nullable', 'numeric', 'min:0'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:500'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:500'],
            'aktif_mi' => ['nullable'],
            'resim' => ['nullable', 'image', 'max:10240'],
        ]);
        $fields = [
            'ad' => $data['ad'],
            'aciklama' => $data['aciklama'] ?? null,
            'sure' => $data['sure'] ?? 30,
            'fiyat' => $data['fiyat'] ?? 0,
            'meta_baslik' => $data['meta_baslik'] ?? null,
            'meta_aciklama' => $data['meta_aciklama'] ?? null,
            'meta_anahtar_kelimeler' => $data['meta_anahtar_kelimeler'] ?? null,
            'aktif_mi' => $request->boolean('aktif_mi'),
            '_method' => 'PUT',
        ];

        try {
            $files = $request->hasFile('resim') ? ['resim' => $request->file('resim')] : [];
            if ($files) {
                $this->api->postMultipart('/hizmetler/'.$id, $fields, $files);
            } else {
                $this->api->put('/hizmetler/'.$id, collect($fields)->except('_method')->all());
            }
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        return redirect()->route('panel.hizmetler')->with('basari', 'Hizmet güncellendi.');
    }

    public function destroy(int $id)
    {
        try {
            $this->api->delete('/hizmetler/'.$id);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return redirect()->route('panel.hizmetler')->with('basari', 'Hizmet silindi.');
    }
}
