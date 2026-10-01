<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTasmotaDeviceRequest;
use App\Http\Requests\Admin\UpdateTasmotaDeviceRequest;
use App\Models\TasmotaDevice;
use App\Services\TasmotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TasmotaController extends Controller
{
    public function __construct(private TasmotaService $tasmotaService) {}

    /**
     * Daftar semua perangkat Tasmota.
     */
    public function index(Request $request): View
    {
        $devices = TasmotaDevice::query()
            ->with('unit')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search');
                $q->where(fn ($q2) => $q2
                    ->where('device_name', 'like', "%{$term}%")
                    ->orWhere('ip_address', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                );
            })
            ->orderBy('device_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.tasmota.index', [
            'devices'  => $devices,
            'statuses' => ['ONLINE', 'OFFLINE', 'UNKNOWN'],
        ]);
    }

    /**
     * Form tambah perangkat baru.
     */
    public function create(): View
    {
        return view('admin.tasmota.create', [
            'device' => new TasmotaDevice(['status' => 'UNKNOWN']),
        ]);
    }

    /**
     * Simpan perangkat baru ke database.
     */
    public function store(StoreTasmotaDeviceRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('password');

        if ($request->filled('password')) {
            $data['password_encrypted'] = $request->string('password')->toString();
        }

        $device = TasmotaDevice::create($data);

        return redirect()
            ->route('admin.tasmota.index')
            ->with('status', "Perangkat {$device->device_name} berhasil ditambahkan.");
    }

    /**
     * Form edit perangkat.
     */
    public function edit(TasmotaDevice $tasmota_device): View
    {
        return view('admin.tasmota.edit', [
            'device' => $tasmota_device->load('unit'),
        ]);
    }

    /**
     * Update data perangkat.
     */
    public function update(UpdateTasmotaDeviceRequest $request, TasmotaDevice $tasmota_device): RedirectResponse
    {
        $data = $request->safe()->except('password');

        // Hanya update password jika diisi (kosong = tidak ubah)
        if ($request->filled('password')) {
            $data['password_encrypted'] = $request->string('password')->toString();
        }

        $tasmota_device->update($data);

        return redirect()
            ->route('admin.tasmota.index')
            ->with('status', "Perangkat {$tasmota_device->device_name} berhasil diperbarui.");
    }

    /**
     * Hapus perangkat (soft delete).
     * Unit yang terhubung akan otomatis terputus (tasmota_device_id = null via cascade/observer).
     */
    public function destroy(TasmotaDevice $tasmota_device): RedirectResponse
    {
        // Putuskan relasi ke unit sebelum hapus
        $tasmota_device->unit?->update(['tasmota_device_id' => null]);

        $tasmota_device->delete();

        return redirect()
            ->route('admin.tasmota.index')
            ->with('status', "Perangkat {$tasmota_device->device_name} berhasil dihapus.");
    }

    /**
     * Test koneksi ke perangkat Tasmota (AJAX).
     */
    public function ping(TasmotaDevice $tasmota_device): JsonResponse
    {
        $result = $this->tasmotaService->ping($tasmota_device);
        $tasmota_device->refresh();

        return response()->json([
            'online'      => $result['online'],
            'latency_ms'  => $result['latency_ms'],
            'error'       => $result['error'],
            'status'      => $tasmota_device->status,
            'last_checked' => $tasmota_device->last_checked_at?->diffForHumans(),
        ]);
    }

    /**
     * Ambil status power perangkat (AJAX).
     */
    public function powerStatus(TasmotaDevice $tasmota_device): JsonResponse
    {
        $result = $this->tasmotaService->getStatus($tasmota_device);
        $tasmota_device->refresh();

        return response()->json([
            'power'        => $result['power'],
            'online'       => $result['online'],
            'error'        => $result['error'],
            'device_status' => $tasmota_device->status,
            'last_checked' => $tasmota_device->last_checked_at?->diffForHumans(),
        ]);
    }

    /**
     * Kirim perintah Power ON manual (AJAX).
     */
    public function powerOn(TasmotaDevice $tasmota_device): JsonResponse
    {
        $success = $this->tasmotaService->turnOn($tasmota_device);
        $tasmota_device->refresh();

        return response()->json([
            'success' => $success,
            'message' => $success
                ? "Power ON berhasil dikirim ke {$tasmota_device->device_name}."
                : "Gagal mengirim Power ON ke {$tasmota_device->device_name}. Periksa koneksi jaringan.",
            'status' => $tasmota_device->status,
        ]);
    }

    /**
     * Kirim perintah Power OFF manual (AJAX).
     */
    public function powerOff(TasmotaDevice $tasmota_device): JsonResponse
    {
        $success = $this->tasmotaService->turnOff($tasmota_device);
        $tasmota_device->refresh();

        return response()->json([
            'success' => $success,
            'message' => $success
                ? "Power OFF berhasil dikirim ke {$tasmota_device->device_name}."
                : "Gagal mengirim Power OFF ke {$tasmota_device->device_name}. Periksa koneksi jaringan.",
            'status' => $tasmota_device->status,
        ]);
    }
}

