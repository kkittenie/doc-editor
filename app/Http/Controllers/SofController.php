<?php

namespace App\Http\Controllers;

use App\Models\Sof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menu S.O.F — repositori berkas Surat Order Formulir.
 *
 * Entitas MANDIRI: tidak ada relasi ke tabel Customer maupun Document.
 * Segala data (nomor pelanggan, nomor kontrak, nama, periode, nilai, berkas
 * PDF) diisi langsung di form ini — tidak bersinggungan dengan apa pun di
 * sidebar "Dokumen Saya".
 *
 * CRUD: index → daftar, create/store → input data + upload PDF,
 *       show/download → unduh berkas, edit/update → koreksi, destroy → hapus.
 */
class SofController extends Controller
{
    /** Prefix nomor Order Form hasil auto-generate. */
    private const ORDER_PREFIX = 'SOF';

    /** Hanya admin pemilik data yang boleh mengakses semua aksi ini. */
    private function authorizeUser(): void
    {
        abort_unless(Auth::user()->hasRole('admin'), 403, 'Hanya admin yang dapat mengakses Menu S.O.F.');
    }

    /** Pastikan user yang login adalah pemilik data S.O.F. */
    private function ensureOwned(Sof $sof): void
    {
        abort_unless($sof->user_id === Auth::id(), 403);
    }

    /**
     * Daftar S.O.F milik user yang login (entitas mandiri).
     */
        public function index()
    {
        $this->authorizeUser();

        $sofs = Sof::where('user_id', Auth::id())
            ->withTrashed()
            ->latest()
            ->get();

        // Petakan ke array terformat untuk frontend Alpine.js (mirip pola Tabel Pelanggan).
        $sofData = $sofs->map(function (Sof $sof) {
            $periode = '';
            if ($sof->active_date) {
                $periode = $sof->active_date->format('d M Y');
                if ($sof->finish_date) {
                    $periode .= ' — ' . $sof->finish_date->format('d M Y');
                }
                if ($sof->active_months) {
                    $periode .= ' (' . $sof->active_months . ' bln)';
                }
            }

            return [
                'id'              => $sof->id,
                'order_number'    => $sof->order_number,
                'customer_number' => $sof->customer_number,
                'customer_name'   => $sof->customer_name,
                'contract_number' => $sof->contract_number,
                'contract_name'   => $sof->contract_name,
                'active_date'     => $sof->active_date ? $sof->active_date->format('Y-m-d') : null,
                'finish_date'     => $sof->finish_date ? $sof->finish_date->format('Y-m-d') : null,
                'active_months'   => $sof->active_months,
                'total_value'     => (float) $sof->total_value,
                'status'          => $sof->status,
                'status_label'    => $sof->statusLabel(),
                'has_file'        => $sof->hasFile(),
                'periode_label'   => $periode ?: '—',
                'detail_url'      => route('sof.show', $sof),
                'edit_url'        => route('sof.edit', $sof),
                'download_url'    => route('sof.download', $sof),
                'delete_url'      => route('sof.destroy', $sof),
            ];
        })->toArray();

        return view('pages.sof', [
            'title' => 'Menu S.O.F',
            'sofs'  => $sofData,
        ]);
    }

    /**
     * Form tambah S.O.F baru (CRUD create).
     */
    public function create()
    {
        $this->authorizeUser();

        return view('pages.sof-form', [
            'title'                  => 'Tambah S.O.F',
            'action'                 => route('sof.store'),
            'method'                 => 'POST',
            'sof'                    => null,
            'orderNumberPlaceholder' => $this->nextOrderNumber(),
        ]);
    }

