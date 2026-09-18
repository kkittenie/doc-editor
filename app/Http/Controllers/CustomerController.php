<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Halaman "Dokumen Saya" (Tabel Pelanggan).
 *
 * Alur: isi Form Pelanggan (Nomer Pelanggan, Nama Pelanggan, Nomor Kontrak)
 * → Simpan → baris muncul di Tabel Pelanggan dengan status default Draft.
 * Kolom kontrak (Nama Kontrak, Tanggal Aktif, Masa Aktif, Tanggal Selesai)
 * baru terisi setelah user menyelesaikan form di Studio Editor.
 */
class CustomerController extends Controller
{
    /** Prefix nomor kontrak hasil auto-generate. */
    private const CONTRACT_PREFIX = 'KTR';

    public function index()
    {
        $customers = Customer::where('user_id', Auth::id())
            ->withCount(['barang', 'services', 'documents'])
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
        ], [
            'customer_number.required' => 'Nomer Pelanggan wajib diisi.',
            'name.required'            => 'Nama Pelanggan wajib diisi.',
        ]);

        // Nomor kontrak: kalau user tidak mengetik apa pun, pakai auto-generate.
        $contractNumber = trim((string) ($data['contract_number'] ?? ''));
        if ($contractNumber === '') {
            $contractNumber = $this->nextContractNumber();
        }

        $customer = Customer::create([
            'user_id'         => Auth::id(),
            'customer_number' => $data['customer_number'],
            'name'            => $data['name'],
            'contract_number' => $contractNumber,
            'status'          => 'draft',
        ]);

        return redirect()
            ->route('documents')
            ->with('success', 'Pelanggan "'.$customer->name.'" berhasil ditambahkan.');
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