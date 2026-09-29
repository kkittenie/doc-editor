{{-- ============================================================================
     DROPDOWN "INFO KONTRAK" PADA TOPBAR EDITOR
     ----------------------------------------------------------------------------
     Saat user menekan "Lanjut" di Tabel Pelanggan, status kontrak naik ke
     On Progress dan mereka langsung masuk editor — sehingga nomor kontrak,
     nama kontrak, periode, serta data barang & service tidak lagi terlihat
     di halaman lain. Panel ini mengembalikannya sebagai dropdown read-only.

     Data dirender server (tanpa request tambahan) dan TIDAK mengubah isi
     dokumen. State Alpine (showContractInfo, contractTab) milik
     wordDocumentEditor() di pages/editor.blade.php.
     ============================================================================ --}}
@php
    $infoCustomer = $contractCustomer ?? null;
    $infoBarang = $contractBarang ?? collect();
    $infoServices = $contractServices ?? collect();
    $infoTotals = $contractTotals ?? ['one_time' => 0, 'monthly' => 0];
    $infoFromMaster = $contractDataFromMaster ?? false;

    $infoStatus = strtolower($infoCustomer->status ?? $document->status ?? 'draft');
    $infoStatusLabel = $infoCustomer?->statusLabel() ?? $document->statusLabel();

    // Warna badge disalin dari statusClass() di pages/customers.blade.php
    // supaya satu status selalu tampil dengan warna yang sama.
    $infoStatusClass = match ($infoStatus) {
        'draft' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30',
        'on_progress' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:border-sky-500/30',
        'on_review' => 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-500/15 dark:text-violet-300 dark:border-violet-500/30',
        'revisi' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/15 dark:text-orange-300 dark:border-orange-500/30',
        'disetujui' => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/15 dark:text-green-300 dark:border-green-500/30',
        default => 'bg-parchment-100 text-slate-warm-600 border-parchment-300 dark:bg-slate-warm-800 dark:text-parchment-300 dark:border-slate-warm-700',
    };

    // Label pendek template kontrak (key = body_content['templateKey']).
    $infoTemplateLabels = [
        'kontrak-kemitraan' => 'Kontrak Kemitraan',
        'kontrak-colocation' => 'Kontrak Colocation',
        'kontrak-managed-service' => 'Kontrak Managed Service',
        'kontrak-soho' => 'Jasa SOHO',
        'kontrak-payung' => 'Kontrak Payung Metro',
    ];
    $infoTemplate = $infoTemplateLabels[$document->body_content['templateKey'] ?? ''] ?? null;

    $infoContractNumber = $infoCustomer?->contract_number
        ?: ($document->header_data['nomorSurat'] ?? null);
    $infoContractName = $infoCustomer?->contract_name ?: $document->title;

    $infoMoney = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $infoPageCount = count(
        $document->body_content['pages'] ?? [$document->body_content['content'] ?? '']
    );
    // Ringkasan dua kolom: detail kontrak kalau dokumen terhubung pelanggan,
    // kalau tidak pakai identitas dokumen supaya panel tetap berguna.
    $infoFacts = $infoCustomer
        ? [
            ['label' => 'Pelanggan', 'value' => $infoCustomer->name],
            ['label' => 'Nomer Pelanggan', 'value' => $infoCustomer->customer_number],
            ['label' => 'Tanggal Aktif', 'value' => $infoCustomer->active_date?->format('d M Y')],
            ['label' => 'Masa Aktif', 'value' => $infoCustomer->active_months ? $infoCustomer->active_months . ' bulan' : null],
            ['label' => 'Tanggal Selesai', 'value' => $infoCustomer->finish_date?->format('d M Y')],
            ['label' => 'Jenis Template', 'value' => $infoTemplate],
        ]
        : [
            ['label' => 'Jenis Dokumen', 'value' => ucfirst((string) ($document->type ?? 'surat'))],
            ['label' => 'Jumlah Halaman', 'value' => $infoPageCount . ' halaman'],
            ['label' => 'Jenis Template', 'value' => $infoTemplate],
            ['label' => 'Terakhir Diperbarui', 'value' => $document->updated_at?->format('d M Y H:i')],
        ];

    $infoFacts = array_values(array_filter($infoFacts, fn ($fact) => filled($fact['value'])));
@endphp

