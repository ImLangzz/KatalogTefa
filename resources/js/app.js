import './bootstrap';

document.querySelectorAll('[data-get-location]').forEach((button) => {
    const form = button.closest('form');
    const latitude = form?.querySelector('[data-location-latitude]');
    const longitude = form?.querySelector('[data-location-longitude]');
    const status = form?.querySelector('[data-location-status]');
    const clearButton = form?.querySelector('[data-clear-location]');

    if (!latitude || !longitude || !status || !clearButton) return;

    const updateLocationStatus = () => {
        const hasLocation = latitude.value !== '' && longitude.value !== '';
        clearButton.hidden = !hasLocation;
        status.textContent = hasLocation
            ? `Titik lokasi tersimpan (${Number(latitude.value).toFixed(5)}, ${Number(longitude.value).toFixed(5)}).`
            : 'Izinkan akses lokasi di browser. Fitur ini memerlukan HTTPS atau localhost.';
    };

    button.addEventListener('click', () => {
        if (!navigator.geolocation) {
            status.textContent = 'Browser ini tidak mendukung fitur lokasi.';
            return;
        }

        button.disabled = true;
        status.textContent = 'Sedang mengambil lokasi...';
        navigator.geolocation.getCurrentPosition(
            (position) => {
                latitude.value = String(position.coords.latitude);
                longitude.value = String(position.coords.longitude);
                button.disabled = false;
                updateLocationStatus();
            },
            (error) => {
                const messages = {
                    1: 'Izin lokasi ditolak. Ubah izin lokasi di pengaturan browser.',
                    2: 'Lokasi tidak tersedia. Coba lagi atau isi alamat secara manual.',
                    3: 'Permintaan lokasi terlalu lama. Coba lagi atau isi alamat secara manual.',
                };
                button.disabled = false;
                status.textContent = messages[error.code] ?? 'Lokasi gagal diambil. Coba lagi atau isi alamat secara manual.';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 },
        );
    });

    clearButton.addEventListener('click', () => {
        latitude.value = '';
        longitude.value = '';
        updateLocationStatus();
    });

    updateLocationStatus();
});

document.querySelectorAll('[data-carousel]').forEach((carousel) => {
    const slides = Array.from(carousel.querySelectorAll('[data-slide]'));
    const dots = Array.from(carousel.parentElement.querySelectorAll('[data-dot]'));
    const counter = carousel.querySelector('[data-current]');

    if (slides.length < 2) return;

    let activeIndex = 0;
    let timer;

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => {
            slide.classList.toggle('is-active', slideIndex === activeIndex);
            slide.setAttribute('aria-hidden', slideIndex === activeIndex ? 'false' : 'true');
        });
        dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === activeIndex));
        if (counter) counter.textContent = String(activeIndex + 1);
    };

    const stop = () => window.clearInterval(timer);
    const start = () => {
        stop();
        if (slides.length > 2) timer = window.setInterval(() => showSlide(activeIndex + 1), 4000);
    };

    carousel.querySelector('[data-prev]')?.addEventListener('click', () => {
        showSlide(activeIndex - 1);
        start();
    });
    carousel.querySelector('[data-next]')?.addEventListener('click', () => {
        showSlide(activeIndex + 1);
        start();
    });
    dots.forEach((dot) => dot.addEventListener('click', () => {
        showSlide(Number(dot.dataset.dot));
        start();
    }));
    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    carousel.addEventListener('focusin', stop);
    carousel.addEventListener('focusout', start);

    showSlide(0);
    start();
});
