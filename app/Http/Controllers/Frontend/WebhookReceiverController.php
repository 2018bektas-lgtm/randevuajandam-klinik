<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SiteContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Ana platformdan gelen outbound webhook alıcısı (HMAC doğrulamalı).
 * Secret: WEBHOOK_RECEIVER_SECRET env veya site options.
 */
class WebhookReceiverController extends Controller
{
    public function __construct(protected SiteContentService $content) {}

    public function receive(Request $request): JsonResponse
    {
        $secret = (string) (config('randevu_api.webhook_receiver_secret')
            ?: env('WEBHOOK_RECEIVER_SECRET', ''));

        if ($secret === '') {
            Log::warning('Webhook receiver: secret not configured');

            return response()->json(['ok' => false, 'message' => 'Not configured'], 503);
        }

        $timestamp = (string) $request->header('X-Timestamp', '');
        $signature = (string) $request->header('X-Webhook-Signature', '');
        $raw = $request->getContent();

        if ($timestamp === '' || $signature === '') {
            return response()->json(['ok' => false, 'message' => 'Missing signature headers'], 401);
        }

        // Reject clock skew > 5 minutes
        if (abs(time() - (int) $timestamp) > 300) {
            return response()->json(['ok' => false, 'message' => 'Timestamp skew'], 401);
        }

        $expected = hash_hmac('sha256', $timestamp.$raw, $secret);
        if (! hash_equals($expected, $signature)) {
            Log::warning('Webhook receiver: bad signature', ['ip' => $request->ip()]);

            return response()->json(['ok' => false, 'message' => 'Invalid signature'], 401);
        }

        $event = $request->header('X-Webhook-Event') ?: $request->input('event');
        Log::info('Doctor site webhook received', [
            'event' => $event,
            'data_keys' => array_keys((array) $request->input('data', [])),
        ]);

        // Invalidate cached public profile after appointment/content events
        try {
            $this->content->forgetCache();
        } catch (\Throwable) {
            // ignore
        }

        return response()->json(['ok' => true]);
    }
}