<div class="relative print:hidden">
    {{-- TRIGGER: chip kecil di grup kiri topbar, sebelah judul dokumen. --}}
    <button type="button" @click="showContractInfo = !showContractInfo"
        :aria-expanded="showContractInfo ? 'true' : 'false'" aria-haspopup="true" title="Lihat informasi kontrak"
        class="inline-flex h-9 items-center gap-2 rounded-lg border border-parchment-300 bg-white px-3 text-[11px] font-semibold text-ink-700 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:bg-transparent dark:text-parchment-200 dark:hover:border-bronze-500 dark:hover:bg-bronze-500 dark:hover:text-ink-900">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="shrink-0">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 11v5" />
            <path d="M12 8h.01" />
        </svg>

        <span>Info Kontrak</span>

        @if ($infoContractNumber)
            <span
                class="hidden max-w-[150px] truncate font-mono text-[10px] font-normal text-slate-warm-500 dark:text-parchment-400 md:inline">{{ $infoContractNumber }}</span>
        @endif

        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            class="shrink-0 transition-transform" :class="showContractInfo ? 'rotate-180' : ''">
            <polyline points="6 9 12 15 18 9" />
        </svg>
    </button>

    {{-- PANEL --}}
    <div x-show="showContractInfo" x-cloak @click.outside="showContractInfo = false"
        @keydown.escape.window="showContractInfo = false"
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
        class="custom-scrollbar absolute left-0 z-50 mt-2 max-h-[70vh] w-[min(92vw,420px)] overflow-y-auto rounded-xl border border-parchment-300 bg-white p-4 shadow-theme-lg dark:border-slate-warm-700 dark:bg-slate-warm-900">

        {{-- KEPALA: status + nama & nomor kontrak --}}
        <div class="flex items-start justify-between gap-3">
            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-warm-500 dark:text-parchment-400">
                Info Kontrak
            </p>

            <span
                class="inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $infoStatusClass }}">
                {{ $infoStatusLabel }}
            </span>
        </div>

        <h3 class="mt-1.5 font-serif text-sm font-bold text-ink-900 dark:text-parchment-50">
            {{ $infoContractName }}
        </h3>

        <p class="mt-0.5 truncate font-mono text-[11px] text-bronze-700 dark:text-bronze-400">
            {{ $infoContractNumber ?: 'Nomor kontrak belum diisi' }}
        </p>

        {{-- RINGKASAN --}}
        <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2 border-t border-parchment-200 pt-3 dark:border-slate-warm-800">
            @foreach ($infoFacts as $fact)
                <div class="min-w-0">
                    <dt class="text-[10px] uppercase tracking-wide text-slate-warm-400 dark:text-parchment-500">
                        {{ $fact['label'] }}
                    </dt>
                    <dd class="mt-0.5 truncate text-[11px] font-medium text-ink-900 dark:text-parchment-100"
                        title="{{ $fact['value'] }}">
                        {{ $fact['value'] }}
                    </dd>
                </div>
            @endforeach
        </dl>

        @if ($infoCustomer)
        {{-- DATA BARANG & SERVICE (salinan dokumen; fallback master pelanggan) --}}
        <div class="mt-3 border-t border-parchment-200 pt-3 dark:border-slate-warm-800">
            <div
                class="flex items-center gap-1 rounded-lg border border-parchment-200 bg-parchment-50 p-1 dark:border-slate-warm-800 dark:bg-slate-warm-800">
                <button type="button" @click="contractTab = 'barang'"
                    :class="contractTab === 'barang' ? 'bg-white text-ink-900 shadow-theme-xs dark:bg-slate-warm-900 dark:text-parchment-50' : 'text-slate-warm-500 dark:text-parchment-400'"
                    class="flex-1 rounded-md px-2 py-1 text-[11px] font-semibold transition">
                    Barang ({{ $infoBarang->count() }})
                </button>

                <button type="button" @click="contractTab = 'service'"
                    :class="contractTab === 'service' ? 'bg-white text-ink-900 shadow-theme-xs dark:bg-slate-warm-900 dark:text-parchment-50' : 'text-slate-warm-500 dark:text-parchment-400'"
                    class="flex-1 rounded-md px-2 py-1 text-[11px] font-semibold transition">
                    Service ({{ $infoServices->count() }})
                </button>
            </div>

            @if ($infoFromMaster)
                <p class="mt-2 text-[10px] leading-snug text-amber-700 dark:text-amber-300">
                    Menampilkan data master pelanggan — dokumen ini belum punya salinan barang/service.
                </p>
            @endif

            {{-- BARANG --}}
            <div x-show="contractTab === 'barang'" class="mt-2 space-y-1.5">
                @forelse ($infoBarang as $index => $item)
                    <div
                        class="flex items-start justify-between gap-3 rounded-lg border border-parchment-200 px-2.5 py-2 dark:border-slate-warm-800">
                        <div class="min-w-0">
                            <p class="truncate text-[11px] font-semibold text-ink-900 dark:text-parchment-100">
                                {{ $item->name }}
                            </p>

                            <p class="mt-0.5 text-[10px] text-slate-warm-500 dark:text-parchment-400">
                                <span
                                    class="font-mono">BRG-{{ str_pad($index + 1, 5, '0', STR_PAD_LEFT) }}</span>
                                · {{ (int) ($item->quantity ?? 1) }} unit
                                · {{ ($item->price_type ?? 'one_time') === 'monthly' ? 'Bulanan' : 'One Time' }}
                                · {{ ucfirst((string) ($item->ownership ?? 'dibeli')) }}
                            </p>
                        </div>

                        <p
                            class="shrink-0 whitespace-nowrap font-mono text-[11px] font-semibold text-ink-900 dark:text-parchment-100">
                            {{ $infoMoney($item->price) }}
                        </p>
                    </div>
                @empty
                    <p
                        class="rounded-lg bg-parchment-50 px-2.5 py-3 text-center text-[10px] text-slate-warm-400 dark:bg-slate-warm-800 dark:text-parchment-500">
                        Belum ada barang untuk kontrak ini.
                    </p>
                @endforelse
            </div>
            {{-- SERVICE --}}
            <div x-show="contractTab === 'service'" x-cloak class="mt-2 space-y-1.5">
                @forelse ($infoServices as $index => $item)
                    <div
                        class="flex items-start justify-between gap-3 rounded-lg border border-parchment-200 px-2.5 py-2 dark:border-slate-warm-800">
                        <div class="min-w-0">
                            <p class="truncate text-[11px] font-semibold text-ink-900 dark:text-parchment-100">
                                {{ $item->name }}
                            </p>

                            <p class="mt-0.5 text-[10px] text-slate-warm-500 dark:text-parchment-400">
                                <span
                                    class="font-mono">SRV-{{ str_pad($index + 1, 5, '0', STR_PAD_LEFT) }}</span>
                                · {{ ($item->price_type ?? 'one_time') === 'monthly' ? 'Bulanan' : 'One Time' }}
                            </p>
                        </div>

                        <p
                            class="shrink-0 whitespace-nowrap font-mono text-[11px] font-semibold text-ink-900 dark:text-parchment-100">
                            {{ $infoMoney($item->price) }}
                        </p>
                    </div>
                @empty
                    <p
                        class="rounded-lg bg-parchment-50 px-2.5 py-3 text-center text-[10px] text-slate-warm-400 dark:bg-slate-warm-800 dark:text-parchment-500">
                        Belum ada service untuk kontrak ini.
                    </p>
                @endforelse
            </div>

            {{-- TOTAL --}}
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="rounded-lg bg-parchment-50 px-2.5 py-2 dark:bg-slate-warm-800">
                    <p class="text-[10px] uppercase tracking-wide text-slate-warm-500 dark:text-parchment-400">
                        Total One Time
                    </p>
                    <p class="mt-0.5 font-mono text-[11px] font-bold text-ink-900 dark:text-parchment-50">
                        {{ $infoMoney($infoTotals['one_time'] ?? 0) }}
                    </p>
                </div>

                <div class="rounded-lg bg-parchment-50 px-2.5 py-2 dark:bg-slate-warm-800">
                    <p class="text-[10px] uppercase tracking-wide text-slate-warm-500 dark:text-parchment-400">
                        Total Bulanan
                    </p>
                    <p class="mt-0.5 font-mono text-[11px] font-bold text-ink-900 dark:text-parchment-50">
                        {{ $infoMoney($infoTotals['monthly'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>
        @else
            <p
                class="mt-3 border-t border-parchment-200 pt-3 text-[10px] leading-snug text-slate-warm-400 dark:border-slate-warm-800 dark:text-parchment-500">
                Dokumen ini tidak terhubung ke data pelanggan, jadi tidak ada rincian barang &amp; service.
            </p>
        @endif

        {{-- FOOTER: jalan pintas ke tabel pelanggan. --}}
        <div class="mt-3 border-t border-parchment-200 pt-2 dark:border-slate-warm-800">
            <a href="{{ route('documents') }}"
                class="inline-flex items-center gap-1 text-[11px] font-semibold text-bronze-700 hover:underline dark:text-bronze-400">
                Lihat di Dokumen Saya →
            </a>
        </div>
    </div>
</div>
