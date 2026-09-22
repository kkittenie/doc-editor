@extends("layouts.app")
@section("content")
@php
    /** @var \App\Models\Sof $sof */
    if (! $sof) { abort(404); }
@endphp
<div class="mx-auto max-w-4xl py-10 px-4">
    <div class="mb-8 flex flex-col items-center justify-between gap-4 sm:flex-row">
        <h1 class="font-serif text-2xl font-bold text-ink-900 dark:text-parchment-50">Detail S.O.F</h1>
        <a href="{{ route("sof.index") }}" class="btn-ghost shrink-0 text-xs">← Menu S.O.F</a>
    </div>
    @if (session("success"))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session("success") }}</div>
    @endif
    <div class="rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm dark:border-slate-warm-800 dark:bg-slate-warm-900">
        <div class="mb-4 pb-3 border-b border-parchment-200 dark:border-slate-warm-800">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-warm-500">Order Number</h2>
            <p class="mt-1 font-mono text-lg text-ink-900 dark:text-parchment-50">{{ $sof->order_number }}</p>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div><span class="text-xs font-semibold uppercase text-slate-warm-500">Nomer Pelanggan</span><p class="mt-1 text-sm text-ink-900 dark:text-parchment-100">{{ $sof->customer_number ?: "—" }}</p></div>
            <div><span class="text-xs font-semibold uppercase text-slate-warm-500">Nama Pelanggan</span><p class="mt-1 text-sm text-ink-900 dark:text-parchment-100">{{ $sof->customer_name ?: "—" }}</p></div>
            <div><span class="text-xs font-semibold uppercase text-slate-warm-500">Nomor Kontrak</span><p class="mt-1 text-sm text-ink-900 dark:text-parchment-100">{{ $sof->contract_number ?: "—" }}</p></div>
            <div><span class="text-xs font-semibold uppercase text-slate-warm-500">Nama Kontrak</span><p class="mt-1 text-sm text-ink-900 dark:text-parchment-100">{{ $sof->contract_name ?: "—" }}</p></div>
            <div><span class="text-xs font-semibold uppercase text-slate-warm-500">Periode</span><p class="mt-1 text-sm text-ink-900 dark:text-parchment-100">
                @if($sof->active_date)
                    {{ $sof->active_date->format("d M Y") }}
                    @if($sof->finish_date) — {{ $sof->finish_date->format("d M Y") }} @endif
                    @if($sof->active_months) ({{ $sof->active_months }} bln) @endif
                @else — @endif
            </p></div>
            <div><span class="text-xs font-semibold uppercase text-slate-warm-500">Nilai Total</span>
                <p class="mt-1 text-sm text-ink-900 dark:text-parchment-100">{{ $sof->total_value ? "Rp " . number_format((float) $sof->total_value, 0, ",", ".") : "—" }}</p></div>
            <div class="sm:col-span-2"><span class="text-xs font-semibold uppercase text-slate-warm-500">Status</span>
                <span class="mt-1 inline-block rounded-full px-3 py-1 text-[10px] font-semibold {{ strtolower($sof->status) === "approved" ? "bg-green-100 text-green-800" : "bg-slate-100 text-slate-700" }}">{{ $sof->statusLabel() }}</span>
            </div>
        </div>
    </div>
    <div class="mt-6 rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm dark:border-slate-warm-800 dark:bg-slate-warm-900">
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-warm-500">Berkas PDF</h2>
        @if($sof->hasFile())
            <div class="flex items-center justify-between rounded-xl border border-parchment-300 bg-parchment-50/60 p-4">
                <div><p class="text-sm font-medium text-ink-900 dark:text-parchment-100">{{ $sof->file_name ?: "berkas.pdf" }}</p><p class="text-[11px] text-slate-warm-500">Di-upload: {{ $sof->file_uploaded_at ? $sof->file_uploaded_at->format("d M Y H:i") : "—" }}</p></div>
                <a href="{{ route("sof.download", $sof) }}" target="_blank" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-ink-900 px-4 text-xs font-semibold text-white hover:opacity-90">📥 Unduh</a>
            </div>
        @else
            <p class="text-sm text-slate-warm-500">Belum ada berkas PDF. Klik "Edit" untuk meng‑upload.</p>
        @endif
    </div>
    @if($sof->revision_notes && is_array($sof->revision_notes) && ($sof->revision_notes["reason"] ?? ""))
        <div class="mt-6 rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-warm-500">Catatan Revisi</h2>
            <p class="text-xs text-slate-warm-500">{{ $sof->revision_notes["reason"] }}</p>
        </div>
    @endif
    <div class="mt-8 flex items-center justify-end gap-3 border-t border-parchment-200 pt-6 dark:border-slate-warm-800">
        <form id="delete-form" method="POST" action="{{ route("sof.destroy", $sof) }}">@csrf @method("DELETE") </form>
        <a href="{{ route("sof.edit", $sof) }}" class="btn-secondary text-xs">✏️ Edit S.O.F</a>
        <button type="button" onclick="confirmDelete()" class="rounded-lg border border-red-300 bg-white px-4 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">🗑️ Hapus</button>
        @if($sof->hasFile())
            <a href="{{ route("sof.download", $sof) }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-ink-900 px-4 text-xs font-semibold text-white hover:opacity-90">⬇️ Unduh PDF</a>
        @endif
    </div>
</div>
@push("scripts")
<script>
async function confirmDelete() {
    const r = await Swal.fire({icon:"warning",title:"Hapus S.O.F?",text:"Berkas dan data akan dihapus permanen. Lanjutkan?",showCancelButton:true,confirmButtonText:"Ya, hapus",cancelButtonText:"Batal",confirmButtonColor:"#dc2626"});
    if (r.isConfirmed) document.getElementById("delete-form").submit();
}
</script>
@endpush
@endsection
