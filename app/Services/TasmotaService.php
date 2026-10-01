<?php

namespace App\Services;

use App\Models\TasmotaDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service class untuk komunikasi dengan perangkat Tasmota/Sonoff.
 *
 * Menggunakan Tasmota HTTP API:
 *   GET http://{ip}/cm?cmnd=Power%20ON
 *   GET http://{ip}/cm?cmnd=Power%20OFF
 *   GET http://{ip}/cm?cmnd=Power
 *
 * Jika perangkat memerlukan autentikasi HTTP Basic:
 *   GET http://{ip}/cm?user={user}&password={pass}&cmnd=Power%20ON
 */
class TasmotaService
{
    /**
     * Timeout dalam detik untuk setiap HTTP request ke Tasmota.
     */
    private int $timeout = 5;

    /**
     * Kirim perintah Power ON ke perangkat Tasmota yang terhubung ke unit.
     * Mengembalikan true jika berhasil, false jika gagal.
     */
    public function turnOn(TasmotaDevice $device): bool
    {
        return $this->sendCommand($device, 'Power ON');
    }

    /**
     * Kirim perintah Power OFF ke perangkat Tasmota yang terhubung ke unit.
     * Mengembalikan true jika berhasil, false jika gagal.
     */
    public function turnOff(TasmotaDevice $device): bool
    {
        return $this->sendCommand($device, 'Power OFF');
    }

    /**
     * Ambil status Power perangkat Tasmota (ON/OFF/UNKNOWN).
     *
     * @return array{power: string, online: bool, raw: array<string,mixed>|null, error: string|null}
     */
    public function getStatus(TasmotaDevice $device): array
    {
        try {
            $response = $this->buildRequest($device, 'Power')->get($this->buildUrl($device, 'Power'));

            if ($response->successful()) {
                $data = $response->json();
                $power = strtoupper($data['POWER'] ?? 'UNKNOWN');

                $this->markOnline($device);

                return [
                    'power'  => $power,
                    'online' => true,
                    'raw'    => $data,
                    'error'  => null,
                ];
            }

            $this->markOffline($device, "HTTP {$response->status()}");

            return [
                'power'  => 'UNKNOWN',
                'online' => false,
                'raw'    => null,
                'error'  => "HTTP Error: {$response->status()}",
            ];
        } catch (\Throwable $e) {
            $this->markOffline($device, $e->getMessage());

            return [
                'power'  => 'UNKNOWN',
                'online' => false,
                'raw'    => null,
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Cek apakah perangkat dapat dijangkau (ping via HTTP).
     *
     * @return array{online: bool, latency_ms: int|null, error: string|null}
     */
    public function ping(TasmotaDevice $device): array
    {
        $start = microtime(true);

        try {
            $response = $this->buildRequest($device, 'Status')->get($this->buildUrl($device, 'Status'));

            $latencyMs = (int) round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $this->markOnline($device);

                return [
                    'online'     => true,
                    'latency_ms' => $latencyMs,
                    'error'      => null,
                ];
            }

            $this->markOffline($device, "HTTP {$response->status()}");

            return [
                'online'     => false,
                'latency_ms' => $latencyMs,
                'error'      => "HTTP Error: {$response->status()}",
            ];
        } catch (\Throwable $e) {
            $this->markOffline($device, $e->getMessage());

            return [
                'online'     => false,
                'latency_ms' => null,
                'error'      => $e->getMessage(),
            ];
        }
    }

    /**
     * Kirim sembarang command string ke Tasmota.
     * Mengembalikan true jika berhasil (HTTP 2xx), false jika gagal.
     */
    private function sendCommand(TasmotaDevice $device, string $command): bool
    {
        try {
            $response = $this->buildRequest($device, $command)->get($this->buildUrl($device, $command));

            if ($response->successful()) {
                $this->markOnline($device);
                Log::info("Tasmota [{$device->device_name}] command '{$command}' berhasil.", [
                    'ip'       => $device->ip_address,
                    'response' => $response->json(),
                ]);

                return true;
            }

            $this->markOffline($device, "HTTP {$response->status()}");
            Log::warning("Tasmota [{$device->device_name}] command '{$command}' gagal: HTTP {$response->status()}.", [
                'ip' => $device->ip_address,
            ]);

            return false;
        } catch (\Throwable $e) {
            $this->markOffline($device, $e->getMessage());
            Log::error("Tasmota [{$device->device_name}] command '{$command}' exception: {$e->getMessage()}", [
                'ip' => $device->ip_address,
            ]);

            return false;
        }
    }

    /**
     * Bangun URL endpoint Tasmota.
     * Format: http://{ip}/cm?cmnd={command}
     */
    private function buildUrl(TasmotaDevice $device, string $command): string
    {
        return "http://{$device->ip_address}/cm";
    }

    /**
     * Bangun HTTP request dengan query param dan autentikasi jika perlu.
     */
    private function buildRequest(TasmotaDevice $device, string $command): \Illuminate\Http\Client\PendingRequest
    {
        $params = ['cmnd' => $command];

        // Tambahkan autentikasi jika perangkat memerlukan kredensial
        if ($device->username) {
            $params['user']     = $device->username;
            $params['password'] = $device->password_encrypted ?? '';
        }

        return Http::timeout($this->timeout)
            ->connectTimeout($this->timeout)
            ->withQueryParameters($params);
    }

    /**
     * Update status device menjadi ONLINE dan catat waktu pengecekan.
     */
    private function markOnline(TasmotaDevice $device): void
    {
        $device->update([
            'status'          => 'ONLINE',
            'last_checked_at' => now(),
        ]);
    }

    /**
     * Update status device menjadi OFFLINE dan catat error ke log.
     */
    private function markOffline(TasmotaDevice $device, string $reason): void
    {
        $device->update([
            'status'          => 'OFFLINE',
            'last_checked_at' => now(),
        ]);

        Log::warning("Tasmota device [{$device->device_name}] offline: {$reason}", [
            'ip' => $device->ip_address,
        ]);
    }
}

