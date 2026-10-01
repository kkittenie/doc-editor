<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use App\Models\Sof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SofController extends Controller
{
   
    private const ORDER_PREFIX = 'SOF';

    private function authorizeUser(): void
    {
        abort_unless(Auth::user()->hasRole('admin'), 403, 'Hanya admin yang dapat mengakses Menu S.O.F.');
    }

    
    private function ensureOwned(Sof $sof): void
    {
        abort_unless($sof->user_id === Auth::id(), 403);
    }

  
    public function index()
    {
        $this->authorizeUser();

        return view('pages.sof', [
            'title' => 'Menu S.O.F',
        ]);
    }

    public function data(Request $request)
    {
        $this->authorizeUser();

        $berkas = $request->input('berkas', 'all');
        $berkas = in_array($berkas, ['ready', 'pending'], true) ? $berkas : 'all';

        $total = Sof::where('user_id', Auth::id())->count();
        $approved = Sof::where('user_id', Auth::id())->where('status', 'approved')->count();
        $withFile = Sof::where('user_id', Auth::id())
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->count();

        $counts = [
            'total' => $total,
            'with_file' => $withFile,
            'approved'  => $approved,
            'pending'   => $total - $approved
        ];

        $query = Sof::query()
            ->where('user_id', Auth::id())
            ->when($berkas === 'ready', fn ($q) => $q
                ->whereNotNull('file_path')
                ->where('file_path', '!=', ''))
            ->when($berkas === 'pending', fn ($q) => $q->where(
                fn ($w) => $w->whereNull('file_path')->orWhere('file_path', '')
            ));

        return DataTables::eloquent($query)
        ->orderColumn('order_number', 'id $1')
        ->filterColumn('contract_number', function ($q, $keyword) {
            $like = '%' . $keyword . '%';
            $q->where(fn ($w) => $w->where('contract_number', 'like', $like)
                ->orWhere('contract_name', 'like', $like));
        })
        ->editColumn('order_number', fn (Sof $s) =>
            '<span class="font-mono text-xs text-ink-800 dark:text-parchment-200">' . e($s->order_number) . '</span>')
        ->editColumn('customer_name', fn (Sof $s) => $s->customer_name ?: '—')
        ->editColumn('contract_number', fn (Sof $s) =>
            '<span class="text-xs text-slate-warm-600 dark:text-parchment-400">' . e($s->contract_number ?: '—') . '</span>'
            . ($s->contract_name ? '<span class="block text-xs text-slate-warm-500">' . e($s->contract_name) . '</span>' : ''))
        ->editColumn('active_date', function (Sof $s) {
            if (! $s->active_date) {
                return '—';
            }
            $periode = $s->active_date->format('d M Y');
            if ($s->finish_date) {
                $periode .= ' — ' . $s->finish_date->format('d M Y');
            }
            if ($s->active_months) {
                $periode .= ' (' . $s->active_months . ' bln)';
            }

            return $periode;
        })
        ->editColumn('total_value', fn (Sof $s) => (float) $s->total_value > 0
            ? 'Rp ' . number_format((float) $s->total_value, 0, ',', '.')
            : '—')
        ->addColumn('has_file', fn (Sof $s) => $s->hasFile()
            ? '<span class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-[10px] font-semibold text-green-800">Ada</span>'
            : '<span class="inline-flex items-center rounded bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-700">Kosong</span>')
        ->editColumn('status', fn (Sof $s) =>
            '<span class="inline-block rounded-full px-2.5 py-1 text-[10px] font-semibold '
            . ($s->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-700') . '">'
            . e($s->statusLabel()) . '</span>')
        ->addColumn('action', fn (Sof $s) => view('partials.sof.row-actions', ['sof' => $s])->render())
        ->rawColumns(['order_number', 'contract_number', 'has_file', 'status', 'action'])
        ->with('counts', $counts)
        ->make(true);
    }

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

        $data['total_value'] = $data['total_value'] ?? 0;

            $orderNumber = $this->nextOrderNumber();

        // Hitung finish_date otomatis dari active date + active months.
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

    public function show(Sof $sof)
    {
        $this->authorizeUser();
        $this->ensureOwned($sof);

        return view('pages.sof-detail', [
            'title' => 'Detail S.O.F — ' . $sof->order_number,
            'sof'   => $sof,
        ]);
    }

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

        $data['total_value'] = $data['total_value'] ?? 0;

        if (!empty($data['active_date']) && !empty($data['active_months'])) {
            $data['finish_date'] = \Carbon\Carbon::parse($data['active_date'])
                ->addMonths((int) $data['active_months'])
                ->toDateString();
        } else {
            $data['finish_date'] = null;
        }

        $sof->update($data);
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
