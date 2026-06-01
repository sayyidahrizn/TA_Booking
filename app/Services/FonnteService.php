<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    private static function normalizeTarget(string $target): string
    {
        $digits = preg_replace('/\D+/', '', $target);
        if (!$digits) {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    public static function send($target, $message)
    {
        $token = (string) config('services.fonnte.token');
        $normalizedTarget = self::normalizeTarget((string) $target);

        if (empty($token)) {
            Log::warning('Fonnte token kosong. Notifikasi tidak dikirim.');
            return false;
        }

        if (empty($normalizedTarget)) {
            Log::warning('Nomor tujuan Fonnte tidak valid.', ['target' => $target]);
            return false;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                'Authorization' => config('services.fonnte.token'),
            ])
            ->asForm()
            ->post('https://api.fonnte.com/send', [
                'target' => $normalizedTarget,
                'message' => $message,
                'countryCode' => '62',
            ]);

            $payload = $response->json();

            if (!$response->successful()) {
                Log::error('Fonnte HTTP gagal', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'target' => $normalizedTarget,
                ]);
                return false;
            }

            $statusOk = (bool) data_get($payload, 'status', true);
            if (!$statusOk) {
                Log::error('Fonnte response status false', [
                    'payload' => $payload,
                    'target' => $normalizedTarget,
                ]);
                return false;
            }

            return $payload;
        } catch (\Throwable $e) {
            Log::error('Fonnte Error: ' . $e->getMessage(), [
                'target' => $normalizedTarget,
            ]);

            return false;
        }
    }
}
