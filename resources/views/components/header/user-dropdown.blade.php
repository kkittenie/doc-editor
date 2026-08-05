<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- User Button -->
    <button
        class="flex items-center text-ink-900 dark:text-parchment-200 hover:text-bronze-700 transition-colors"
        @click.prevent="toggleDropdown()"
        type="button"
    >
        <span class="mr-2.5 flex h-9 w-9 items-center justify-center rounded-full bg-ink-900 text-parchment-100 font-serif font-bold text-xs shadow-xs dark:bg-parchment-100 dark:text-ink-900 border border-parchment-300">
            AB
        </span>

        <span class="hidden md:block mr-1 font-serif font-bold text-xs">Aris Budiman</span>

        <!-- Chevron Icon -->
        <svg
            class="w-4 h-4 text-slate-warm-400 transition-transform duration-200"
            :class="{ 'rotate-180': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Panel -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-3 flex w-[260px] flex-col rounded-xl border border-parchment-300 bg-white p-3 shadow-theme-lg dark:border-slate-warm-800 dark:bg-slate-warm-900 z-50"
        style="display: none;"
    >
        <!-- User Info Header -->
        <div class="px-2 py-1.5 border-b border-parchment-200 dark:border-slate-warm-800 pb-2.5 mb-1">
            <span class="block font-serif font-bold text-sm text-ink-900 dark:text-parchment-100">Drs. H. Aris Budiman, M.B.A.</span>
            <span class="block text-[11px] font-mono text-bronze-700 dark:text-bronze-400 mt-0.5">Direktur Utama</span>
            <span class="block text-[10px] font-mono text-slate-warm-400 truncate">aris.budiman@ncm-media.co.id</span>
        </div>

        <!-- Menu Items -->
        <ul class="flex flex-col gap-1 py-1 border-b border-parchment-200 dark:border-slate-warm-800">
            <li>
                <a href="/profile" class="flex items-center gap-2.5 px-2.5 py-2 font-medium text-xs text-ink-800 rounded-lg hover:bg-parchment-100 dark:text-parchment-200 dark:hover:bg-slate-warm-800 transition-colors">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 3.5l3 3L8 19l-4 1 1-4L17.5 3.5z"/><path d="M3 21h18"/></svg>
                    Studio Tanda Tangan & e-Sign
                </a>
            </li>
            <li>
                <a href="/form-elements" class="flex items-center gap-2.5 px-2.5 py-2 font-medium text-xs text-ink-800 rounded-lg hover:bg-parchment-100 dark:text-parchment-200 dark:hover:bg-slate-warm-800 transition-colors">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="4" rx="1.5"/><rect x="14" y="10" width="7" height="11" rx="1.5"/><rect x="3" y="13" width="7" height="8" rx="1.5"/></svg>
                    Galeri Template Dokumen
                </a>
            </li>
            <li>
                <a href="/blank" class="flex items-center gap-2.5 px-2.5 py-2 font-medium text-xs text-ink-800 rounded-lg hover:bg-parchment-100 dark:text-parchment-200 dark:hover:bg-slate-warm-800 transition-colors">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Pengaturan Workspace
                </a>
            </li>
        </ul>

        <!-- Sign Out Action -->
        <a
            href="/logout"
            class="flex items-center w-full gap-2.5 px-2.5 py-2 mt-1 font-medium text-xs text-seal-700 hover:bg-seal-50 rounded-lg transition-colors dark:text-seal-400 dark:hover:bg-slate-warm-800"
            @click="closeDropdown()"
        >
            <svg class="w-4 h-4 text-seal-700 dark:text-seal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span>Keluar Sesi (Sign Out)</span>
        </a>
    </div>
</div>
