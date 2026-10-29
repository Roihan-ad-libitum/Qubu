/**
 * Dashboard Interactivity & InfoLomba Live Search + Filter Module (public/js/dashboard.js)
 * Mencakup:
 * - Debounced Live Search (300ms) pada multi-atribut: nama_lomba, penyelenggara, deskripsi, bidang, tingkat, partisipasi
 * - Sinkronisasi kombinasi Filter Bidang (5 Bidang Qubu) + Search Keyword secara real-time
 * - Sinkronisasi opsional dengan Endpoint JSON API (?page=api-competitions) menggunakan AbortController
 * - Navigasi Keyboard WAI-ARIA untuk Filter Pills (role="tablist")
 * - Pengelolaan Modal Detail Perlombaan Pop-up (Countdown, Share Link, Escape key)
 */

let currentModalDetailUrl = '';
let activeFetchController = null;

/**
 * Utility Debounce: Menunda eksekusi fungsi hingga pengguna berhenti mengetik selama `delay` ms (default 300ms).
 *
 * @param {Function} fn    Fungsi yang akan dijalankan
 * @param {number}   delay Waktu tunda dalam milidetik (default 300)
 * @returns {Function}
 */
function debounce(fn, delay = 300) {
    let timerId = null;
    const debounced = function (...args) {
        if (timerId !== null) {
            clearTimeout(timerId);
        }
        timerId = setTimeout(() => {
            timerId = null;
            fn.apply(this, args);
        }, delay);
    };
    debounced.cancel = function () {
        if (timerId !== null) {
            clearTimeout(timerId);
            timerId = null;
        }
    };
    return debounced;
}

/**
 * Normalisasi string pencarian di sisi klien (trim, lowercase, spasi tunggal)
 */
function normalizeSearchText(input) {
    return String(input || '')
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
}

/**
 * Hitung sisa waktu menuju deadline pendaftaran
 */
function computeDeadlineCountdown(deadlineStr) {
    if (!deadlineStr) {
        return { text: 'Jadwal menyusul', isClosed: false };
    }
    const normalized = String(deadlineStr).replace(' ', 'T');
    const targetMs = new Date(normalized).getTime() || new Date(deadlineStr).getTime();
    if (!targetMs || isNaN(targetMs)) {
        return { text: 'Jadwal menyusul', isClosed: false };
    }

    const diffMs = targetMs - Date.now();
    if (diffMs <= 0) {
        return { text: 'Pendaftaran Ditutup', isClosed: true };
    }

    const totalHours = Math.floor(diffMs / (1000 * 60 * 60));
    const days = Math.floor(totalHours / 24);
    const hours = totalHours % 24;

    if (days > 0) {
        return { text: 'Sisa ' + days + ' Hari ' + hours + ' Jam', isClosed: false };
    }
    return { text: 'Sisa ' + Math.max(1, hours) + ' Jam Lagi!', isClosed: false };
}

/**
 * Buka Modal Detail Perlombaan Pop-up
 */
