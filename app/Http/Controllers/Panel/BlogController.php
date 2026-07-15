<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Services\SiteContentService;
use App\Support\ApiData;
use Illuminate\Http\Request;
use RuntimeException;

class BlogController extends Controller
{
    public function __construct(
        protected PlatformApiClient $api,
        protected SiteContentService $siteContent,
    ) {}

    public function index()
    {
        try {
            $res = $this->api->get('/bloglar');
            $items = $res['data']['items'] ?? [];
            $meta = $res['data']['meta'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $bloglar = ApiData::paginate($items, $meta);

        return view('panel.blog.index', compact('bloglar'));
    }

    public function create()
    {
        return view('panel.blog.ekle');
    }

    public function store(Request $request)
    {
        $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string'],
            'resim' => ['nullable', 'image', 'max:10240'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:500'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $fields = [
                'baslik' => $request->baslik,
                'icerik' => $request->icerik,
                'meta_baslik' => $request->meta_baslik,
                'meta_aciklama' => $request->meta_aciklama,
                'meta_anahtar_kelimeler' => $request->meta_anahtar_kelimeler,
                'aktif_mi' => $request->boolean('aktif_mi', true),
            ];
            $files = $request->hasFile('resim') ? ['resim' => $request->file('resim')] : [];
            $this->api->postMultipart('/bloglar', $fields, $files);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        return redirect()->route('panel.bloglar')->with('basari', 'Blog eklendi (senkron).');
    }

    public function edit(int $id)
    {
        try {
            $res = $this->api->get('/bloglar', ['per_page' => 50]);
            $items = $res['data']['items'] ?? [];
            $found = collect($items)->firstWhere('id', $id);
            if (! $found) {
                // try page through simple re-fetch all if needed
                return redirect()->route('panel.bloglar')->with('hata', 'Yazı bulunamadı.');
            }
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        return view('panel.blog.duzenle', ['blog' => ApiData::obj($found)]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string'],
            'resim' => ['nullable', 'image', 'max:10240'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:500'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $fields = [
                'baslik' => $request->baslik,
                'icerik' => $request->icerik,
                'meta_baslik' => $request->meta_baslik,
                'meta_aciklama' => $request->meta_aciklama,
                'meta_anahtar_kelimeler' => $request->meta_anahtar_kelimeler,
                'aktif_mi' => $request->boolean('aktif_mi'),
                '_method' => 'PUT',
            ];
            $files = $request->hasFile('resim') ? ['resim' => $request->file('resim')] : [];
            $this->api->postMultipart('/bloglar/'.$id, $fields, $files);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return redirect()->route('panel.bloglar')->with('basari', 'Blog güncellendi.');
    }

    public function destroy(int $id)
    {
        try {
            $this->api->delete('/bloglar/'.$id);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return redirect()->route('panel.bloglar')->with('basari', 'Blog silindi.');
    }
}
