        // Pra-cek: struktur tabel yang TIDAK didukung (tabel di dalam sel)
        // atau HTML ZONA ber-tabel (header/footer dokumen kontrak) membentuk
        // blot campuran -> optimize() melempar "formats is not a function".
        // Zona memang non-editable (cermin): simpan HTML apa adanya, pakai
        // paginasi DOM.
        {
            const __role = regionEl.dataset ? regionEl.dataset.region : null;
            const __nestedTable = /<(?:td|th)\b[^>]*>[\s\S]*?<table/i.test(existingHtml);
            const __zoneWithTable = (__role === 'header' || __role === 'footer')
                && /<table/i.test(existingHtml);
            if (existingHtml.trim() && (__nestedTable || __zoneWithTable)) {
                try { q.disable && q.disable(); } catch (err) { /* noop */ }
                try { q.off && q.off('text-change'); } catch (err) { /* noop */ }
                try { q.off && q.off('selection-change'); } catch (err) { /* noop */ }
                quillsByRegion.delete(regionEl);
                regionEl.dataset.quillReady = '';
                regionEl.innerHTML = existingHtml;
                regionEl.setAttribute('contenteditable', 'true');
                regionEl.classList.add('ql-editor');
                if (__role === 'body') {
                    regionEl.dataset.domFlow = '1';
                    bindDomPageOverflowWatch(regionEl);
                } else {
                    console.info('[DocQuill] Zona ber-tabel disimpan sebagai HTML asli (mode cermin).');
                }
                return null;
            }
        }