    /**
     * Simpan entri S.O.F baru (CRUD store + upload file).
     */
    public function store(Request $request)
    {
        $this->authorizeUser();

        $data = $request->validate([
            'customer_number' => ['required', 'string', 'max:50'],
            'customer_name'   => ['required', 'string', 'max:150'],
            'contract_number' => ['nullable', 'string', 'max:100'],
            'contract_name'   => ['nullable', 'string', 'max:255'],
            'active_date'     => ['nullable', 'date'],
            'active_months'   => ['nullable', 'integer', 'min:1', 'max:120'],
            'total_value'     => ['nullable', 'numeric', 'min:0'],
            'revision_notes'  => ['nullable', 'array'],
            'file_path'       => ['nullable', 'string'],
        ], [
            'customer_number.required' => 'Nomer Pelanggan wajib diisi.',
            'customer_name.required'   => 'Nama Pelanggan wajib diisi.',
            'active_date.date'         => 'Tanggal Aktif harus berupa tanggal yang valid.',
            'active_months.integer'    => 'Masa Aktif harus berupa angka.',
        ]);

                $orderNumber = $this->nextOrderNumber();

        // Hitung finish_date otomatis dari active_date + active_months.
        if (!empty($data['active_date']) && !empty($data['active_months'])) {
            $data['finish_date'] = \Carbon\Carbon::parse($data['active_date'])
                ->addMonths((int) $data['active_months'])
                ->toDateString();
        }

        $sof = Sof::create(array_merge($data, [
            'user_id'      => Auth::id(),
            'order_number' => $orderNumber,
            'status'       => 'draft',
        ]));

        if ($request->filled('file_path')) {
            $this->storeUploadedFile($sof, $request->string('file_path'));
        }

        return redirect()
            ->route('sof.index')
            ->with('success', 'S.O.F "'.$sof->order_number.'" berhasil ditambahkan.');
    }
        /**
     * Detail / preview S.O.F (CRUD read detail).
     */
    public function show(Sof $sof)
    {
        $this->authorizeUser();
        $this->ensureOwned($sof);

        return view('pages.sof-detail', [
            'title' => 'Detail S.O.F — ' . $sof->order_number,
            'sof'   => $sof,
        ]);
    }

    /**
     * Form edit S.O.F (CRUD edit).
     */
    public function edit(Sof $sof)
    {
        $this->authorizeUser();
        $this->ensureOwned($sof);

        return view('pages.sof-form', [
            'title'                  => 'Edit S.O.F',
            'action'                 => route('sof.update', $sof),
            'method'                 => 'PUT',
            'sof'                    => $sof,
            'orderNumberPlaceholder' => $sof->order_number,
        ]);
    }

    /**
     * Update entri S.O.F (CRUD update + optional re-upload file).
     */
    public function update(Request $request, Sof $sof)
    {
        $this->authorizeUser();
        $this->ensureOwned($sof);

        $data = $request->validate([
            'customer_number' => ['required', 'string', 'max:50'],
            'customer_name'   => ['required', 'string', 'max:150'],
            'contract_number' => ['nullable', 'string', 'max:100'],
            'contract_name'   => ['nullable', 'string', 'max:255'],
            'active_date'     => ['nullable', 'date'],
            'active_months'   => ['nullable', 'integer', 'min:1', 'max:120'],
            'total_value'     => ['nullable', 'numeric', 'min:0'],
            'revision_notes'  => ['nullable', 'array'],
            'file_path'       => ['nullable', 'string'],
        ], [
            'customer_number.required' => 'Nomer Pelanggan wajib diisi.',
            'customer_name.required'   => 'Nama Pelanggan wajib diisi.',
        ]);

        // Hitung finish_date otomatis dari active_date + active_months.
        if (!empty($data['active_date']) && !empty($data['active_months'])) {
            $data['finish_date'] = \Carbon\Carbon::parse($data['active_date'])
                ->addMonths((int) $data['active_months'])
                ->toDateString();
        } else {
            // Reset finish_date bila periode dikosongkan.
            $data['finish_date'] = null;
        }

        $sof->update($data);

        // Jika ada berkas baru (via pop up upload), hapus berkas lama.
        if ($request->filled('file_path')) {
            if ($sof->file_path && Storage::disk('public')->exists($sof->file_path)) {
                Storage::disk('public')->delete($sof->file_path);
            }
            $this->storeUploadedFile($sof, $request->string('file_path'));
        }

        return redirect()
            ->route('sof.index')
            ->with('success', 'S.O.F "'.$sof->order_number.'" berhasil diperbarui.');
    }

