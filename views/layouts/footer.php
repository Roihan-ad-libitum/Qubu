<?php

$footerWaNumber  = defined('QUBU_FOOTER_WHATSAPP') ? QUBU_FOOTER_WHATSAPP : '081289917510';
$footerEmailAddr = defined('QUBU_FOOTER_EMAIL') ? QUBU_FOOTER_EMAIL : 'roihan@gmail.com';
$footerGithubUrl = defined('QUBU_FOOTER_GITHUB') ? QUBU_FOOTER_GITHUB : 'https://github.com/roihan-ad-libitum';

$footerWaHref   = function_exists('buildWhatsAppUrl') ? buildWhatsAppUrl($footerWaNumber) : ('https://wa.me/62' . ltrim($footerWaNumber, '0'));
$footerMailHref = function_exists('buildMailtoUrl') ? buildMailtoUrl($footerEmailAddr) : ('mailto:' . $footerEmailAddr);
?>
    <footer class="qubu-global-footer" role="contentinfo">
        <div class="qubu-footer-inner">
            <div class="qubu-footer-brand">
                <span class="qubu-footer-logo">QUBU</span>
                <span class="qubu-footer-tagline">Platform Info Lomba &amp; Kolaborasi Tim Mahasiswa Fasilkom</span>
            </div>

            <div class="qubu-footer-links" aria-label="Kontak dan Media Sosial Qubu">

                <a
                    href="<?= e($footerWaHref) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="qubu-footer-link qubu-icon-wa"
                    aria-label="Hubungi via WhatsApp"
                    title="WhatsApp"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                    </svg>
                </a>

                <a
                    href="<?= e($footerMailHref) ?>"
                    class="qubu-footer-link qubu-icon-mail"
                    aria-label="Kirim Email"
                    title="Email"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                </a>

                <a
                    href="<?= e($footerGithubUrl) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="qubu-footer-link qubu-icon-gh"
                    aria-label="Kunjungi GitHub"
                    title="GitHub"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 0.5C5.37 0.5 0 5.87 0 12.5c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.295 24 17.795 24 12.5 24 5.87 18.63 0.5 12 0.5z"/>
                    </svg>
                </a>
            </div>
        </div>
    </footer>

    <?php if (isset($extraScript)): ?>
        <script src="<?= url($extraScript) ?>"></script>
    <?php endif; ?>
</body>
</html>
