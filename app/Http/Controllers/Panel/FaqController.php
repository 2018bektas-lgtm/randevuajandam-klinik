<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Services\SiteContentService;
use App\Support\ApiData;
use Illuminate\Http\Request;
use RuntimeException;

class FaqController extends Controller
{
    public function __construct(
        protected PlatformApiClient $api,
        protected SiteContentService $siteContent,
    ) {}

    public function index()
    {
        try {
            $items = $this->api->get('/faqs')['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $faqs = ApiData::collection($items);

        return view('panel.faq.index', compact('faqs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'soru' => ['required', 'string', 'max:255'],
            'cevap' => ['required', 'string'],
            'sira' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['aktif'] = true;

        try {
            $this->api->post('/faqs', $data);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'SSS eklendi.');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'soru' => ['required', 'string', 'max:255'],
            'cevap' => ['required', 'string'],
            'sira' => ['nullable', 'integer', 'min:0'],
            'aktif' => ['nullable'],
        ]);
        if ($request->has('aktif')) {
            $data['aktif'] = $request->boolean('aktif');
        }

        try {
            $this->api->put('/faqs/'.$id, $data);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'SSS güncellendi.');
    }

    public function destroy(int $id)
    {
        try {
            $this->api->delete('/faqs/'.$id);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'SSS silindi.');
    }

    public function toggle(int $id)
    {
        try {
            $this->api->post('/faqs/'.$id.'/toggle');
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Durum değişti.');
    }
}
