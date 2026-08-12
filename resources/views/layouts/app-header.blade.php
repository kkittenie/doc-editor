<header
    class="sticky top-0 flex w-full bg-parchment-50/90 border-parchment-200 backdrop-blur-md z-9999 border-b dark:border-slate-warm-800 dark:bg-slate-warm-900/90"
    x-data="{
        isApplicationMenuOpen: false,
        docTitle: 'Surat Keputusan Direksi No. 042/SK-DIR/VIII/2026',
        isEditingTitle: false,
        savedTime: 'Tersimpan (Baru Saja)',
        toggleApplicationMenu() {
            this.isApplicationMenuOpen = !this.isApplicationMenuOpen;
        }
    }">
    <div class="flex flex-col items-center justify-between grow xl:flex-row xl:px-6">
        <div
            class="flex items-center justify-between w-full gap-3 px-4 py-3 border-b border-parchment-200 dark:border-slate-warm-800 xl:justify-normal xl:border-b-0 xl:px-0">

            <!-- Desktop Sidebar Toggle Button -->
            <button
                class="hidden xl:flex items-center justify-center w-10 h-10 text-slate-warm-500 border border-parchment-300 rounded-lg dark:border-slate-warm-700 dark:text-parchment-400 hover:bg-parchment-100 transition-colors"
                :class="{ 'bg-parchment-200 dark:bg-slate-warm-800': !$store.sidebar.isExpanded }"
                @click="$store.sidebar.toggleExpanded()" aria-label="Toggle Sidebar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6H20M4 12H14M4 18H18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>

            <!-- Mobile Sidebar Toggle Button -->
            <button
                class="flex xl:hidden items-center justify-center w-10 h-10 text-slate-warm-600 rounded-lg dark:text-parchment-400"
                @click="$store.sidebar.toggleMobileOpen()" aria-label="Toggle Mobile Menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Mobile Brand Logo -->
            <a href="/" class="xl:hidden flex items-center gap-2">
                <span class="font-serif font-bold text-ink-900 dark:text-parchment-100">Papercraft</span>
            </a>

            <!-- Active Document Title Bar (centerpiece of the document editor header) -->
            <div class="hidden sm:flex items-center gap-3 ml-2 lg:ml-4 grow max-w-xl">
                <div
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-parchment-300 bg-white shadow-theme-xs dark:bg-slate-warm-800 dark:border-slate-warm-700 grow">
                    <svg class="text-bronze-600 dark:text-bronze-400 shrink-0" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                    <input type="text" x-model="docTitle"
                        class="text-xs font-semibold text-ink-900 bg-transparent border-none focus:outline-none dark:text-parchment-100 truncate grow"
                        placeholder="Judul Dokumen..." />
                    <span class="text-[10px] font-mono text-slate-warm-400 shrink-0 hidden md:inline"
                        x-text="savedTime"></span>
                </div>
            </div>

            <!-- Quick Document Search Bar (desktop) -->
            <div class="hidden xl:block ml-auto">
                <form @submit.prevent>
                    <div class="relative">
                        <span
                            class="absolute -translate-y-1/2 pointer-events-none left-3.5 top-1/2 text-slate-warm-400">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                        </span>
                        <input type="text" placeholder="Cari arsip & template..."
                            class="h-9 w-48 rounded-lg border border-parchment-300 bg-white py-1.5 pl-10 pr-8 text-xs text-ink-900 shadow-theme-xs focus:border-bronze-500 focus:w-64 transition-all dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100 dark:focus:border-bronze-400" />
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side Header Actions -->
        <div class="flex items-center justify-between w-full gap-3 px-4 py-2.5 xl:flex xl:justify-end xl:px-0">
            <div class="flex items-center gap-2">
                <!-- Export / Print Quick Action Button -->
                <button @click="documentPrintArea()" class="btn-secondary text-xs px-3 py-1.5 h-9 shadow-xs">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <rect x="6" y="14" width="12" height="8" />
                    </svg>
                    <span class="hidden sm:inline">Cetak / PDF</span>
                </button>

                <!-- Save Document Action -->
                <!-- <button @click="savedTime = 'Tersimpan ' + new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'})" class="btn-primary text-xs px-3 py-1.5 h-9 shadow-xs">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    <span>Simpan</span>
                </button> -->

                <div class="h-5 w-px bg-parchment-300 dark:bg-slate-warm-700 mx-1"></div>

                <!-- Theme Toggle Button -->
                <button
                    class="flex items-center justify-center h-9 w-9 text-slate-warm-500 rounded-lg border border-parchment-300 bg-white hover:bg-parchment-100 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-400 dark:hover:bg-slate-warm-700 transition-colors"
                    @click="$store.theme.toggle()" title="Ganti Mode Terang/Gelap">
                    <!-- Sun icon -->
                    <svg class="hidden dark:block" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5" />
                        <line x1="12" y1="1" x2="12" y2="3" />
                        <line x1="12" y1="21" x2="12" y2="23" />
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                        <line x1="1" y1="12" x2="3" y2="12" />
                        <line x1="21" y1="12" x2="23" y2="12" />
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                    </svg>
                    <!-- Moon icon -->
                    <svg class="dark:hidden" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                    </svg>
                </button>

                <!-- User Profile Dropdown -->
                <x-header.user-dropdown />
            </div>
        </div>
    </div>
</header>
<script>
    function documentPrintArea() {
        const area = document.querySelector('.documentPrintArea');
        if (!area) {
            window.print();
            return;
        }

        const win = window.open('', '_blank');
        if (!win) {
            Swal.fire({ icon: 'error', title: 'Popup Diblokir', text: 'Mohon izinkan popup untuk situs ini agar bisa mencetak dokumen.', confirmButtonColor: '#1B2A4A' });
            return;
        }

        // Clone current styles (link and style tags)
        const headStyles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
            .map(node => node.outerHTML)
            .join('\n');

        const docHtml = `
            <!doctype html>
            <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width,initial-scale=1">
                ${headStyles}
                <style>
                    /* Ensure printed page uses A4 sizing and preserves colors */
                    @page { size: A4; margin: 20mm; }
                    html, body { background: white; color: black; }
                </style>
            </head>
            <body>
                ${area.innerHTML}
            </body>
            </html>
        `;

        win.document.open();
        win.document.write(docHtml);
        win.document.close();

        // Give the new window a moment to load styles, then print
        win.focus();
        setTimeout(() => {
            try {
                win.print();
                // close after printing
                win.close();
            } catch (e) {
                console.error('Print failed', e);
            }
        }, 500);
    }
</script>