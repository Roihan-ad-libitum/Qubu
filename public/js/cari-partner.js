/**
 * Script Interaktif Halaman Cari Partner Qubu (public/js/cari-partner.js)
 * Mencakup:
 * - CP-3.4: Modal Profil Lengkap Kandidat Partner Read-Only & Toast Placeholder ("Ajak ke Tim" / "+ Posting Cari Tim")
 * - CP-4.1: Interaktivitas Filter Kombinasi (6 Pill Bidang + Dropdown Program Studi + Search Input Debounce 300ms),
 *           Sinkronisasi URL (history.replaceState), Fetch Async (?page=api-partners) dengan AbortController,
 *           dan Indikator Loading State (#partner-loading-state)
 * - CP-4.2: Empty State Kontekstual (#partner-empty-state) & Fungsi Reset Filter (resetPartnerFilters)
 */

(function () {
    'use strict';

    let toastTimer = null;
    let activeAbortController = null;
    let loadingHideTimer = null;

    /**
     * Utilitas Debounce untuk menunda eksekusi fungsi saat pengguna mengetik di kolom pencarian
     * @param {Function} fn
     * @param {number} delay
     * @returns {Function}
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

    window.debouncePartnerSearch = debounce;

    /**
     * Tampilkan toast pemberitahuan untuk tombol placeholder ("Ajak ke Tim" / "+ Posting Cari Tim")
     */
    window.showPartnerPlaceholderNotice = function (message) {
        const toastEl   = document.getElementById('partner-toast');
        const toastText = document.getElementById('partner-toast-text');
        if (!toastEl) return;

        if (toastText && message) {
            toastText.textContent = message;
        }
        toastEl.style.display = 'inline-flex';

        if (toastTimer) {
            clearTimeout(toastTimer);
        }
        toastTimer = setTimeout(function () {
            toastEl.style.display = 'none';
        }, 3200);
    };

    /**
     * Buka Modal Profil Lengkap Read-Only untuk Kandidat Partner
     */
    window.openPartnerProfileModal = function (mhsData) {
        const modal = document.getElementById('partner-profile-modal');
        if (!modal || !mhsData) return;

        const initialsEl = document.getElementById('pmodal-avatar-initials');
        const imgEl      = document.getElementById('pmodal-avatar-img');
        const nameEl     = document.getElementById('pmodal-name');
        const subEl      = document.getElementById('pmodal-sub');
        const npmEl      = document.getElementById('pmodal-npm');
        const prodiEl    = document.getElementById('pmodal-prodi');
        const angkatanEl = document.getElementById('pmodal-angkatan');
        const skillsEl   = document.getElementById('pmodal-skills');
        const bioEl      = document.getElementById('pmodal-bio');
        const fullLinkEl = document.getElementById('pmodal-full-link');

        const nama     = mhsData.nama_mahasiswa || 'Mahasiswa Qubu';
        const prodi    = mhsData.program_studi || 'Informatika';
        const angkatan = mhsData.angkatan || '2025';
        const npm      = mhsData.npm || '-';
        const inisial  = mhsData.inisial || nama.substring(0, 2).toUpperCase();
        const fotoUrl  = mhsData.foto_url || '';

        if (nameEl) nameEl.textContent = nama;
        if (subEl) subEl.textContent = prodi + ' \u2022 Angkatan ' + angkatan;
        if (npmEl) npmEl.textContent = npm;
        if (prodiEl) prodiEl.textContent = prodi;
        if (angkatanEl) angkatanEl.textContent = String(angkatan);

        if (fotoUrl && imgEl && initialsEl) {
            imgEl.src = fotoUrl;
            imgEl.alt = nama;
            imgEl.style.display = 'block';
            initialsEl.style.display = 'none';
        } else if (initialsEl && imgEl) {
            initialsEl.textContent = inisial;
            initialsEl.style.display = 'inline';
            imgEl.style.display = 'none';
        }

        if (skillsEl) {
            while (skillsEl.firstChild) {
                skillsEl.removeChild(skillsEl.firstChild);
            }
            const tags = Array.isArray(mhsData.bidang_tags) && mhsData.bidang_tags.length > 0
                ? mhsData.bidang_tags
                : String(mhsData.bidang_minat || 'Frontend').split(',').map(function (s) { return s.trim(); }).filter(Boolean);

            tags.forEach(function (tag) {
                const span = document.createElement('span');
                span.className = 'pcard-skill-pill';
                span.textContent = tag;
                skillsEl.appendChild(span);
            });
        }

        if (bioEl) {
            const cleanBio = (mhsData.bio || '').trim();
            bioEl.textContent = cleanBio !== ''
                ? cleanBio
                : 'Mahasiswa Fasilkom yang siap berkolaborasi dalam kompetisi tingkat nasional dan pengembangan proyek tim lintas prodi.';
        }

        if (fullLinkEl) {
            fullLinkEl.href = mhsData.profil_url || ('?page=lihat-profil&id=' + encodeURIComponent(mhsData.id_mahasiswa || 0));
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    /**
     * Tutup Modal Profil Lengkap Read-Only
     */
    window.closePartnerProfileModal = function () {
        const modal = document.getElementById('partner-profile-modal');
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    /**
     * Atur tampilan indikator Loading State (#partner-loading-state)
     * @param {boolean} isLoading
     */
    window.setPartnerLoadingState = function (isLoading) {
        const loadingEl = document.getElementById('partner-loading-state');
        const gridEl    = document.getElementById('partner-grid');
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

        // 2. Tutup Modal saat klik backdrop atau tekan Escape
        const profileModal = document.getElementById('partner-profile-modal');
        if (profileModal) {
            profileModal.addEventListener('click', function (e) {
                if (e.target === profileModal) {
                    window.closePartnerProfileModal();
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.closePartnerProfileModal();
            }
        });

        // =====================================================================
        // 3. FILTER INTERAKTIF KOMBINASI (BIDANG + PRODI + KEYWORD) & LOADING
        // =====================================================================
        const mainContainer   = document.getElementById('partner-main-container') || document.querySelector('.partner-main-container');
        const pillsContainer  = document.getElementById('partner-pills-container');
        const pillButtons     = pillsContainer ? Array.prototype.slice.call(pillsContainer.querySelectorAll('.partner-pill-btn')) : [];
        const searchInput     = document.getElementById('partner-search-input');
        const prodiSelect     = document.getElementById('partner-prodi-select');
        const partnerGrid     = document.getElementById('partner-grid');
        const cardItems       = partnerGrid ? Array.prototype.slice.call(partnerGrid.querySelectorAll('.partner-card-item')) : [];
        const countBadge      = document.getElementById('partner-count-badge');
        const activeFilterBar = document.getElementById('active-partner-filter-bar');
        const activeFilterLbl = document.getElementById('active-partner-filter-label');
        const emptyState      = document.getElementById('partner-empty-state');
        const emptyMsg        = document.getElementById('empty-partner-msg');
        const btnClearFilter  = document.getElementById('btn-clear-partner-filter');
        const btnResetEmpty   = document.getElementById('btn-reset-empty-partner');

        if (!partnerGrid) return;

        // Inisialisasi state awal dari elemen DOM yang telah dirender server (SSR)
        let activeBidang = 'Semua Bidang';
        pillButtons.forEach(function (btn) {
            if (btn.classList.contains('is-active') && btn.dataset.bidang) {
                activeBidang = btn.dataset.bidang;
            }
        });

        let activeProdi = prodiSelect && prodiSelect.value ? prodiSelect.value : 'Semua Prodi';

        /**
         * Perbarui teks info filter aktif secara aman (bebas XSS, tanpa innerHTML dari input user)
         */
        function renderSafeActiveFilterLabel(bidang, prodi, keyword) {
            if (!activeFilterLbl) return;
            while (activeFilterLbl.firstChild) {
                activeFilterLbl.removeChild(activeFilterLbl.firstChild);
            }

            activeFilterLbl.appendChild(document.createTextNode('Filter Aktif: '));
            const strongBidang = document.createElement('strong');
            strongBidang.textContent = bidang;
            activeFilterLbl.appendChild(strongBidang);

            activeFilterLbl.appendChild(document.createTextNode(' \u2022 Prodi: '));
            const strongProdi = document.createElement('strong');
            strongProdi.textContent = prodi;
            activeFilterLbl.appendChild(strongProdi);

            if (keyword !== '') {
                activeFilterLbl.appendChild(document.createTextNode(' \u2022 Kata kunci: "' + keyword + '"'));
            }
        }

        /**
         * Perbarui pesan kontekstual pada Empty State (#empty-partner-msg)
         */
        function updateContextualEmptyMessage(bidang, prodi, keyword) {
            if (!emptyMsg) return;
            const parts = [];
            if (bidang !== 'Semua Bidang') {
                parts.push('bidang "' + bidang + '"');
            }
            if (prodi !== 'Semua Prodi') {
                parts.push('Program Studi "' + prodi + '"');
            }
            if (keyword !== '') {
                parts.push('kata kunci "' + keyword + '"');
            }

            if (parts.length > 0) {
                emptyMsg.textContent = 'Tidak ditemukan kandidat mahasiswa untuk kombinasi ' + parts.join(', ') + '. Coba sesuaikan atau reset filter Anda.';
            } else {
                emptyMsg.textContent = 'Coba gunakan kata kunci lain, pilih bidang minat berbeda, atau ubah filter Program Studi.';
            }
        }

        /**
         * Sinkronisasi parameter pencarian & filter ke URL browser tanpa reload halaman
         */
        function syncBrowserUrl(bidang, prodi, keyword) {
            if (!window.history || typeof window.history.replaceState !== 'function') return;
            try {
                const urlObj = new URL(window.location.href);
                urlObj.searchParams.set('page', 'cari-partner');

                if (bidang && bidang !== 'Semua Bidang') {
                    urlObj.searchParams.set('bidang', bidang);
                } else {
                    urlObj.searchParams.delete('bidang');
                }

                if (prodi && prodi !== 'Semua Prodi') {
                    urlObj.searchParams.set('prodi', prodi);
                } else {
                    urlObj.searchParams.delete('prodi');
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

                window.history.replaceState({ bidang: bidang, prodi: prodi, q: keyword }, '', urlObj.toString());
            } catch (_) {
                // Abaikan jika lingkungan sandbox tidak memiliki URL penuh
            }
        }

        /**
         * Sinkronisasi async ke endpoint JSON API (?page=api-partners) dengan AbortController & Loading State
         */
        function syncPartnersWithApi(bidang, prodi, keyword) {
            if (typeof window.fetch !== 'function' || !mainContainer) return;
            const baseApiUrl = mainContainer.dataset.apiUrl || '?page=api-partners';

            if (activeAbortController) {
                activeAbortController.abort();
            }
            if (typeof AbortController !== 'undefined') {
                activeAbortController = new AbortController();
            }

            window.setPartnerLoadingState(true);

            const params = new URLSearchParams();
            params.set('page', 'api-partners');
            if (bidang && bidang !== 'Semua Bidang') {
                params.set('bidang', bidang);
            }
            if (prodi && prodi !== 'Semua Prodi') {
                params.set('prodi', prodi);
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
                    window.setPartnerLoadingState(false);
                    if (!payload || !payload.success || !Array.isArray(payload.data)) return;

                    const matchedIdSet = {};
                    payload.data.forEach(function (item) {
                        matchedIdSet[String(item.id_mahasiswa)] = true;
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
                        countBadge.textContent = 'Menampilkan ' + count + ' Rekan';
                    }
                    updateContextualEmptyMessage(bidang, prodi, keyword);
                    if (emptyState) {
                        emptyState.style.display = count === 0 ? 'flex' : 'none';
                    }
                })
                .catch(function (err) {
                    if (err && err.name === 'AbortError') return;
                    window.setPartnerLoadingState(false);
                });
        }

        /**
         * Fungsi utama penyaringan kartu kandidat partner berdasarkan kombinasi Bidang + Prodi + Keyword
         * @param {Object} [options]
         */
        function filterPartnerCards(options) {
            const opts       = options || {};
            const rawKeyword = searchInput ? searchInput.value.trim() : '';
            const cleanQuery = rawKeyword.toLowerCase();
            activeProdi      = prodiSelect && prodiSelect.value ? prodiSelect.value : 'Semua Prodi';

            const targetBidangLower = activeBidang.toLowerCase();
            const targetProdiLower  = activeProdi.toLowerCase();

            // Tampilkan transisi indikator loading singkat saat melakukan pemfilteran interaktif
            if (opts.showLoading !== false) {
                window.setPartnerLoadingState(true);
                loadingHideTimer = setTimeout(function () {
                    window.setPartnerLoadingState(false);
                }, 140);
            }

            let visibleCount = 0;

            cardItems.forEach(function (card) {
                const cardName   = (card.dataset.name || '').toLowerCase();
                const cardNpm    = (card.dataset.npm || '').toLowerCase();
                const cardProdi  = (card.dataset.prodi || '').toLowerCase();
                const cardBidang = (card.dataset.bidang || '').toLowerCase();
                const cardBio    = (card.dataset.bio || '').toLowerCase();

                // 1. Cek kecocokan Bidang (mendukung multi-tag dipisahkan koma)
                let matchBidang = (activeBidang === 'Semua Bidang');
                if (!matchBidang) {
                    const tags = cardBidang.split(',').map(function (t) { return t.trim(); });
                    matchBidang = tags.indexOf(targetBidangLower) !== -1 || cardBidang.indexOf(targetBidangLower) !== -1;
                }

                // 2. Cek kecocokan Program Studi
                const matchProdi = (activeProdi === 'Semua Prodi') || (cardProdi === targetProdiLower);

                // 3. Cek kecocokan Kata Kunci (Nama, NPM, Prodi, Bidang, atau Bio)
                const matchKeyword = (cleanQuery === '') ||
                    cardName.indexOf(cleanQuery) !== -1 ||
                    cardNpm.indexOf(cleanQuery) !== -1 ||
                    cardProdi.indexOf(cleanQuery) !== -1 ||
                    cardBidang.indexOf(cleanQuery) !== -1 ||
                    cardBio.indexOf(cleanQuery) !== -1;

                if (matchBidang && matchProdi && matchKeyword) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Update counter jumlah rekan
            if (countBadge) {
                countBadge.textContent = 'Menampilkan ' + visibleCount + ' Rekan';
            }

            // Update bar status filter aktif
            const hasFilter = (activeBidang !== 'Semua Bidang') || (activeProdi !== 'Semua Prodi') || (rawKeyword !== '');
            if (activeFilterBar) {
                activeFilterBar.style.display = hasFilter ? 'flex' : 'none';
            }
            renderSafeActiveFilterLabel(activeBidang, activeProdi, rawKeyword);

            // Update Empty State
            updateContextualEmptyMessage(activeBidang, activeProdi, rawKeyword);
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'flex' : 'none';
            }

            // Sinkronisasi URL Browser
            syncBrowserUrl(activeBidang, activeProdi, rawKeyword);

            // Sinkronisasi ke API jika dipicu interaksi user dan fetch tersedia
            if (opts.syncApi) {
                syncPartnersWithApi(activeBidang, activeProdi, rawKeyword);
            }

            return visibleCount;
        }

        window.filterPartnerCards = filterPartnerCards;

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

            filterPartnerCards({ showLoading: true });
        }

        /**
         * Reset seluruh filter Cari Partner ke kondisi awal ("Semua Bidang", "Semua Prodi", keyword kosong)
         */
        function resetPartnerFilters() {
            activeBidang = 'Semua Bidang';
            activeProdi  = 'Semua Prodi';

            if (searchInput) {
                searchInput.value = '';
            }
            if (prodiSelect) {
                prodiSelect.value = 'Semua Prodi';
            }

            pillButtons.forEach(function (btn, idx) {
                const isDefault = (btn.dataset.bidang === 'Semua Bidang') || idx === 0;
                btn.classList.toggle('is-active', isDefault);
                btn.setAttribute('aria-selected', isDefault ? 'true' : 'false');
                btn.setAttribute('tabindex', isDefault ? '0' : '-1');
            });

            filterPartnerCards({ showLoading: false });
        }

        window.resetPartnerFilters = resetPartnerFilters;

        // Pasang event listener klik & keyboard WAI-ARIA pada 6 Pill Bidang
        pillButtons.forEach(function (btn, index) {
            btn.addEventListener('click', function () {
                selectBidangPill(btn, false);
            });

            btn.addEventListener('keydown', function (e) {
                const total = pillButtons.length;
                if (total === 0) return;

                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    const nextBtn = pillButtons[(index + 1) % total];
                    selectBidangPill(nextBtn, true);
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    const prevBtn = pillButtons[(index - 1 + total) % total];
                    selectBidangPill(prevBtn, true);
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

        // Pasang event listener perubahan Dropdown Program Studi
        if (prodiSelect) {
            prodiSelect.addEventListener('change', function () {
                activeProdi = prodiSelect.value || 'Semua Prodi';
                filterPartnerCards({ showLoading: true });
            });
        }

        // Pasang event listener input pencarian dengan debounce 300ms
        if (searchInput) {
            const debouncedSearch = debounce(function () {
                filterPartnerCards({ showLoading: true });
            }, 300);

            searchInput.addEventListener('input', debouncedSearch);

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterPartnerCards({ showLoading: true });
                } else if (e.key === 'Escape' && searchInput.value !== '') {
                    e.preventDefault();
                    searchInput.value = '';
                    filterPartnerCards({ showLoading: false });
                }
            });
        }

        // Pasang event listener tombol Reset Filter
        if (btnClearFilter) {
            btnClearFilter.addEventListener('click', resetPartnerFilters);
        }
        if (btnResetEmpty) {
            btnResetEmpty.addEventListener('click', resetPartnerFilters);
        }
    });
})();
