document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');
    if (!toggle) return;

    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!input) return;

    const shouldReveal = input.type === 'password';
    input.type = shouldReveal ? 'text' : 'password';
    toggle.setAttribute('aria-label', shouldReveal ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    toggle.setAttribute('aria-pressed', shouldReveal ? 'true' : 'false');
    toggle.querySelectorAll('[data-password-icon]').forEach((icon) => {
        icon.hidden = icon.dataset.passwordIcon !== (shouldReveal ? 'hide' : 'show');
    });
});
