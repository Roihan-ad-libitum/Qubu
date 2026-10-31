(function () {
    'use strict';

    let toastTimer = null;
    let activeAbortController = null;
    let loadingHideTimer = null;

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

    function sanitizePartnerUrl(rawUrl) {
        if (!rawUrl || typeof rawUrl !== 'string') return '';
        const trimmed = rawUrl.trim();
        if (trimmed === '') return '';
        const lower = trimmed.toLowerCase();
        if (lower.indexOf('javascript:') === 0 || lower.indexOf('data:') === 0 || lower.indexOf('vbscript:') === 0) {
            return '';
        }
        return trimmed;
    }

    window.sanitizePartnerUrl = sanitizePartnerUrl;

    function normalizePhoneDigits(rawPhone) {
        let digits = String(rawPhone || '').replace(/\D+/g, '');
        if (digits.indexOf('0') === 0) {
            digits = '62' + digits.substring(1);
        } else if (digits.indexOf('8') === 0) {
            digits = '62' + digits;
        }
        return digits.length >= 8 ? digits : '';
    }

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

    window.openPartnerProfileModal = function (mhsInput) {
        const modal = document.getElementById('partner-profile-modal');
        if (!modal || !mhsInput) return;

        let mhsData = mhsInput;
        if (typeof mhsInput === 'string') {
            try {
                mhsData = JSON.parse(mhsInput);
            } catch (_) {
                return;
            }
        } else if (mhsInput && mhsInput.dataset && mhsInput.dataset.profile) {
            try {
                mhsData = JSON.parse(mhsInput.dataset.profile);
            } catch (_) {
                return;
            }
        }
        if (!mhsData || typeof mhsData !== 'object') return;

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

        const nama     = String(mhsData.nama_mahasiswa || 'Mahasiswa Qubu');
        const prodi    = String(mhsData.program_studi || 'Informatika');
        const angkatan = String(mhsData.angkatan || '2025');
        const npm      = String(mhsData.npm || '-');
        const inisial  = String(mhsData.inisial || nama.substring(0, 2).toUpperCase());
        const fotoUrl  = sanitizePartnerUrl(mhsData.foto_url || '');

        if (nameEl) nameEl.textContent = nama;
        if (subEl) subEl.textContent = prodi + ' \u2022 Angkatan ' + angkatan;
        if (npmEl) npmEl.textContent = npm;
        if (prodiEl) prodiEl.textContent = prodi;
        if (angkatanEl) angkatanEl.textContent = String(angkatan);

        const githubRowEl  = document.getElementById('pmodal-github-row');
        const githubLinkEl = document.getElementById('pmodal-github-link');
        const githubBtnEl  = document.getElementById('pmodal-btn-github');
        const rawGhUrl     = sanitizePartnerUrl(mhsData.github_url || mhsData.github || '');

        if (rawGhUrl !== '') {
            const fullGhUrl = /^https?:\/\//i.test(rawGhUrl) ? rawGhUrl : ('https://github.com/' + rawGhUrl.replace(/^[@/]+/, ''));
            if (githubRowEl && githubLinkEl) {
                githubLinkEl.href = fullGhUrl;
                githubLinkEl.textContent = mhsData.github_display || fullGhUrl.replace(/^https?:\/\/(?:www\.)?/i, '');
                githubRowEl.style.display = 'flex';
            }
            if (githubBtnEl) {
                githubBtnEl.href = fullGhUrl;
                githubBtnEl.style.display = 'inline-flex';
            }
        } else {
            if (githubRowEl && githubLinkEl) {
                githubLinkEl.href = '#';
                githubRowEl.style.display = 'none';
            }
            if (githubBtnEl) {
                githubBtnEl.href = '#';
                githubBtnEl.style.display = 'none';
            }
        }

        if (fotoUrl && imgEl && initialsEl) {
            imgEl.src = fotoUrl;
            imgEl.alt = nama;
            imgEl.style.display = 'block';
            initialsEl.style.display = 'none';
            imgEl.onerror = function () {
                imgEl.style.display = 'none';
                initialsEl.textContent = inisial;
                initialsEl.style.display = 'inline';
            };
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
            const safeProfilUrl = sanitizePartnerUrl(mhsData.profil_url || '');
            fullLinkEl.href = safeProfilUrl !== ''
                ? safeProfilUrl
                : ('?page=lihat-profil&id=' + encodeURIComponent(mhsData.id_mahasiswa || 0));
        }

        const inviteBtnEl = document.getElementById('pmodal-btn-invite');
        if (inviteBtnEl) {
            const digits   = normalizePhoneDigits(mhsData.no_hp);
            const emailVal = String(mhsData.email || '').trim();
            if (digits !== '') {
                inviteBtnEl.href = 'https://wa.me/' + digits + '?text=' + encodeURIComponent('Halo ' + nama + ', saya melihat profil Anda di Qubu dan ingin berdiskusi mengenai tim lomba.');
                inviteBtnEl.textContent = 'Hubungi via WhatsApp';
                inviteBtnEl.target = '_blank';
            } else if (emailVal.indexOf('@') !== -1) {
                inviteBtnEl.href = 'mailto:' + emailVal + '?subject=' + encodeURIComponent('Kolaborasi Tim Lomba via Qubu');
                inviteBtnEl.textContent = 'Hubungi via Email';
                inviteBtnEl.removeAttribute('target');
            } else {
                inviteBtnEl.href = fullLinkEl ? fullLinkEl.href : '#';
                inviteBtnEl.textContent = 'Lihat Kontak di Profil';
                inviteBtnEl.removeAttribute('target');
            }
        }

        modal.style.display = 'flex';
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.closePartnerProfileModal = function () {
        const modal = document.getElementById('partner-profile-modal');
        if (!modal) return;
        modal.style.display = 'none';
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    window.setPartnerLoadingState = function (isLoading, customLoadingId, customGridId) {
        const loadingEl = document.getElementById(customLoadingId || 'partner-loading-state');
        const gridEl    = document.getElementById(customGridId || 'partner-grid');
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

    function initDirectoryFilter(cfg) {
        const mainContainer   = document.getElementById(cfg.mainContainerId);
        const gridEl          = document.getElementById(cfg.gridId);
        if (!mainContainer || !gridEl) return null;

        const pillsContainer  = cfg.pillsContainerId ? document.getElementById(cfg.pillsContainerId) : null;
        const pillButtons     = (pillsContainer && cfg.pillSelector) ? Array.prototype.slice.call(pillsContainer.querySelectorAll(cfg.pillSelector)) : [];
        const filterForm      = document.getElementById(cfg.formId);
        const searchInput     = cfg.searchInputId ? document.getElementById(cfg.searchInputId) : null;
        const selectEl        = cfg.selectId ? document.getElementById(cfg.selectId) : null;
        const secondarySelEl  = cfg.secondarySelectId ? document.getElementById(cfg.secondarySelectId) : null;
        const cardItems       = Array.prototype.slice.call(gridEl.querySelectorAll(cfg.cardSelector));
        const countBadge      = document.getElementById(cfg.countBadgeId);
        const activeFilterBar = document.getElementById(cfg.activeFilterBarId);
        const activeFilterLbl = document.getElementById(cfg.activeFilterLabelId);
        const emptyState      = document.getElementById(cfg.emptyStateId);
        const emptyMsg        = document.getElementById(cfg.emptyMsgId);
        const btnClearFilter  = document.getElementById(cfg.clearBtnId);
        const btnResetEmpty   = document.getElementById(cfg.resetEmptyBtnId);

        const defaultSecVal = typeof cfg.defaultSecondary === 'string' ? cfg.defaultSecondary : '';

        let activeBidang = 'Semua Bidang';
        pillButtons.forEach(function (btn) {
            if (btn.classList.contains('is-active') && btn.dataset.bidang) {
                activeBidang = btn.dataset.bidang;
            }
        });
        if (cfg.syncSelectWithBidang && selectEl && selectEl.value) {
            activeBidang = selectEl.value;
        }

        const effectiveSecondarySelect = secondarySelEl || (!cfg.syncSelectWithBidang ? selectEl : null);
        let activeSecondary = (effectiveSecondarySelect && typeof effectiveSecondarySelect.value === 'string')
            ? effectiveSecondarySelect.value
            : defaultSecVal;

        function getSecondaryDisplayLabel(secVal) {
            if (!secVal || secVal === defaultSecVal) return '';
            if (effectiveSecondarySelect && effectiveSecondarySelect.options) {
                for (let i = 0; i < effectiveSecondarySelect.options.length; i++) {
                    const opt = effectiveSecondarySelect.options[i];
                    if (String(opt.value) === String(secVal)) {
                        return opt.text.replace(/\s*\([^)]*\)\s*$/, '').trim() || secVal;
                    }
                }
            }
            return secVal;
        }

        function syncPillsUi(selectedBidang) {
            activeBidang = selectedBidang || 'Semua Bidang';
            if (cfg.syncSelectWithBidang && selectEl && selectEl.value !== activeBidang) {
                selectEl.value = activeBidang;
            }
            if (filterForm) {
                const hiddenBidang = filterForm.querySelector('input[type="hidden"][name="bidang"]');
                if (hiddenBidang) {
                    hiddenBidang.value = activeBidang !== 'Semua Bidang' ? activeBidang : '';
                }
            }
            pillButtons.forEach(function (btn) {
                const btnBidang  = btn.dataset.bidang || 'Semua Bidang';
                const isSelected = btnBidang.toLowerCase() === activeBidang.toLowerCase();
                btn.classList.toggle('is-active', isSelected);
                btn.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                btn.setAttribute('tabindex', isSelected ? '0' : '-1');
            });
        }

        function renderActiveFilterLabel(bidang, secondaryVal, keyword) {
            if (!activeFilterLbl) return;
            while (activeFilterLbl.firstChild) {
                activeFilterLbl.removeChild(activeFilterLbl.firstChild);
            }

            activeFilterLbl.appendChild(document.createTextNode('Filter Aktif: '));
            const strongBidang = document.createElement('strong');
            strongBidang.textContent = bidang;
            activeFilterLbl.appendChild(strongBidang);

            const secLabel = getSecondaryDisplayLabel(secondaryVal);
            if (cfg.secondaryPrefix && secLabel !== '') {
                activeFilterLbl.appendChild(document.createTextNode(' \u2022 ' + cfg.secondaryPrefix + ': '));
                const strongSec = document.createElement('strong');
                strongSec.textContent = secLabel;
                activeFilterLbl.appendChild(strongSec);
            }

            if (keyword !== '') {
                activeFilterLbl.appendChild(document.createTextNode(' \u2022 Kata kunci: "' + keyword + '"'));
            }
        }

        function syncUrl(bidang, secondaryVal, keyword) {
            if (!window.history || typeof window.history.replaceState !== 'function') return;
            try {
                const urlObj = new URL(window.location.href);
                urlObj.searchParams.set('page', cfg.pageName);

                if (bidang && bidang !== 'Semua Bidang') {
                    urlObj.searchParams.set('bidang', bidang);
                } else {
                    urlObj.searchParams.delete('bidang');
                }

                if (cfg.secondaryParamName) {
                    if (secondaryVal && secondaryVal !== defaultSecVal) {
                        urlObj.searchParams.set(cfg.secondaryParamName, secondaryVal);
                    } else {
                        urlObj.searchParams.delete(cfg.secondaryParamName);
                    }
                }

                if (keyword !== '') {
                    urlObj.searchParams.set('q', keyword);
                } else {
                    urlObj.searchParams.delete('q');
                }

                if (cfg.secondaryParamName !== 'id_lomba') {
                    const idLomba = mainContainer.dataset.idLomba ? mainContainer.dataset.idLomba.trim() : '';
                    if (idLomba !== '') {
                        urlObj.searchParams.set('id_lomba', idLomba);
                    }
                }

                window.history.replaceState({ bidang: bidang, secondary: secondaryVal, q: keyword }, '', urlObj.toString());
            } catch (_) {

            }
        }

        function filterCards(options) {
            const opts       = options || {};
            const rawKeyword = searchInput ? searchInput.value.trim() : '';
            const cleanQuery = rawKeyword.toLowerCase();

            if (effectiveSecondarySelect) {
                activeSecondary = typeof effectiveSecondarySelect.value === 'string'
                    ? effectiveSecondarySelect.value
                    : defaultSecVal;
            }

            const targetBidangLow    = activeBidang.toLowerCase();
            const targetSecondaryLow = String(activeSecondary || '').toLowerCase();

            if (opts.showLoading !== false) {
                window.setPartnerLoadingState(true, cfg.loadingId, cfg.gridId);
                loadingHideTimer = setTimeout(function () {
                    window.setPartnerLoadingState(false, cfg.loadingId, cfg.gridId);
                }, 140);
            }

            let visibleCount = 0;

            cardItems.forEach(function (card) {
                const cardBidang = (card.dataset.bidang || '').toLowerCase();
                let matchBidang  = (activeBidang === 'Semua Bidang');
                if (!matchBidang) {
                    const tags  = cardBidang.split(',').map(function (t) { return t.trim(); });
                    matchBidang = tags.indexOf(targetBidangLow) !== -1 || cardBidang.indexOf(targetBidangLow) !== -1;
                }

                const matchSecondary = !cfg.secondaryDatasetKey
                    || activeSecondary === defaultSecVal
                    || (String(card.dataset[cfg.secondaryDatasetKey] || '').toLowerCase() === targetSecondaryLow);

                let matchKeyword = (cleanQuery === '');
                if (!matchKeyword && Array.isArray(cfg.searchDatasetKeys)) {
                    matchKeyword = cfg.searchDatasetKeys.some(function (key) {
                        return (card.dataset[key] || '').toLowerCase().indexOf(cleanQuery) !== -1;
                    });
                }

                if (matchBidang && matchSecondary && matchKeyword) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (countBadge && typeof cfg.formatCountText === 'function') {
                countBadge.textContent = cfg.formatCountText(visibleCount, activeBidang, activeSecondary, getSecondaryDisplayLabel(activeSecondary));
            }

            if (typeof cfg.onAfterFilter === 'function') {
                cfg.onAfterFilter(visibleCount, activeBidang, activeSecondary, getSecondaryDisplayLabel(activeSecondary));
            }

            const hasFilter = (activeBidang !== 'Semua Bidang')
                || (cfg.secondaryDatasetKey && activeSecondary !== defaultSecVal)
                || (rawKeyword !== '');

            if (activeFilterBar) {
                activeFilterBar.style.display = hasFilter ? 'flex' : 'none';
            }
            renderActiveFilterLabel(activeBidang, activeSecondary, rawKeyword);

            if (emptyMsg && typeof cfg.formatEmptyMessage === 'function') {
                emptyMsg.textContent = cfg.formatEmptyMessage(activeBidang, activeSecondary, rawKeyword, getSecondaryDisplayLabel(activeSecondary));
            }
            gridEl.style.display = visibleCount === 0 ? 'none' : 'grid';
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'flex' : 'none';
            }

            syncUrl(activeBidang, activeSecondary, rawKeyword);

            if (opts.syncApi && typeof cfg.onSyncApi === 'function') {
                cfg.onSyncApi(activeBidang, activeSecondary, rawKeyword, cardItems, countBadge, gridEl, emptyState, emptyMsg);
            }

            return visibleCount;
        }

        function resetFilters() {
            if (searchInput) {
                searchInput.value = '';
            }
            if (effectiveSecondarySelect) {
                effectiveSecondarySelect.value = defaultSecVal;
                activeSecondary = defaultSecVal;
            }
            syncPillsUi('Semua Bidang');
            filterCards({ showLoading: false });
        }

        pillButtons.forEach(function (btn, index) {
            btn.addEventListener('click', function () {
                syncPillsUi(btn.dataset.bidang || 'Semua Bidang');
                filterCards({ showLoading: true });
            });

            btn.addEventListener('keydown', function (e) {
                const total = pillButtons.length;
                if (total === 0) return;

                let targetBtn = null;
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    targetBtn = pillButtons[(index + 1) % total];
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    targetBtn = pillButtons[(index - 1 + total) % total];
                } else if (e.key === 'Home') {
                    e.preventDefault();
                    targetBtn = pillButtons[0];
                } else if (e.key === 'End') {
                    e.preventDefault();
                    targetBtn = pillButtons[total - 1];
                } else if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    targetBtn = btn;
                }

                if (targetBtn) {
                    syncPillsUi(targetBtn.dataset.bidang || 'Semua Bidang');
                    if (targetBtn !== btn && typeof targetBtn.focus === 'function') {
                        targetBtn.focus();
                    }
                    filterCards({ showLoading: true });
                }
            });
        });

        if (selectEl) {
            selectEl.addEventListener('change', function () {
                if (cfg.syncSelectWithBidang) {
                    syncPillsUi(selectEl.value || 'Semua Bidang');
                } else {
                    activeSecondary = selectEl.value || defaultSecVal;
                }
                filterCards({ showLoading: true });
            });
        }

        if (secondarySelEl) {
            secondarySelEl.addEventListener('change', function () {
                activeSecondary = secondarySelEl.value || defaultSecVal;
                filterCards({ showLoading: true });
            });
        }

        if (searchInput) {
            const debouncedSearch = debounce(function () {
                filterCards({ showLoading: true });
            }, 300);

            searchInput.addEventListener('input', debouncedSearch);
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterCards({ showLoading: true });
                } else if (e.key === 'Escape' && searchInput.value !== '') {
                    e.preventDefault();
                    searchInput.value = '';
                    filterCards({ showLoading: false });
                }
            });
        }

        if (filterForm) {
            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                filterCards({ showLoading: true });
            });
        }

        if (btnClearFilter) {
            btnClearFilter.addEventListener('click', resetFilters);
        }
        if (btnResetEmpty) {
            btnResetEmpty.addEventListener('click', resetFilters);
        }

        return {
            filterCards: filterCards,
            resetFilters: resetFilters,
        };
    }

    document.addEventListener('DOMContentLoaded', function () {

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

        const partnerCtrl = initDirectoryFilter({
            pageName:             'cari-partner',
            mainContainerId:      'partner-main-container',
            pillsContainerId:     'partner-pills-container',
            pillSelector:         '.partner-pill-btn',
            formId:               'partner-filter-form',
            searchInputId:        'partner-search-input',
            selectId:             'partner-prodi-select',
            gridId:               'partner-grid',
            cardSelector:         '.partner-card-item',
            countBadgeId:         'partner-count-badge',
            activeFilterBarId:    'active-partner-filter-bar',
            activeFilterLabelId:  'active-partner-filter-label',
            loadingId:            'partner-loading-state',
            emptyStateId:         'partner-empty-state',
            emptyMsgId:           'empty-partner-msg',
            clearBtnId:           'btn-clear-partner-filter',
            resetEmptyBtnId:      'btn-reset-empty-partner',
            syncSelectWithBidang: false,
            defaultSecondary:     'Semua Prodi',
            secondaryPrefix:      'Prodi',
            secondaryParamName:   'prodi',
            secondaryDatasetKey:  'prodi',
            searchDatasetKeys:    ['name', 'npm', 'prodi', 'bidang', 'bio'],
            formatCountText: function (count) {
                return 'Menampilkan ' + count + ' rekan yang siap berkolaborasi';
            },
            formatEmptyMessage: function (bidang, prodi, keyword) {
                const parts = [];
                if (bidang !== 'Semua Bidang') parts.push('bidang "' + bidang + '"');
                if (prodi !== 'Semua Prodi') parts.push('Program Studi "' + prodi + '"');
                if (keyword !== '') parts.push('kata kunci "' + keyword + '"');
                return parts.length > 0
                    ? ('Tidak ditemukan kandidat mahasiswa untuk kombinasi ' + parts.join(', ') + '. Coba sesuaikan atau reset filter Anda.')
                    : 'Coba gunakan kata kunci lain, pilih bidang minat berbeda, atau ubah filter Program Studi.';
            },
            onSyncApi: function (bidang, prodi, keyword, cardItems, countBadge, gridEl, emptyState) {
                const mainContainer = document.getElementById('partner-main-container');
                if (typeof window.fetch !== 'function' || !mainContainer) return;
                const baseApiUrl = mainContainer.dataset.apiUrl || '?page=api-partners';

                if (activeAbortController) {
                    activeAbortController.abort();
                }
                if (typeof AbortController !== 'undefined') {
                    activeAbortController = new AbortController();
                }

                window.setPartnerLoadingState(true, 'partner-loading-state', 'partner-grid');

                const params = new URLSearchParams();
                params.set('page', 'api-partners');
                if (bidang && bidang !== 'Semua Bidang') params.set('bidang', bidang);
                if (prodi && prodi !== 'Semua Prodi') params.set('prodi', prodi);
                if (keyword !== '') params.set('q', keyword);
                if (mainContainer.dataset.idLomba) params.set('id_lomba', mainContainer.dataset.idLomba);

                const sep        = baseApiUrl.indexOf('?') !== -1 ? '&' : '?';
                const requestUrl = baseApiUrl + sep + params.toString();
                const fetchOpts  = { method: 'GET', headers: { 'Accept': 'application/json' } };
                if (activeAbortController) fetchOpts.signal = activeAbortController.signal;

                window.fetch(requestUrl, fetchOpts)
                    .then(function (res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function (payload) {
                        window.setPartnerLoadingState(false, 'partner-loading-state', 'partner-grid');
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
                            countBadge.textContent = 'Menampilkan ' + count + ' rekan yang siap berkolaborasi';
                        }
                        if (gridEl) {
                            gridEl.style.display = count === 0 ? 'none' : 'grid';
                        }
                        if (emptyState) {
                            emptyState.style.display = count === 0 ? 'flex' : 'none';
                        }
                    })
                    .catch(function (err) {
                        if (err && err.name === 'AbortError') return;
                        window.setPartnerLoadingState(false, 'partner-loading-state', 'partner-grid');
                    });
            },
        });

        if (partnerCtrl) {
            window.filterPartnerCards  = partnerCtrl.filterCards;
            window.resetPartnerFilters = partnerCtrl.resetFilters;
        }

        const advisorCtrl = initDirectoryFilter({
            pageName:             'advisor-directory',
            mainContainerId:      'advisor-main-container',
            pillsContainerId:     'advisor-pills-container',
            pillSelector:         '.advisor-pill-btn',
            formId:               'advisor-filter-form',
            searchInputId:        'advisor-search-input',
            selectId:             'advisor-bidang-select',
            gridId:               'advisor-grid',
            cardSelector:         '.advisor-card-item',
            countBadgeId:         'advisor-count-badge',
            activeFilterBarId:    'active-advisor-filter-bar',
            activeFilterLabelId:  'active-advisor-filter-label',
            loadingId:            'advisor-loading-state',
            emptyStateId:         'advisor-empty-state',
            emptyMsgId:           'empty-advisor-msg',
            clearBtnId:           'btn-clear-advisor-filter',
            resetEmptyBtnId:      'btn-reset-empty-advisor',
            syncSelectWithBidang: true,
            searchDatasetKeys:    ['name', 'username', 'nidn', 'bidang'],
            formatCountText: function (count, bidang) {
                return 'Menampilkan ' + count + ' profil dosen pembimbing'
                    + (bidang !== 'Semua Bidang' ? ' pada bidang ' + bidang : '') + '.';
            },
            formatEmptyMessage: function (bidang, _secondary, keyword) {
                const parts = [];
                if (bidang !== 'Semua Bidang') parts.push('bidang keahlian "' + bidang + '"');
                if (keyword !== '') parts.push('kata kunci "' + keyword + '"');
                return parts.length > 0
                    ? ('Tidak ditemukan profil dosen pembimbing untuk kombinasi ' + parts.join(' dan ') + '. Coba sesuaikan atau reset filter Anda.')
                    : 'Coba gunakan kata kunci pencarian lain atau pilih kategori "Semua Bidang".';
            },
        });

        if (advisorCtrl) {
            window.filterAdvisorCards  = advisorCtrl.filterCards;
            window.resetAdvisorFilters = advisorCtrl.resetFilters;
        }

        const teamPostsCtrl = initDirectoryFilter({
            pageName:             'team-posts',
            mainContainerId:      'team-posts-main-container',
            formId:               'team-posts-filter-form',
            selectId:             'team-bidang-select',
            secondarySelectId:    'team-competition',
            gridId:               'team-posts-grid',
            cardSelector:         '.team-post-card-item',
            countBadgeId:         'team-posts-count-badge',
            activeFilterBarId:    'active-team-filter-bar',
            activeFilterLabelId:  'active-team-filter-label',
            loadingId:            'team-posts-loading-state',
            emptyStateId:         'team-posts-empty-state',
            emptyMsgId:           'empty-team-posts-msg',
            clearBtnId:           'btn-clear-team-filter',
            syncSelectWithBidang: true,
            defaultSecondary:     '',
            secondaryPrefix:      'Perlombaan',
            secondaryParamName:   'id_lomba',
            secondaryDatasetKey:  'lomba',
            formatCountText: function (count) {
                return 'Menampilkan ' + count + ' posting berstatus Open. Klik nama atau tombol Lihat Profil untuk meninjau pembuat posting sebelum menghubungi.';
            },
            formatEmptyMessage: function (bidang, lombaId, _keyword, lombaLabel) {
                const parts = [];
                if (lombaId !== '' && lombaLabel !== '') parts.push('perlombaan "' + lombaLabel + '"');
                if (bidang !== 'Semua Bidang') parts.push('bidang "' + bidang + '"');
                return parts.length > 0
                    ? ('Tidak ditemukan posting pencarian anggota tim untuk ' + parts.join(' pada ') + '. Coba sesuaikan atau reset filter Anda.')
                    : 'Jadilah yang pertama membuat posting pencarian anggota tim atau jelajahi Direktori Kandidat Partner.';
            },
            onAfterFilter: function (_count, _bidang, lombaId, lombaLabel) {
                const eyebrowEl = document.getElementById('team-posts-eyebrow');
                const titleEl   = document.getElementById('team-posts-heading-title');
                const createBtn = document.getElementById('btn-posting-cari-tim');
                const mainEl    = document.getElementById('team-posts-main-container');
                const baseUrl   = mainEl && mainEl.dataset.createBaseUrl ? mainEl.dataset.createBaseUrl : '?page=team-post-create';

                if (eyebrowEl) {
                    eyebrowEl.textContent = lombaId !== '' ? 'KOMPETISI TERPILIH' : 'SEMUA LOWONGAN TIM AKTIF';
                }
                if (titleEl) {
                    titleEl.textContent = (lombaId !== '' && lombaLabel !== '')
                        ? lombaLabel
                        : 'Daftar Posting Pencarian Anggota Tim (Open)';
                }
                if (createBtn) {
                    createBtn.href = baseUrl + (lombaId !== '' ? '&id_lomba=' + encodeURIComponent(lombaId) : '');
                }
            },
        });

        if (teamPostsCtrl) {
            window.filterTeamPostCards  = teamPostsCtrl.filterCards;
            window.resetTeamPostFilters = teamPostsCtrl.resetFilters;
        }
    });
})();
