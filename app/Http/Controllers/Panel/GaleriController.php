<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Services\SiteContentService;
use App\Support\ApiData;
use Illuminate\Http\Request;
use RuntimeException;

class GaleriController extends Controller
{
    public function __construct(
        protected PlatformApiClient $api,
        protected SiteContentService $siteContent,
    ) {}

    public function index()
    {
        try {
            $items = $this->api->get('/galeri')['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $galeriler = ApiData::collection($items);

        return view('panel.galeri.index', compact('galeriler'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'resimler' => ['required', 'array'],
            'resimler.*' => ['image', 'max:5120'],
        ]);

        try {
            $this->api->postMultipart('/galeri', [], [
                'resimler' => $request->file('resimler'),
            ]);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Fotoğraflar yüklendi.');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'baslik' => ['nullable', 'string', 'max:255'],
            'sira' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $this->api->put('/galeri/'.$id, $data);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Galeri güncellendi.');
    }

    public function destroy(int $id)
    {
        try {
            $this->api->delete('/galeri/'.$id);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Silindi.');
    }

    public function sirala(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        try {
            $res = $this->api->post('/galeri/sirala', $data);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json($res);
    }
}
