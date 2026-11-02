/**
 * Script Interaktif Halaman Cari Dosen Pembimbing Qubu (public/js/cari-dosen.js)
 * Mencakup (DP-2.3):
 * - Dropdown Profil Avatar di Top Navbar
 * - Filter Interaktif Kombinasi (6 Pill Bidang + Search Input Debounce 300ms)
 * - Sinkronisasi URL (history.replaceState) & Fetch Async (?page=api-dosen) dengan AbortController
 * - Indikator Loading State (#dosen-loading-state)
 * - Empty State Kontekstual (#dosen-empty-state) & Fungsi Reset Filter (resetDosenFilters)
 * Catatan: Modal pengajuan bimbingan (openAjukanBimbinganModal) dikerjakan pada DP-3.1.
 */

(function () {
    'use strict';

    let activeAbortController = null;
    let loadingHideTimer = null;

    /**
     * Utilitas Debounce untuk menunda eksekusi saat pengguna mengetik
     */
    function debounce(fn, delay) {
        let timer = null;
        return function () {
            const context = this;
            const args = arguments;
            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(function () {
                fn.apply(context, args);
            }, delay);
        };
    }

    window.debounceDosenSearch = debounce;

    /**
     * Placeholder aman hingga modal pengajuan bimbingan diimplementasikan (DP-3.1)
     */
    if (typeof window.openAjukanBimbinganModal !== 'function') {
        window.openAjukanBimbinganModal = function () {};
    }

    /**
     * Atur tampilan indikator Loading State (#dosen-loading-state)
     */
    window.setDosenLoadingState = function (isLoading) {
        const loadingEl = document.getElementById('dosen-loading-state');
        const gridEl    = document.getElementById('dosen-grid');
        if (!loadingEl) return;

        if (loadingHideTimer) {
            clearTimeout(loadingHideTimer);
            loadingHideTimer = null;
        }

        if (isLoading) {
            loadingEl.style.display = 'flex';
            loadingEl.setAttribute('aria-busy', 'true');
            if (gridEl) {
                gridEl.style.opacity = '0.55';
            }
        } else {
            loadingEl.style.display = 'none';
            loadingEl.setAttribute('aria-busy', 'false');
            if (gridEl) {
                gridEl.style.opacity = '1';
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Dropdown Profil Avatar di Navbar
        const avatarBtn  = document.getElementById('dash-avatar-btn');
        const avatarMenu = document.getElementById('dash-dropdown-menu');

        if (avatarBtn && avatarMenu) {
            avatarBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = avatarMenu.classList.toggle('is-open');
                avatarBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            document.addEventListener('click', function (e) {
                if (!avatarMenu.contains(e.target) && e.target !== avatarBtn) {
                    avatarMenu.classList.remove('is-open');
                    avatarBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // 2. Elemen filter
        const mainContainer   = document.getElementById('dosen-main-container') || document.querySelector('.dosen-main-container');
        const pillsContainer  = document.getElementById('dosen-pills-container');
        const pillButtons     = pillsContainer ? Array.prototype.slice.call(pillsContainer.querySelectorAll('.dosen-pill-btn')) : [];
        const searchInput     = document.getElementById('dosen-search-input');
        const dosenGrid       = document.getElementById('dosen-grid');
        const cardItems       = dosenGrid ? Array.prototype.slice.call(dosenGrid.querySelectorAll('.dosen-card-item')) : [];
        const countBadge      = document.getElementById('dosen-count-badge');
        const activeFilterBar = document.getElementById('active-dosen-filter-bar');
        const activeFilterLbl = document.getElementById('active-dosen-filter-label');
        const emptyState      = document.getElementById('dosen-empty-state');
        const emptyMsg        = document.getElementById('empty-dosen-msg');
        const btnClearFilter  = document.getElementById('btn-clear-dosen-filter');
        const btnResetEmpty   = document.getElementById('btn-reset-empty-dosen');

        if (!dosenGrid) return;

        let activeBidang = 'Semua Bidang';
        pillButtons.forEach(function (btn) {
            if (btn.classList.contains('is-active') && btn.dataset.bidang) {
                activeBidang = btn.dataset.bidang;
            }
        });

        /**
         * Perbarui teks info filter aktif secara aman (tanpa innerHTML)
         */
        function renderSafeActiveFilterLabel(bidang, keyword) {
            if (!activeFilterLbl) return;
            while (activeFilterLbl.firstChild) {
                activeFilterLbl.removeChild(activeFilterLbl.firstChild);
            }

            activeFilterLbl.appendChild(document.createTextNode('Filter Aktif: '));
            const strongBidang = document.createElement('strong');
            strongBidang.textContent = bidang;
            activeFilterLbl.appendChild(strongBidang);

            if (keyword !== '') {
                activeFilterLbl.appendChild(document.createTextNode(' \u2022 Kata kunci: "' + keyword + '"'));
            }
        }

        /**
         * Perbarui pesan kontekstual pada Empty State (#empty-dosen-msg)
         */
        function updateContextualEmptyMessage(bidang, keyword) {
            if (!emptyMsg) return;
            const parts = [];
            if (bidang !== 'Semua Bidang') {
                parts.push('bidang keahlian "' + bidang + '"');
            }
            if (keyword !== '') {
                parts.push('kata kunci "' + keyword + '"');
            }

            if (parts.length > 0) {
                emptyMsg.textContent = 'Tidak ditemukan dosen pembimbing untuk ' + parts.join(' dan ') + '. Coba pilih bidang lain atau reset filter.';
            } else {
                emptyMsg.textContent = 'Belum ada dosen pembimbing yang cocok dengan pencarian Anda.';
            }
        }

        /**
         * Sinkronisasi parameter filter ke URL browser tanpa reload halaman
         */
        function syncBrowserUrl(bidang, keyword) {
            if (!window.history || typeof window.history.replaceState !== 'function') return;
            try {
                const urlObj = new URL(window.location.href);
                urlObj.searchParams.set('page', 'cari-dosen');

                if (bidang && bidang !== 'Semua Bidang') {
                    urlObj.searchParams.set('bidang', bidang);
                } else {
                    urlObj.searchParams.delete('bidang');
                }

                if (keyword !== '') {
                    urlObj.searchParams.set('q', keyword);
                } else {
                    urlObj.searchParams.delete('q');
                }

                const idLomba = mainContainer && mainContainer.dataset.idLomba ? mainContainer.dataset.idLomba.trim() : '';
                if (idLomba !== '') {
                    urlObj.searchParams.set('id_lomba', idLomba);
                }

                window.history.replaceState({ bidang: bidang, q: keyword }, '', urlObj.toString());
            } catch (_) {
                // Abaikan jika lingkungan tidak memiliki URL penuh
            }
        }

        /**
         * Sinkronisasi async ke endpoint JSON API (?page=api-dosen) dengan AbortController & Loading State
         */
        function syncDosenWithApi(bidang, keyword) {
            if (typeof window.fetch !== 'function' || !mainContainer) return;
            const baseApiUrl = mainContainer.dataset.apiUrl || '?page=api-dosen';

            if (activeAbortController) {
                activeAbortController.abort();
            }
            if (typeof AbortController !== 'undefined') {
                activeAbortController = new AbortController();
            }

            window.setDosenLoadingState(true);

            const params = new URLSearchParams();
            if (bidang && bidang !== 'Semua Bidang') {
                params.set('bidang', bidang);
            }
            if (keyword !== '') {
                params.set('q', keyword);
            }
            if (mainContainer.dataset.idLomba) {
                params.set('id_lomba', mainContainer.dataset.idLomba);
            }

            const sep = baseApiUrl.indexOf('?') !== -1 ? '&' : '?';
            const requestUrl = baseApiUrl + sep + params.toString();

            const fetchOptions = {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
            };
            if (activeAbortController) {
                fetchOptions.signal = activeAbortController.signal;
            }

            window.fetch(requestUrl, fetchOptions)
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (payload) {
                    window.setDosenLoadingState(false);
                    if (!payload || !payload.success || !Array.isArray(payload.data)) return;

                    const matchedIdSet = {};
                    payload.data.forEach(function (item) {
                        matchedIdSet[String(item.id_dosen)] = true;
                    });

                    let count = 0;
                    cardItems.forEach(function (card) {
                        const cardId = String(card.dataset.id || '');
                        if (matchedIdSet[cardId]) {
                            card.style.display = '';
                            count++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (countBadge) {
                        countBadge.textContent = 'Menampilkan ' + count + ' Dosen';
                    }
                    updateContextualEmptyMessage(bidang, keyword);
                    if (emptyState) {
                        emptyState.style.display = count === 0 ? 'flex' : 'none';
                    }
                })
                .catch(function (err) {
                    if (err && err.name === 'AbortError') return;
                    window.setDosenLoadingState(false);
                });
        }

        /**
         * Penyaringan kartu dosen berdasarkan kombinasi Bidang + Kata Kunci (nama / NIDN / bidang)
         */
        function filterDosenCards(options) {
            const opts       = options || {};
            const rawKeyword = searchInput ? searchInput.value.trim() : '';
            const cleanQuery = rawKeyword.toLowerCase();
            const targetBidangLower = activeBidang.toLowerCase();

            if (opts.showLoading !== false) {
                window.setDosenLoadingState(true);
                loadingHideTimer = setTimeout(function () {
                    window.setDosenLoadingState(false);
                }, 140);
            }

            let visibleCount = 0;

            cardItems.forEach(function (card) {
                const cardName   = (card.dataset.name || '').toLowerCase();
                const cardNidn   = (card.dataset.nidn || '').toLowerCase();
                const cardBidang = (card.dataset.bidang || '').toLowerCase();

                let matchBidang = (activeBidang === 'Semua Bidang');
                if (!matchBidang) {
                    const tags = cardBidang.split(',').map(function (t) { return t.trim(); });
                    matchBidang = tags.indexOf(targetBidangLower) !== -1;
                }

                const matchKeyword = (cleanQuery === '') ||
                    cardName.indexOf(cleanQuery) !== -1 ||
                    cardNidn.indexOf(cleanQuery) !== -1 ||
                    cardBidang.indexOf(cleanQuery) !== -1;

                if (matchBidang && matchKeyword) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (countBadge) {
                countBadge.textContent = 'Menampilkan ' + visibleCount + ' Dosen';
            }

            const hasFilter = (activeBidang !== 'Semua Bidang') || (rawKeyword !== '');
            if (activeFilterBar) {
                activeFilterBar.style.display = hasFilter ? 'flex' : 'none';
            }
            renderSafeActiveFilterLabel(activeBidang, rawKeyword);

            updateContextualEmptyMessage(activeBidang, rawKeyword);
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'flex' : 'none';
            }

            syncBrowserUrl(activeBidang, rawKeyword);

            if (opts.syncApi) {
                syncDosenWithApi(activeBidang, rawKeyword);
            }

            return visibleCount;
        }

        window.filterDosenCards = filterDosenCards;

        /**
         * Pilih Pill Bidang tertentu & perbarui atribut WAI-ARIA
         */
        function selectBidangPill(targetButton, focusButton) {
            if (!targetButton) return;
            activeBidang = targetButton.dataset.bidang || 'Semua Bidang';

            pillButtons.forEach(function (btn) {
                const isSelected = (btn === targetButton);
                btn.classList.toggle('is-active', isSelected);
                btn.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                btn.setAttribute('tabindex', isSelected ? '0' : '-1');
            });

            if (focusButton && typeof targetButton.focus === 'function') {
                targetButton.focus();
            }

            filterDosenCards({ showLoading: true });
        }

        /**
         * Reset seluruh filter ke kondisi awal ("Semua Bidang" & keyword kosong)
         */
        function resetDosenFilters() {
            activeBidang = 'Semua Bidang';

            if (searchInput) {
                searchInput.value = '';
            }

            pillButtons.forEach(function (btn, idx) {
                const isDefault = (btn.dataset.bidang === 'Semua Bidang') || idx === 0;
                btn.classList.toggle('is-active', isDefault);
                btn.setAttribute('aria-selected', isDefault ? 'true' : 'false');
                btn.setAttribute('tabindex', isDefault ? '0' : '-1');
            });

            filterDosenCards({ showLoading: false });
        }

        window.resetDosenFilters = resetDosenFilters;

        // Event listener klik & keyboard WAI-ARIA pada Pill Bidang
        pillButtons.forEach(function (btn, index) {
            btn.addEventListener('click', function () {
                selectBidangPill(btn, false);
            });

            btn.addEventListener('keydown', function (e) {
                const total = pillButtons.length;
                if (total === 0) return;

                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    selectBidangPill(pillButtons[(index + 1) % total], true);
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    selectBidangPill(pillButtons[(index - 1 + total) % total], true);
                } else if (e.key === 'Home') {
                    e.preventDefault();
                    selectBidangPill(pillButtons[0], true);
                } else if (e.key === 'End') {
                    e.preventDefault();
                    selectBidangPill(pillButtons[total - 1], true);
                } else if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectBidangPill(btn, false);
                }
            });
        });

        // Input pencarian dengan debounce 300ms
        if (searchInput) {
            const debouncedSearch = debounce(function () {
                filterDosenCards({ showLoading: true });
            }, 300);

            searchInput.addEventListener('input', debouncedSearch);

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterDosenCards({ showLoading: true });
                } else if (e.key === 'Escape' && searchInput.value !== '') {
                    e.preventDefault();
                    searchInput.value = '';
                    filterDosenCards({ showLoading: false });
                }
            });
        }

        if (btnClearFilter) {
            btnClearFilter.addEventListener('click', resetDosenFilters);
        }
        if (btnResetEmpty) {
            btnResetEmpty.addEventListener('click', resetDosenFilters);
        }
    });
})();

