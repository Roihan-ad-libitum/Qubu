/**
 * JavaScript Kontroler 3D Coverflow Carousel Qubu
 * Mengatur interaktivitas scroll kanan-kiri (mouse drag, touch swipe, wheel, klik kartu, tombol, keyboard)
 */

document.addEventListener('DOMContentLoaded', () => {
    const track = document.getElementById('carousel-track');
    const viewport = document.getElementById('carousel-viewport');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const dotsContainer = document.getElementById('carousel-dots');

    if (!track || !viewport) return;

    const cards = Array.from(track.querySelectorAll('.carousel-card'));
    const totalCards = cards.length;
    if (totalCards === 0) return;

    // Awal aktif di Card index 2 (UI/UX Design) persis seperti screenshot awal
    let activeIndex = 2;
    let isTransitioning = false;

    // Generate Indicator Dots
    dotsContainer.innerHTML = '';
    const dots = [];
    cards.forEach((card, index) => {
        const dot = document.createElement('button');
        dot.className = 'carousel-dot' + (index === activeIndex ? ' is-active' : '');
        dot.setAttribute('aria-label', `Pindah ke kategori ${card.dataset.title || index + 1}`);
        dot.addEventListener('click', (e) => {
            e.stopPropagation();
            goToIndex(index);
        });
        dotsContainer.appendChild(dot);
        dots.push(dot);
    });

    /**
     * Memperbarui posisi seluruh kartu secara circular 3D
     */
    function updateCards() {
        cards.forEach((card, index) => {
            let offset = index - activeIndex;

            // Hitung circular offset dalam rentang [-2, 2]
            while (offset > Math.floor(totalCards / 2)) offset -= totalCards;
            while (offset < -Math.floor(totalCards / 2)) offset += totalCards;

            card.setAttribute('data-offset', offset.toString());
            card.setAttribute('aria-hidden', offset === 0 ? 'false' : 'true');

            if (offset === 0) {
                card.classList.add('is-active');
            } else {
                card.classList.remove('is-active');
            }
        });

        // Update indikator dots
        dots.forEach((dot, index) => {
            if (index === activeIndex) {
                dot.classList.add('is-active');
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.classList.remove('is-active');
                dot.removeAttribute('aria-current');
            }
        });
    }

    /**
     * Navigasi ke index tertentu
     */
    function goToIndex(index) {
        if (isTransitioning) return;
        isTransitioning = true;
        activeIndex = (index + totalCards) % totalCards;
        updateCards();
        setTimeout(() => {
            isTransitioning = false;
        }, 300);
    }

    function goToNext() {
        goToIndex(activeIndex + 1);
    }

    function goToPrev() {
        goToIndex(activeIndex - 1);
    }

    // 1. Klik Tombol Panah
    if (btnPrev) {
        btnPrev.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            goToPrev();
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            goToNext();
        });
    }

    // 2. Klik Langsung Pada Kartu
    cards.forEach((card, index) => {
        card.addEventListener('click', (e) => {
            const offset = parseInt(card.getAttribute('data-offset'), 10);
            if (offset !== 0) {
                e.preventDefault();
                goToIndex(index);
            }
        });
    });

    // 3. Horizontal & Vertical Wheel Scroll (Mouse wheel / Touchpad gesture)
    let wheelCooldown = false;
    viewport.addEventListener('wheel', (e) => {
        if (wheelCooldown) return;
        const delta = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
        if (Math.abs(delta) > 20) {
            if (delta > 0) {
                goToNext();
            } else {
                goToPrev();
            }
            wheelCooldown = true;
            setTimeout(() => {
                wheelCooldown = false;
            }, 300);
        }
    }, { passive: true });

    // 4. Mouse Drag & Touch Swipe (Pointer Events)
    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    let hasMoved = false;

    viewport.addEventListener('pointerdown', (e) => {
        // Jangan drag jika klik tombol navigasi
        if (e.target.closest('.carousel-arrow') || e.target.closest('.carousel-dot')) return;
        isDragging = true;
        hasMoved = false;
        startX = e.clientX;
        currentX = e.clientX;
        viewport.classList.add('is-dragging');
        viewport.setPointerCapture(e.pointerId);
    });

    viewport.addEventListener('pointermove', (e) => {
        if (!isDragging) return;
        currentX = e.clientX;
        const deltaX = currentX - startX;
        if (Math.abs(deltaX) > 8) {
            hasMoved = true;
        }
    });

    const handlePointerEnd = (e) => {
        if (!isDragging) return;
        isDragging = false;
        viewport.classList.remove('is-dragging');
        try {
            viewport.releasePointerCapture(e.pointerId);
        } catch (_) {}

        const deltaX = currentX - startX;
        const threshold = 35; // pixel minimum untuk memicu pergeseran

        if (deltaX < -threshold) {
            // Drag ke kiri -> geser ke item berikutnya
            goToNext();
        } else if (deltaX > threshold) {
            // Drag ke kanan -> geser ke item sebelumnya
            goToPrev();
        }
    };

    viewport.addEventListener('pointerup', handlePointerEnd);
    viewport.addEventListener('pointercancel', handlePointerEnd);

    // 5. Keyboard Navigation (Panah Kiri & Kanan)
    window.addEventListener('keydown', (e) => {
        // Hanya tangani jika tidak sedang mengetik di input/textarea
        if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
        if (e.key === 'ArrowLeft') {
            goToPrev();
        } else if (e.key === 'ArrowRight') {
            goToNext();
        }
    });

    // Inisialisasi tampilan awal
    updateCards();
});
