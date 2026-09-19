<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Halaman "Dokumen Saya" (Tabel Pelanggan).
 *
 * Alur: isi Form Pelanggan (data pelanggan + periode kontrak + barang/service)
 * → Simpan → baris muncul di Tabel Pelanggan dengan status default Draft.
 * Tombol Lanjut langsung masuk ke halaman pilih template (read-only).
 */
class CustomerController extends Controller
{
    /** Prefix nomor kontrak hasil auto-generate. */
    private const CONTRACT_PREFIX = 'KTR';

    public function index()
    {
        $customers = Customer::where('user_id', Auth::id())
            ->withCount(['barang', 'services', 'documents'])
            // Dokumen terbaru dipakai untuk tombol aksi di Tabel Pelanggan:
            // "Setujui" (dokumen On Review) dan "Unduh S.O.F" (sudah disetujui).
            ->with(['documents' => fn ($query) => $query->orderByDesc('id')])
            ->latest()
            ->get();

        return view('pages.customers', [
            'title' => 'Dokumen Saya',
            'customers' => $customers,

            // Dipakai sebagai placeholder abu-abu pada input Nomor Kontrak:
            // kalau user tidak mengetik apa pun, server memakai nilai ini.
            'nextContractNumber' => $this->nextContractNumber(),

            // Perkiraan Pelanggan ID berikutnya (kolom auto).
            'nextCustomerCode' => $this->peekNextCustomerCode(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_number' => ['required', 'string', 'max:50'],
            'name'            => ['required', 'string', 'max:150'],
            'contract_number' => ['nullable', 'string', 'max:100'],

            // Periode kontrak — tanggal selesai selalu dihitung server.
            'active_date'   => ['required', 'date'],
            'active_months' => ['required', 'integer', 'min:1', 'max:120'],

            // Barang (boleh kosong, boleh banyak; baris tanpa nama diabaikan).
            'barang'                   => ['nullable', 'array', 'max:20'],
            'barang.*.name'            => ['nullable', 'string', 'max:150'],
            'barang.*.quantity'        => ['nullable', 'integer', 'min:1', 'max:100000'],
            'barang.*.price'           => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'barang.*.price_type'      => ['nullable', 'in:one_time,monthly'],
            'barang.*.ownership'       => ['nullable', 'in:disewa,dipinjamkan,dibeli'],

            // Service (boleh kosong, boleh banyak; baris tanpa nama diabaikan).
            'services'              => ['nullable', 'array', 'max:20'],
            'services.*.name'       => ['nullable', 'string', 'max:150'],
            'services.*.price'      => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'services.*.price_type' => ['nullable', 'in:one_time,monthly'],
        ], [
            'customer_number.required' => 'Nomer Pelanggan wajib diisi.',
            'name.required'            => 'Nama Pelanggan wajib diisi.',
            'active_date.required'     => 'Tanggal Aktif wajib diisi.',
            'active_months.required'   => 'Masa Aktif wajib diisi.',
        ]);

        // Nomor kontrak: kalau user tidak mengetik apa pun, pakai auto-generate.
        $contractNumber = trim((string) ($data['contract_number'] ?? ''));
        if ($contractNumber === '') {
            $contractNumber = $this->nextContractNumber();
        }

        // Tanggal Selesai otomatis: Tanggal Aktif + Masa Aktif (bulan).
        $finishDate = \Carbon\Carbon::parse($data['active_date'])
            ->addMonthsNoOverflow((int) $data['active_months'])
            ->toDateString();

        $customer = DB::transaction(function () use ($data, $contractNumber, $finishDate) {
            $customer = Customer::create([
                'user_id'         => Auth::id(),
                'customer_number' => $data['customer_number'],
                'name'            => $data['name'],
                'contract_number' => $contractNumber,
                'active_date'     => $data['active_date'],
                'active_months'   => (int) $data['active_months'],
                'finish_date'     => $finishDate,
                'status'          => 'draft',
            ]);

            foreach ($this->cleanItems($data['barang'] ?? []) as $row) {
                $customer->barang()->create([
                    'name'       => $row['name'],
                    'quantity'   => $row['quantity'] ?? 1,
                    'price'      => $row['price'] ?? 0,
                    'price_type' => $row['price_type'] ?? 'one_time',
                    'ownership'  => $row['ownership'] ?? 'dibeli',
                ]);
            }

            foreach ($this->cleanItems($data['services'] ?? []) as $row) {
                $customer->services()->create([
                    'name'       => $row['name'],
                    'price'      => $row['price'] ?? 0,
                    'price_type' => $row['price_type'] ?? 'one_time',
                ]);
            }

            return $customer;
        });

        return redirect()
            ->route('documents')
            ->with('success', 'Pelanggan "'.$customer->name.'" berhasil ditambahkan.');
    }

    /**
     * Buang baris repeater yang kosong (tanpa nama) supaya input
     * barang/service yang tidak diisi tidak ikut tersimpan.
     */
    private function cleanItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(fn ($row) => trim((string) ($row['name'] ?? '')) !== '')
            ->map(function ($row) {
                $row['name'] = trim((string) $row['name']);

                return $row;
            })
            ->values()
            ->all();
    }

    public function destroy(Customer $customer)
    {
        // Hanya pemilik data yang boleh menghapus.
        abort_unless($customer->user_id === Auth::id(), 403);

        // Barang & service ikut terhapus, tautan dokumen dilepas. Catatan:
        // $customer->delete() adalah soft delete (UPDATE), jadi cascade/nullOnDelete
        // FK di database TIDAK ikut jalan (barisnya masih ada) — karena itu
        // semuanya dihapus/dilepas eksplisit di sini. Dokumen yang pernah dibuat
        // tetap tersimpan, hanya kolom customer_id-nya yang dikosongkan.
        $customer->documents()->update(['customer_id' => null]);
        $customer->barang()->delete();
        $customer->services()->delete();
        $customer->delete();

        return response()->json([
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }

    /**
     * Nomor kontrak berikutnya (auto-generate), format mengikuti nomor surat
     * dokumen: PREFIX/001/IX/2026 (per user, per bulan, per tahun).
     */
    private function nextContractNumber(): string
    {
        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $month = $romanMonths[now()->month - 1];
        $year = now()->year;

        $pattern = '/^'.preg_quote(self::CONTRACT_PREFIX, '/').'\/(\d+)\/'.$month.'\/'.$year.'$/';

        $maxNumber = Customer::withTrashed()
            ->where('user_id', Auth::id())
            ->pluck('contract_number')
            ->filter(fn ($nomor) => preg_match($pattern, (string) $nomor))
            ->map(fn ($nomor) => (int) preg_replace($pattern, '$1', (string) $nomor))
            ->max();

        $next = ((int) ($maxNumber ?? 0)) + 1;

        return self::CONTRACT_PREFIX.'/'.str_pad((string) $next, 3, '0', STR_PAD_LEFT).'/'.$month.'/'.$year;
    }

    /**
     * Perkiraan kode pelanggan berikutnya untuk placeholder Pelanggan ID.
     */
    private function peekNextCustomerCode(): string
    {
        $nextId = ((int) Customer::withTrashed()->max('id')) + 1;

        return 'PLG-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }
}