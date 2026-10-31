let activeFetchController = null;

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

function normalizeSearchText(input) {
    return String(input || '')
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
}

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
    if (diffMs < 0) {
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

function sanitizeHttpUrl(rawUrl, allowRelative = false) {
    const cleaned = String(rawUrl || '').replace(/[\r\n\0\t]/g, '').trim();
    if (!cleaned) return '';
    if (/^(?:javascript|vbscript|data):/i.test(cleaned)) {
        return '';
    }
    if (/^https?:\/\//i.test(cleaned)) {
        return cleaned;
    }
    if (allowRelative && !/^[a-z][a-z0-9+.-]*:/i.test(cleaned)) {
        return cleaned;
    }
    return '';
}

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
    const countdown = computeDeadlineCountdown(data.deadline_daftar);
    const isClosed = data.is_closed === true || countdown.isClosed;
    if (countdownBadge) {
        countdownBadge.textContent = isClosed ? 'Pendaftaran Ditutup' : countdown.text;
        countdownBadge.classList.toggle('is-closed', isClosed);
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
    const rawPosterSrc = (data.poster_url && data.poster && String(data.poster).trim() !== '')
        ? data.poster_url
        : ((data.poster && String(data.poster).trim() !== '') ? data.poster : '');
    const posterSrc = sanitizeHttpUrl(rawPosterSrc, true);

    if (placeholderTitle) placeholderTitle.textContent = data.nama_lomba || '';
    if (placeholderCat) placeholderCat.textContent = bidang;

    if (posterSrc !== '') {
        imgEl.onerror = () => {
            imgEl.style.display = 'none';
            if (placeholderEl) placeholderEl.style.display = 'flex';
        };
        imgEl.src = posterSrc;
        imgEl.alt = 'Poster ' + (data.nama_lomba || 'Lomba');
        imgEl.style.display = 'block';
        if (placeholderEl) placeholderEl.style.display = 'none';
    } else {
        imgEl.style.display = 'none';
        if (placeholderEl) {
            placeholderEl.style.display = 'flex';
        }
    }

    const linkResmi = document.getElementById('modal-link-resmi');
    const safeLinkResmi = sanitizeHttpUrl(data.link_resmi, false);
    if (linkResmi) {
        if (safeLinkResmi !== '' && !isClosed) {
            linkResmi.href = safeLinkResmi;
            linkResmi.style.display = 'inline-flex';
        } else {
            linkResmi.href = '#';
            linkResmi.style.display = 'none';
        }
    }

    const linkCariTim = document.getElementById('modal-link-cari-tim');
    if (linkCariTim) {
        const isTeamAllowed = (partisipasiRaw === 'Tim' || partisipasiRaw === 'Individu/Tim');
        if (isTeamAllowed && !isClosed) {
            linkCariTim.href = window.location.origin + window.location.pathname
                + '?page=team-posts&id_lomba=' + encodeURIComponent(idLomba);
            linkCariTim.style.display = 'inline-flex';
        } else {
            linkCariTim.style.display = 'none';
        }
    }

    const noLinksNotice = document.getElementById('modal-no-links-notice');
    if (noLinksNotice) {
        noLinksNotice.style.display = safeLinkResmi === '' ? 'flex' : 'none';
    }
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
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

function scrollSlider(sliderId, offset) {
    const slider = document.getElementById(sliderId);
    if (slider) {
        slider.scrollBy({ left: offset, behavior: 'smooth' });
    }
}

const recommendationTabs = Array.from(document.querySelectorAll('#recommendation-tabs [role="tab"]'));

function activateRecommendationTab(activeIndex) {
    recommendationTabs.forEach((tab, index) => {
        const isActive = index === activeIndex;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', String(isActive));
        tab.tabIndex = isActive ? 0 : -1;
        const panel = document.getElementById(tab.getAttribute('aria-controls'));
        if (panel) panel.hidden = !isActive;
    });
}

recommendationTabs.forEach((tab, index) => {
    tab.addEventListener('click', () => {
        activateRecommendationTab(index);
    });
    tab.addEventListener('keydown', (event) => {
        let nextIndex = index;
        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
            nextIndex = (index + 1) % recommendationTabs.length;
        } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
            nextIndex = (index - 1 + recommendationTabs.length) % recommendationTabs.length;
        } else if (event.key === 'Home') {
            nextIndex = 0;
        } else if (event.key === 'End') {
            nextIndex = recommendationTabs.length - 1;
        } else {
            return;
        }
        event.preventDefault();
        activateRecommendationTab(nextIndex);
        recommendationTabs[nextIndex].focus();
    });
});

document.addEventListener('DOMContentLoaded', () => {

    const pillButtons = Array.from(document.querySelectorAll('.dash-pill-btn'));
    const searchInput = document.getElementById('dash-search-input');
    const searchForm  = document.getElementById('dash-search-form');
    const gridCards      = Array.from(document.querySelectorAll('.grid-card-item'));
    const semuaLombaGrid = document.getElementById('semua-lomba-grid');
    const emptyState     = document.getElementById('empty-search-state');
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

        }
    }

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

    function updateEmptyStateMessage(visibleCount) {
        if (semuaLombaGrid) {
            semuaLombaGrid.style.display = visibleCount === 0 ? 'none' : 'grid';
        }
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

    function filterCards(matchedIdsFromApi = null) {
        let visibleCount = 0;
        const queryTokens = searchQuery ? searchQuery.split(/\s+/).filter(Boolean) : [];

        gridCards.forEach(card => {
            const cardId      = parseInt(card.dataset.id || '0', 10);
            const cardBidang  = (card.dataset.bidang || '').trim().toLowerCase();
            const cardTitle   = (card.dataset.title || '').toLowerCase();
            const cardPeny    = (card.dataset.penyelenggara || '').toLowerCase();
            const cardDesc    = (card.dataset.desc || '').toLowerCase();
            const cardTingkat = (card.dataset.tingkat || '').toLowerCase();
            const cardPartis  = (card.dataset.partisipasi || '').toLowerCase();

            const cardBidangItems = cardBidang.split(',').map(s => s.trim()).filter(Boolean);
            const matchBidang = (activeBidang === 'semua bidang')
                || cardBidang === activeBidang
                || cardBidangItems.includes(activeBidang)
                || cardBidang.includes(activeBidang);

            const searchableCorpus = [cardTitle, cardPeny, cardBidang, cardDesc, cardTingkat, cardPartis].join(' ');
            const matchSearch = !searchQuery
                || searchableCorpus.includes(searchQuery)
                || (queryTokens.length > 0 && queryTokens.every(token => searchableCorpus.includes(token)));

            let isMatched = matchBidang && matchSearch;

            if (Array.isArray(matchedIdsFromApi)) {
                isMatched = matchBidang && (matchedIdsFromApi.includes(cardId) || matchSearch);
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

            });
    }

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