    /**
     * Hapus S.O.F (CRUD delete — soft delete).
     */
    public function destroy(Sof $sof)
    {
        $this->authorizeUser();
        $this->ensureOwned($sof);

        if ($sof->file_path && Storage::disk('public')->exists($sof->file_path)) {
            Storage::disk('public')->delete($sof->file_path);
        }

        $sof->delete();

        return response()->json([
            'message' => 'S.O.F "'.$sof->order_number.'" berhasil dihapus.',
        ]);
    }

    /**
     * Unduh berkas PDF S.O.F yang sudah di-scan / di-upload.
     */
    public function download(Sof $sof)
    {
        $this->authorizeUser();
        $this->ensureOwned($sof);

        abort_unless($sof->hasFile(), 404, 'Belum ada berkas PDF untuk S.O.F ini.');
        abort_unless(
            Storage::disk('public')->exists($sof->file_path),
            404,
            'Berkas tidak ditemukan di penyimpanan.'
        );

        return Storage::disk('public')->download(
            $sof->file_path,
            $sof->fileName()
        );
    }
        /**
     * Nomor Order Form berikutnya (auto-generate).
     * Format sama seperti nomor kontrak: SOF/001/IX/2026 (per user, per bulan, per tahun).
     */
    private function nextOrderNumber(): string
    {
        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $month = $romanMonths[now()->month - 1];
        $year = now()->year;

        $pattern = '/^' . preg_quote(self::ORDER_PREFIX, '/') . '\/(\d+)\/' . $month . '\/' . $year . '$/';

        $maxNumber = Sof::withTrashed()
            ->where('user_id', Auth::id())
            ->pluck('order_number')
            ->filter(fn ($nomor) => preg_match($pattern, (string) $nomor))
            ->map(fn ($nomor) => (int) preg_replace($pattern, '$1', (string) $nomor))
            ->max();

        $next = ((int) ($maxNumber ?? 0)) + 1;

        return self::ORDER_PREFIX . '/' . str_pad((string) $next, 3, '0', STR_PAD_LEFT) . '/' . $month . '/' . $year;
    }

    /**
     * Simpan berkas PDF hasil scan ke folder khusus S.O.F.
     *
     * $storedPath adalah path relatif pada disk 'public' yang sudah
     * di-upload sebelumnya (oleh uploadFile()).
     */
    private function storeUploadedFile(Sof $sof, string $storedPath): void
    {
        if (! Storage::disk('public')->exists($storedPath)) {
            return;
        }

        $fileName  = $sof->fileName();
        $finalPath = 'sofs/' . $sof->id . '/' . $fileName;

        Storage::disk('public')->move($storedPath, $finalPath);

        $sof->update([
            'file_path'        => $finalPath,
            'file_name'        => $fileName,
            'file_uploaded_at' => now(),
            'status'           => 'approved',
        ]);
    }

    /**
     * Upload temporer untuk pop up file (dipanggil oleh Swal modal).
     * Validasi konsisten dengan DocumentController::approve().
     */
    public function uploadFile(Request $request)
    {
        $this->authorizeUser();

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'file.required' => 'Pilih berkas kontrak terlebih dahulu.',
            'file.mimes'   => 'Berkas harus berformat PDF.',
            'file.max'     => 'Ukuran berkas maksimal 10 MB.',
        ]);

        $path = $request->file('file')->store('sofs/tmp', 'public');

        return response()->json(['path' => $path]);
    }
}
