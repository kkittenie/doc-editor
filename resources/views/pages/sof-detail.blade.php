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
                <div class="mt-2">
                    @include('partials.sof.row-status', [
                        'status' => strtolower($sof->status ?? 'draft'),
                        'label'  => $sof->statusLabel(),
                    ])
                </div>
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
        <a href="{{ route("sof.edit", $sof) }}" class="btn-secondary text-xs"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
</svg> Edit S.O.F</a>
        <button type="button" onclick="confirmDelete()" class="rounded-lg border border-red-300 bg-white px-4 py-2 text-xs font-semibold text-red-700 hover:bg-red-50"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
</svg> Hapus</button>
        @if($sof->hasFile())
            <a href="{{ route("sof.download", $sof) }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-ink-900 px-4 text-xs font-semibold text-white hover:opacity-90"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
</svg> Unduh PDF</a>
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
