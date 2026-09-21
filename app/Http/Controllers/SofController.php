<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menu S.O.F — repositori berkas Surat Order Formulir.
 *
 * Isinya KHUSUS kontrak yang berkas S.O.F-nya dibuat sistem (kolom `pdf_path`).
 * Approval dokumen terbaru memakai berkas kontrak hasil upload user
 * (DocumentController::approve) yang peruntukannya menggantikan isi dokumen di
 * Tabel Pelanggan — berkas upload itu sengaja tidak masuk ke sini.
 *
 * Dokumen yang disetujui SEBELUM fitur ini ada belum punya berkas; berkasnya
 * dibuat sekali saat pertama kali diunduh (lihat download()).
 */
class SofController extends Controller
{
    /**
     * Daftar S.O.F: pelanggan dengan kontrak berstatus "disetujui".
     */
    public function index()
    {
        $customers = Customer::where('user_id', Auth::id())
            ->where('status', 'disetujui')
            ->withCount(['barang', 'services'])
            ->with(['barang', 'services'])
            ->latest()
            ->get();

        // Dokumen disetujui terbaru untuk tiap pelanggan — dipakai sebagai
        // sumber berkas S.O.F dan tautan "Lihat Dokumen".
        //
        // Hanya dokumen yang punya berkas S.O.F hasil render sistem
        // (`pdf_path` terisi) yang dihitung. Kontrak yang disetujui lewat
        // upload berkas user tidak masuk Menu S.O.F: berkas finalnya dipakai
        // sebagai dokumen di Tabel Pelanggan (lihat DocumentController::approve).
        $approvedDocuments = Document::where('user_id', Auth::id())
            ->where('status', 'disetujui')
            ->whereNotNull('pdf_path')
            ->whereIn('customer_id', $customers->pluck('id'))
            ->orderByDesc('id')
            ->get()
            ->keyBy('customer_id');

        // Buang pelanggan yang tidak punya berkas S.O.F sistem.
        $customers = $customers
            ->filter(fn (Customer $customer) => $approvedDocuments->has($customer->id))
            ->values();

        $sofData = $customers->map(function (Customer $customer) use ($approvedDocuments) {
            $document = $approvedDocuments->get($customer->id);

            $fileReady = $document
                && $document->hasSofPdf()
                && Storage::disk('public')->exists($document->pdf_path);

            $totalBarang = $customer->barang->sum(
                fn ($item) => (float) $item->price * (int) $item->quantity
            );
            $totalService = $customer->services->sum(fn ($item) => (float) $item->price);

            return [
                'id' => 'PLG-' . str_pad((string) $customer->id, 5, '0', STR_PAD_LEFT),
                'databaseId' => $customer->id,
                'customerNumber' => $customer->customer_number,
                'name' => $customer->name,
                'contractNumber' => $customer->contract_number,
                'contractName' => $customer->contract_name ?? '—',
                'activeDate' => $customer->active_date
                    ? $customer->active_date->format('d M Y')
                    : '—',
                'activeMonths' => $customer->active_months
                    ? $customer->active_months . ' bulan'
                    : '—',
                'finishDate' => $customer->finish_date
                    ? $customer->finish_date->format('d M Y')
                    : '—',
                'barangCount' => $customer->barang_count ?? 0,
                'serviceCount' => $customer->services_count ?? 0,
                'totalBarang' => $totalBarang,
                'totalService' => $totalService,
                'totalValue' => $totalBarang + $totalService,
                'documentId' => $document?->id,
                'documentTitle' => $document?->title,
                'generatedAt' => $document?->pdf_generated_at
                    ? $document->pdf_generated_at->format('d M Y H:i')
                    : null,
                'fileReady' => (bool) $fileReady,
                'downloadUrl' => route('sof.download', $customer->id),
                'documentUrl' => $document
                    ? route('documents.edit', $document->id)
                    : null,
            ];
        })->toArray();

        return view('pages.sof', [
            'title' => 'Menu S.O.F',
            'sofData' => $sofData,
        ]);
    }

    /**
     * Unduh berkas PDF S.O.F milik kontrak pelanggan.
     *
     * Kalau berkas belum ada (dokumen disetujui sebelum fitur S.O.F ada),
     * berkas dibuat lebih dulu lalu disimpan, kemudian diunduh.
     */
    public function download(Customer $customer)
    {
        $user = Auth::user();

        // Hanya admin pemilik kontrak yang boleh mengunduh S.O.F-nya.
        abort_unless($user->hasRole('admin') && $customer->user_id === $user->id, 403);

        $document = $this->approvedDocument($customer);

        abort_unless($document, 404, 'Belum ada dokumen disetujui untuk pelanggan ini.');

        if (! $document->hasSofPdf() || ! Storage::disk('public')->exists($document->pdf_path)) {
            // regenerateSofPdf() melakukan pengecekan hak aksesnya sendiri
            // (admin + pemilik dokumen) — sama dengan pengecekan di atas.
            $path = app(DocumentController::class)->regenerateSofPdf($document);

            $document->update([
                'pdf_path' => $path,
                'pdf_generated_at' => $document->pdf_generated_at ?? now(),
            ]);
        }

        return Storage::disk('public')->download(
            $document->pdf_path,
            $this->downloadFilename($document, $customer)
        );
    }

    /**
     * Dokumen disetujui terbaru milik pelanggan (sumber berkas S.O.F).
     *
     * Wajib punya `pdf_path`: kontrak yang disetujui lewat upload berkas user
     * tidak pernah menyimpan berkas S.O.F, sehingga unduhan S.O.F-nya 404
     * (berkas finalnya diunduh dari tombol "Unduh PDF" di Tabel Pelanggan).
     */
    private function approvedDocument(Customer $customer): ?Document
    {
        return $customer->documents()
            ->where('status', 'disetujui')
            ->whereNotNull('pdf_path')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Nama berkas saat diunduh: SOF-<nomor kontrak>-<nama pelanggan>.pdf
     */
    private function downloadFilename(Document $document, Customer $customer): string
    {
        $label = trim((string) $customer->contract_number);

        if ($label === '') {
            $label = trim((string) $document->title);
        }

        $contract = Str::slug($label) ?: 'dokumen';
        $name = Str::slug((string) $customer->name);

        return $name === ''
            ? 'SOF-' . $contract . '.pdf'
            : 'SOF-' . $contract . '-' . $name . '.pdf';
    }
}
