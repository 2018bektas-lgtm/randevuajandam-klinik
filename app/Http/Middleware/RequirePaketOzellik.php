<?php

namespace App\Http\Middleware;

use App\Support\PaketOzellik;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePaketOzellik
{
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $codes = [];
        foreach ($features as $f) {
            foreach (explode(',', $f) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $codes[] = $part;
                }
            }
        }

        if ($codes !== [] && ! PaketOzellik::has($codes)) {
            $label = collect($codes)->map(fn ($c) => PaketOzellik::label($c))->implode(' / ');
            $msg = "«{$label}» mevcut paketinizde yok. Ana platformdan paketinizi yükseltin.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'feature' => $codes[0],
                    'features' => $codes,
                    'upgrade_url' => PaketOzellik::upgradeUrl(),
                ], 403);
            }

            return redirect()
                ->route('panel.dashboard')
                ->with('hata', $msg)
                ->with('upgrade_url', PaketOzellik::upgradeUrl());
        }

        return $next($request);
    }
}
