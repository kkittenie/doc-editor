<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use App\Models\Barang;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CustomerController extends Controller
{
    private const CONTRACT_PREFIX = 'KTR';

    public function index()
    {
        return view('pages.customers', [
            'title' => 'Dokumen Saya',

            'nextContractNumber' => $this->nextContractNumber(),
            'nextCustomerCode'   => $this->peekNextCustomerCode(),
        ]);
    }

    public function data(Request $request)
    {
        $isAdmin = Auth::user()->hasRole('admin');

        $status = $request->input('status', 'all');
        $status = in_array($status, Customer::STATUSES, true) ? $status : 'all';

        $rawCounts = Customer::where('user_id', Auth::id())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = ['all' => (int) $rawCounts->sum()];
        foreach (Customer::STATUSES as $s) {
            $counts[$s] = (int) ($rawCounts[$s] ?? 0);
        }

        $query = Customer::query()
            ->where('user_id', Auth::id())
            ->withCount(['barang', 'services'])
            ->withMax('documents', 'updated_at')
            ->with(['latestDocument' => fn ($q) => $q->select('documents.id', 'documents.customer_id', 'documents.status')])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status));
            
        return DataTables::eloquent($query)
            ->filterColumn('id', function($q, $keyword) {
                if (preg_match('/^(?:plg-?)?0*(\d+)$/i', trim($keyword), $m)) {
                    $q->where('id', (int) $m[1]);
                }
            })
        ->editColumn('id', fn (Customer $c) => 'PLG-' . str_pad((string) $c->id, 5, '0', STR_PAD_LEFT))
        ->editColumn('name', fn (Customer $c) =>
            '<div class="text-sm font-semibold text-ink-900 dark:text-parchment-50">' . e($c->name) . '</div>'
            . '<div class="mt-0.5 text-[11px] text-slate-warm-400 dark:text-parchment-500">'
            . (int) $c->barang_count . ' barang · ' . (int) $c->services_count . ' service</div>')
        ->editColumn('contract_name', fn (Customer $c) => $c->contract_name ?: '—')
        ->editColumn('active_date', fn (Customer $c) => $c->active_date?->format('d M Y') ?? '—')
        ->editColumn('active_months', fn (Customer $c) => $c->active_months ? $c->active_months . ' bulan' : '—')
        ->editColumn('finish_date', fn (Customer $c) => $c->finish_date?->format('d M Y') ?? '—')
        ->editColumn('status', function (Customer $c) {
            $lastUpdate = $c->documents_max_updated_at
                ? \Carbon\Carbon::parse($c->documents_max_updated_at)
                : $c->updated_at;

            return view('partials.customer.row-status', [
                'status'     => strtolower($c->status ?? 'draft'),
                'label'      => $c->statusLabel(),
                'lastUpdate' => $lastUpdate,
            ])->render();
        })
        ->addColumn('action', fn (Customer $c) => view('partials.customer.row-actions', [
            'customer' => $c,
            'doc'      => $c->latestDocument,
            'isAdmin'  => $isAdmin,
        ])->render())
        ->rawColumns(['name', 'status', 'action'])
        ->with('counts', $counts)
        ->make(true);
    }       

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_number' => ['required', 'string', 'max:50'],
            'name'            => ['required', 'string', 'max:150'],
            'contract_number' => ['nullable', 'string', 'max:100'],

            'active_date'   => ['required', 'date'],
            'active_months' => ['required', 'integer', 'min:1', 'max:120'],

        
            'barang'                   => ['nullable', 'array', 'max:20'],
            'barang.*.name'            => ['nullable', 'string', 'max:150'],
            'barang.*.quantity'        => ['nullable', 'integer', 'min:1', 'max:100000'],
            'barang.*.price'           => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'barang.*.price_type'      => ['nullable', 'in:one_time,monthly'],
            'barang.*.ownership'       => ['nullable', 'in:disewa,dipinjamkan,dibeli'],

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

        $contractNumber = trim((string) ($data['contract_number'] ?? ''));
        if ($contractNumber === '') {
            $contractNumber = $this->nextContractNumber();
        }

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
        abort_unless($customer->user_id === Auth::id(), 403);
        $customer->documents()->update(['customer_id' => null]);
        Barang::where('customer_id', $customer->id)->delete();
        Service::where('customer_id', $customer->id)->delete();
        $customer->delete();

        return response()->json([
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }

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