function openDetailModal(data) {
    const modal = document.getElementById('detail-modal-overlay');
    if (!modal || !data) return;

    const idLomba = data.id_lomba || '';
    const bidang = data.bidang || 'UI/UX Design';
    const tingkat = data.tingkat || 'Nasional';
    const partisipasiRaw = data.partisipasi || 'Individu/Tim';
    const partisipasiFormatted = data.partisipasi_formatted
        || (partisipasiRaw === 'Individu/Tim' ? 'Individu & Tim' : partisipasiRaw);

    document.getElementById('modal-title').textContent = data.nama_lomba || '-';
    document.getElementById('modal-cat').textContent = bidang;
    const tingkatBadge = document.getElementById('modal-tingkat-badge');
    if (tingkatBadge) tingkatBadge.textContent = tingkat;
    document.getElementById('modal-organizer').textContent = 'Diselenggarakan oleh: ' + (data.penyelenggara || '-');
    document.getElementById('modal-desc').textContent = data.deskripsi || 'Belum ada deskripsi rinci.';

    const countdownBadge = document.getElementById('modal-countdown-badge');
    if (countdownBadge) {
        const cd = computeDeadlineCountdown(data.deadline_daftar);
        countdownBadge.textContent = cd.text;
        countdownBadge.classList.toggle('is-closed', cd.isClosed);
    }

    const deadline = data.deadline_formatted || (data.deadline_daftar
        ? new Date(data.deadline_daftar).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
        : '-');
    const pelaksanaan = data.tanggal_pelaksanaan_formatted || (data.tanggal_pelaksanaan
        ? new Date(data.tanggal_pelaksanaan).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
        : '-');
    const biayaVal = parseFloat(data.biaya || 0);

    document.getElementById('modal-deadline').textContent = deadline;
    document.getElementById('modal-pelaksanaan').textContent = pelaksanaan;
    document.getElementById('modal-biaya').textContent = biayaVal <= 0
        ? 'Gratis (Rp 0)'
        : 'Rp ' + Number(biayaVal).toLocaleString('id-ID');
    document.getElementById('modal-partisipasi').textContent = partisipasiFormatted;
    document.getElementById('modal-tingkat').textContent = tingkat;
    document.getElementById('modal-bidang').textContent = bidang;

    const imgEl = document.getElementById('modal-img');
    const placeholderEl = document.getElementById('modal-placeholder');
    const placeholderTitle = document.getElementById('modal-placeholder-title');
    const placeholderCat = document.getElementById('modal-placeholder-cat');
    const posterSrc = (data.poster_url && data.poster && String(data.poster).trim() !== '')
        ? data.poster_url
        : ((data.poster && String(data.poster).trim() !== '') ? data.poster : '');

    if (posterSrc !== '') {
        imgEl.src = posterSrc;
        imgEl.alt = 'Poster ' + (data.nama_lomba || 'Lomba');
        imgEl.style.display = 'block';
        if (placeholderEl) placeholderEl.style.display = 'none';
    } else {
        imgEl.style.display = 'none';
        if (placeholderEl) {
            placeholderEl.style.display = 'flex';
            if (placeholderTitle) placeholderTitle.textContent = data.nama_lomba || '';
            if (placeholderCat) placeholderCat.textContent = bidang;
        }
    }

    const linkResmi = document.getElementById('modal-link-resmi');
    if (linkResmi) {
        if (data.link_resmi && String(data.link_resmi).trim() !== '') {
            linkResmi.href = data.link_resmi;
            linkResmi.style.display = 'inline-flex';
        } else {
            linkResmi.href = '#';
            linkResmi.style.display = 'none';
        }
    }

    const linkGuidebook = document.getElementById('modal-link-guidebook');
    if (linkGuidebook) {
        if (data.link_guidebook && String(data.link_guidebook).trim() !== '') {
            linkGuidebook.href = data.link_guidebook;
            linkGuidebook.style.display = 'inline-flex';
        } else {
            linkGuidebook.style.display = 'none';
        }
    }

    currentModalDetailUrl = data.detail_url
        || (window.location.origin + window.location.pathname + '?page=lomba-detail&id=' + encodeURIComponent(idLomba));
    const linkFullpage = document.getElementById('modal-link-fullpage');
    if (linkFullpage) {
        linkFullpage.href = currentModalDetailUrl;
    }
    const copyLabel = document.getElementById('modal-copy-label');
    if (copyLabel) {
        copyLabel.textContent = 'Salin Link Lomba';
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function copyModalLombaLink() {
    const targetUrl = currentModalDetailUrl || window.location.href;
    const copyLabel = document.getElementById('modal-copy-label');
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(targetUrl).then(() => {
            if (copyLabel) {
                copyLabel.textContent = 'Link Tersalin!';
                setTimeout(() => { copyLabel.textContent = 'Salin Link Lomba'; }, 2000);
            }
        });
    }
}

function closeDetailModal(e) {
    if (e && e.target !== e.currentTarget && !e.target.classList.contains('dash-modal-close')) {
        return;
    }
    const modal = document.getElementById('detail-modal-overlay');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function switchRekanTab(tab) {
    const viewLomba = document.getElementById('view-rekan-lomba');
    const viewTeman = document.getElementById('view-rekan-teman');
    const btnLomba  = document.getElementById('tab-rekan-lomba');
    const btnTeman  = document.getElementById('tab-rekan-teman');

    if (tab === 'teman') {
        if (viewLomba) viewLomba.style.display = 'none';
        if (viewTeman) viewTeman.style.display = 'block';
        if (btnLomba)  btnLomba.classList.remove('is-active');
        if (btnTeman)  btnTeman.classList.add('is-active');
    } else {
        if (viewLomba) viewLomba.style.display = 'block';
        if (viewTeman) viewTeman.style.display = 'none';
        if (btnLomba)  btnLomba.classList.add('is-active');
        if (btnTeman)  btnTeman.classList.remove('is-active');
    }
}

function scrollSlider(sliderId, offset) {
    const slider = document.getElementById(sliderId);
    if (slider) {
        slider.scrollBy({ left: offset, behavior: 'smooth' });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Dropdown Avatar Toggle
    const avatarBtn = document.getElementById('dash-avatar-btn');
    const avatarWrapper = document.getElementById('dash-avatar-wrapper');

    if (avatarBtn && avatarWrapper) {
        avatarBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            avatarWrapper.classList.toggle('is-open');
        });

        document.addEventListener('click', (e) => {
            if (!avatarWrapper.contains(e.target)) {
                avatarWrapper.classList.remove('is-open');
            }
        });
    }

    // 2. Live Debounced Search (300ms) & Filter Bidang Terpadu
    const pillButtons = Array.from(document.querySelectorAll('.dash-pill-btn'));
    const searchInput = document.getElementById('dash-search-input');
    const searchForm  = document.getElementById('dash-search-form');
    const gridCards   = Array.from(document.querySelectorAll('.grid-card-item'));
    const emptyState  = document.getElementById('empty-search-state');
    const emptyTitle  = document.getElementById('empty-search-title');
    const emptyDesc   = document.getElementById('empty-search-desc');
    const countNumber = document.getElementById('count-number');
    const activeFilterBar  = document.getElementById('active-filter-bar');
    const chipActiveBidang = document.getElementById('chip-active-bidang');
    const chipBidangVal    = document.getElementById('chip-bidang-val');
    const chipActiveQuery  = document.getElementById('chip-active-query');
    const chipQueryVal     = document.getElementById('chip-query-val');

    const initialActiveBtn = pillButtons.find(b => b.classList.contains('is-active')) || pillButtons[0];
    let activeBidangLabel = initialActiveBtn
        ? (initialActiveBtn.dataset.bidang || 'Semua Bidang').trim()
        : 'Semua Bidang';
    let activeBidang = activeBidangLabel.toLowerCase();
    let rawSearchText = searchInput ? searchInput.value.trim() : '';
    let searchQuery = searchInput ? normalizeSearchText(searchInput.value) : '';

    /**
     * Sinkronisasi URL Query String (?page=mahasiswa-dashboard&bidang=...&q=...) tanpa reload halaman
     */
    function syncUrlQueryParams() {
        if (!window.history || typeof window.history.replaceState !== 'function') return;

        try {
            const url = new URL(window.location.href);
            if (!url.searchParams.get('page')) {
                url.searchParams.set('page', 'mahasiswa-dashboard');
            }

            if (activeBidang !== 'semua bidang' && activeBidangLabel !== '') {
                url.searchParams.set('bidang', activeBidangLabel);
            } else {
                url.searchParams.delete('bidang');
            }

            if (rawSearchText !== '') {
                url.searchParams.set('q', rawSearchText);
            } else {
                url.searchParams.delete('q');
            }

            window.history.replaceState({}, '', url.toString());
        } catch (_) {
            // Abaikan pada environment headless/test tanpa URL valid
        }
    }

    /**
     * Perbarui bar indikator kombinasi Filter Bidang + Kata Kunci aktif secara real-time
     */
    function updateActiveFilterBar() {
        if (!activeFilterBar) return;

        const hasBidang = activeBidang !== 'semua bidang';
        const hasQuery  = searchQuery !== '';

        activeFilterBar.style.display = (hasBidang || hasQuery) ? 'flex' : 'none';

        if (chipActiveBidang && chipBidangVal) {
            chipActiveBidang.style.display = hasBidang ? 'inline-flex' : 'none';
            chipBidangVal.textContent = activeBidangLabel;
        }

        if (chipActiveQuery && chipQueryVal) {
            chipActiveQuery.style.display = hasQuery ? 'inline-flex' : 'none';
            chipQueryVal.textContent = '"' + (rawSearchText || searchQuery) + '"';
        }
    }

    /**
     * Perbarui pesan pada Empty State secara kontekstual sesuai kombinasi filter & keyword aktif
     */
    function updateEmptyStateMessage(visibleCount) {
        if (!emptyState) return;
        emptyState.style.display = visibleCount === 0 ? 'flex' : 'none';

        if (visibleCount === 0) {
            const hasQuery = searchQuery !== '';
            const hasBidang = activeBidang !== 'semua bidang';
            const displayQuery = rawSearchText || searchQuery;

            if (emptyTitle) {
                emptyTitle.textContent = 'Tidak ada lomba yang sesuai';
            }
            if (emptyDesc) {
                if (hasQuery && hasBidang) {
                    emptyDesc.textContent = 'Tidak ditemukan lomba dengan kata kunci "' + displayQuery + '" pada bidang "' + activeBidangLabel + '".';
                } else if (hasQuery) {
                    emptyDesc.textContent = 'Tidak ditemukan lomba yang cocok dengan kata kunci "' + displayQuery + '".';
                } else if (hasBidang) {
                    emptyDesc.textContent = 'Belum ada lomba yang tersedia pada bidang "' + activeBidangLabel + '" saat ini.';
                } else {
                    emptyDesc.textContent = 'Coba kata kunci lain atau pilih filter kategori "Semua Bidang".';
                }
            }
        }
    }

    /**
     * Sinkronisasi kombinasi Filter Bidang + Search Keyword pada kartu DOM (Logika AND ketat)
     */
    function filterCards(matchedIdsFromApi = null) {
        let visibleCount = 0;

        gridCards.forEach(card => {
            const cardId      = parseInt(card.dataset.id || '0', 10);
            const cardBidang  = (card.dataset.bidang || '').trim().toLowerCase();
            const cardTitle   = card.dataset.title || '';
            const cardPeny    = card.dataset.penyelenggara || '';
            const cardDesc    = card.dataset.desc || '';
            const cardTingkat = card.dataset.tingkat || '';
            const cardPartis  = card.dataset.partisipasi || '';

            const matchBidang = (activeBidang === 'semua bidang')
                || cardBidang === activeBidang;

            const matchSearch = !searchQuery
                || cardTitle.includes(searchQuery)
                || cardPeny.includes(searchQuery)
                || cardDesc.includes(searchQuery)
                || cardBidang.includes(searchQuery)
                || cardTingkat.includes(searchQuery)
                || cardPartis.includes(searchQuery);

            let isMatched = matchBidang && matchSearch;

            if (Array.isArray(matchedIdsFromApi)) {
                isMatched = matchBidang && matchedIdsFromApi.includes(cardId);
            }

            if (isMatched) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (countNumber) {
            countNumber.textContent = String(visibleCount);
        }
        updateActiveFilterBar();
        updateEmptyStateMessage(visibleCount);
    }

    /**
     * Sinkronisasi async ke Endpoint JSON API (?page=api-competitions) dengan fallback instan ke DOM
     */
    function syncWithBackendApi() {
        if (typeof window.fetch !== 'function') return;

        if (activeFetchController) {
            activeFetchController.abort();
        }
        activeFetchController = new AbortController();

        const params = new URLSearchParams();
        params.set('page', 'api-competitions');
        if (searchQuery !== '') {
            params.set('q', searchQuery);
        }
        if (activeBidangLabel !== '' && activeBidang !== 'semua bidang') {
            params.set('bidang', activeBidangLabel);
        }

        fetch('?' + params.toString(), {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            signal: activeFetchController.signal,
        })
            .then(res => (res.ok ? res.json() : null))
            .then(payload => {
                if (payload && payload.success && Array.isArray(payload.data)) {
                    const matchedIds = payload.data.map(item => parseInt(item.id_lomba, 10));
                    filterCards(matchedIds);
                }
            })
            .catch(() => {
                // Abaikan jika request dibatalkan (AbortError) atau offline; fallback DOM sudah berjalan
            });
    }

    // Debounced handler (300ms) untuk input pencarian real-time
    const debouncedSearch = debounce(() => {
        rawSearchText = searchInput ? searchInput.value.trim() : '';
        searchQuery   = searchInput ? normalizeSearchText(searchInput.value) : '';
        filterCards();
        syncUrlQueryParams();
        syncWithBackendApi();
    }, 300);

    function activatePill(targetBtn, shouldFocus = false) {
        if (!targetBtn) return;

        pillButtons.forEach(b => {
            const isTarget = (b === targetBtn);
            b.classList.toggle('is-active', isTarget);
            b.setAttribute('aria-selected', isTarget ? 'true' : 'false');
            b.setAttribute('tabindex', isTarget ? '0' : '-1');
        });

        if (shouldFocus) {
            targetBtn.focus();
        }

        activeBidangLabel = (targetBtn.dataset.bidang || 'Semua Bidang').trim();
        activeBidang = activeBidangLabel.toLowerCase();
        filterCards();
        syncUrlQueryParams();
        syncWithBackendApi();
    }

    pillButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            activatePill(btn, false);
        });

        btn.addEventListener('keydown', (e) => {
            const total = pillButtons.length;
            if (total === 0) return;

            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                activatePill(btn, true);
            } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                const nextBtn = pillButtons[(index + 1) % total];
                activatePill(nextBtn, true);
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                const prevBtn = pillButtons[(index - 1 + total) % total];
                activatePill(prevBtn, true);
            } else if (e.key === 'Home') {
                e.preventDefault();
                activatePill(pillButtons[0], true);
            } else if (e.key === 'End') {
                e.preventDefault();
                activatePill(pillButtons[total - 1], true);
            }
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            debouncedSearch();
        });
    }

    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            debouncedSearch.cancel();
            rawSearchText = searchInput ? searchInput.value.trim() : '';
            searchQuery   = searchInput ? normalizeSearchText(searchInput.value) : '';
            filterCards();
            syncUrlQueryParams();
            syncWithBackendApi();
            const gridEl = document.getElementById('section-semua-lomba');
            if (gridEl) gridEl.scrollIntoView({ behavior: 'smooth' });
        });
    }

    window.resetFilters = function () {
        debouncedSearch.cancel();
        if (searchInput) searchInput.value = '';
        rawSearchText = '';
        searchQuery   = '';
        if (pillButtons.length > 0) {
            activatePill(pillButtons[0], false);
        } else {
            activeBidangLabel = 'Semua Bidang';
            activeBidang = 'semua bidang';
            filterCards();
            syncUrlQueryParams();
        }
    };

    // Jalankan evaluasi awal saat halaman dimuat
    filterCards();
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('detail-modal-overlay');
        if (modal && modal.style.display !== 'none') {
            closeDetailModal();
        }
    }
});

