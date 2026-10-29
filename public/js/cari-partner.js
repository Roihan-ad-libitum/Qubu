/**
 * Script Interaktif Halaman Cari Partner Qubu (public/js/cari-partner.js)
 * Mencakup:
 * - Dropdown Profil Avatar di Top Navbar
 * - Modal Profil Lengkap Kandidat Partner Read-Only (openPartnerProfileModal / closePartnerProfileModal)
 * - Toast Pemberitahuan Placeholder ("+ Posting Cari Tim" & "Ajak ke Tim")
 * - Filter Interaktif Kombinasi (6 Pills Bidang + Dropdown Program Studi + Search Input Debounced 250ms)
 * - Loading State, Empty State & Sinkronisasi URL (history.replaceState)
 */

(function () {
    'use strict';

    let toastTimer = null;

    /**
     * Tampilkan toast pemberitahuan untuk tombol placeholder ("Ajak ke Tim" / "+ Posting Cari Tim")
     */
    window.showPartnerPlaceholderNotice = function (message) {
        const toastEl = document.getElementById('partner-toast');
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
            skillsEl.innerHTML = '';
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
    });
})();

