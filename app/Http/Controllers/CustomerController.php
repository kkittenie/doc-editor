<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\Service;
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
        return view('pages.customers', [
            'title' => 'Dokumen Saya',

            // Placeholder input Nomor Kontrak & Pelanggan ID di Form Pelanggan.
            'nextContractNumber' => $this->nextContractNumber(),
            'nextCustomerCode'   => $this->peekNextCustomerCode(),
        ]);
    }

    public function data(Request $request) 
    {
        $isAdmin = Auth::user()->hasRole('admin');

        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'all');
        $status = in_array($status, Customer::STATUSES, true) ? $status : 'all';

        $sortable = ['id', 'customer_number', 'name', 'contract_number', 'contract_name', 'active_date', 'active_months', 'finish_date', 'status'];
        $sort = in_array($request->input('sort'), $sortable, true)
            ? $request->input('sort')
            : 'created_at';
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10,25,50], true) ? $perPage : 10;

        $rawCounts = Customer::where('user_id', Auth::id())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = ['all' => (int) $rawCounts->sum()];
        foreach (Customer::STATUSES as $s) {
            $counts[$s] = (int) ($rawCounts[$s] ?? 0);
        }

        $paginator = Customer::where('user_id', Auth::id())
            ->withCount(['barang', 'services'])
            ->withMax('documents', 'updated_at')
            ->with(['latestDocument' => fn ($q) => $q->select('documents.id', 'documents.customer_id', 'documents.status')])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';

                $q->where(function ($w) use ($like, $search) {
                    $w->where('name', 'like', $like)
                        ->orWhere('customer_number', 'like', $like)
                        ->orWhere('contract_number', 'like', $like)
                        ->orWhere('contract_name', 'like', $like);

                        if (preg_match('/^plg-?0*(\d+)$/i', $search, $m)) {
                            $w->orWhere('id', (int) $m[1]);
                        }
                });
            })
            ->orderBy($sort, $dir)
            ->orderBy('id', $dir)
            ->paginate($perPage);

            return response()->json([
                'data' => $paginator->getCollection()
                    ->map(fn (Customer $c) => $this->transformCustomer($c, $isAdmin))
                    ->values(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'counts' => $counts,
            ]);
    }

    private function transformCustomer(Customer $customer, bool $isAdmin): array
    {
        $status = strtolower($customer->status ?? 'draft');
        $doc    = $customer->latestDocument;

        $lastUpdate = $customer->documents_max_updated_at
            ? \Carbon\Carbon::parse($customer->documents_max_updated_at)
            : $customer->updated_at;

        return [
            'id'              => 'PLG-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
            'databaseId'      => $customer->id,
            'customerNumber'  => $customer->customer_number,
            'name'            => $customer->name,
            'contractNumber'  => $customer->contract_number,
            'contractName'    => $customer->contract_name ?? '—',
            'activeDate'      => $customer->active_date ? $customer->active_date->format('d M Y') : '—',
            'activeMonths'    => $customer->active_months ? $customer->active_months . ' bulan' : '—',
            'finishDate'      => $customer->finish_date ? $customer->finish_date->format('d M Y') : '—',
            'barangCount'     => $customer->barang_count ?? 0,
            'serviceCount'    => $customer->services_count ?? 0,
            'status'          => $status,
            'statusUpdated'   => $lastUpdate ? $lastUpdate->format('d M Y H:i') : null,
            'statusLabel'     => $customer->statusLabel(),

            'createUrl'       => route('documents.create', $customer->id),
            'deleteUrl'       => route('customers.destroy', $customer->id),

            'documentId'      => $doc?->id,
            'documentStatus'  => $doc?->status,

            'canContinue'     => ! in_array($doc?->status, ['on_review', 'disetujui'], true),
            'canView'         => $isAdmin && $doc !== null,
            'viewUrl'         => $doc ? route('documents.edit', $doc->id) : null,

            'canApprove'         => $isAdmin && $doc?->status === 'on_review',
            'canRequestRevision' => $isAdmin && $doc?->status === 'on_review',
            'canExportPdf'       => $isAdmin && $doc !== null,
            'approveUrl'      => $doc ? route('documents.approve', $doc->id) : null,
            'statusUrl'       => $doc ? route('documents.Status', $doc->id) : null,
            'exportUrl'       => $doc ? route('documents.export', $doc->id) : null,
        ];
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
        // Hapus master + salinan per-dokumen (relasi barang()/services()
        // hanya memuat master/document_id NULL, jadi salinan dihapus eksplisit).
        Barang::where('customer_id', $customer->id)->delete();
        Service::where('customer_id', $customer->id)->delete();
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