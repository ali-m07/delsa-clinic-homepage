/**
 * WPCode Snippet — فوری: درست کردن فوتر (حتی با کد قدیمی HTML)
 * نوع: CSS Snippet
 * محل: Site Wide Header
 *
 * بعد از paste کامل elementor-paste.html v2 این snippet را غیرفعال کنید.
 */

footer#contact,
footer#contact.bg-ink-950,
.delsa-footer-wrap {
  background-color: #122f5c !important;
  background: #122f5c !important;
  color: #ffffff !important;
  width: 100vw !important;
  max-width: 100vw !important;
  margin-left: calc(50% - 50vw) !important;
  margin-right: calc(50% - 50vw) !important;
  padding: 2.5rem 0 3rem !important;
  box-sizing: border-box !important;
}

footer#contact .site-footer__grid,
footer#contact .grid {
  display: grid !important;
  grid-template-columns: 1fr !important;
  gap: 2rem !important;
}

@media (min-width: 1024px) {
  footer#contact .site-footer__grid,
  footer#contact .grid.lg\:grid-cols-3 {
    grid-template-columns: 1fr 1fr 1fr !important;
  }
}

footer#contact h4 {
  color: rgba(76, 201, 192, 0.65) !important;
  font-size: 11px !important;
  font-weight: 600 !important;
  letter-spacing: 0.08em !important;
  text-transform: uppercase !important;
}

footer#contact p,
footer#contact li,
footer#contact span,
footer#contact a {
  color: rgba(255, 255, 255, 0.65) !important;
}

footer#contact .footer-brand > p:first-of-type,
footer#contact .site-footer__contact-title {
  color: #ffffff !important;
}

footer#contact .site-footer__address,
footer#contact .footer-brand__desc {
  color: rgba(255, 255, 255, 0.45) !important;
}

footer#contact .site-footer__phone-main,
footer#contact .site-footer__email-link {
  color: rgba(255, 255, 255, 0.75) !important;
}

footer#contact ul {
  list-style: none !important;
  padding: 0 !important;
  margin: 0 !important;
}

footer#contact .footer-maps-btn {
  display: inline-flex !important;
  background: #4CC9C0 !important;
  color: #122f5c !important;
  padding: 0.5rem 1rem !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  text-decoration: none !important;
  margin-top: 0.75rem !important;
}

footer#contact .footer-map iframe,
footer#contact .footer-map {
  width: 100% !important;
  min-height: 220px !important;
  border: 0 !important;
}

footer#contact .site-footer__bottom,
footer#contact .site-footer__bottom p {
  border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
  padding-top: 1.25rem !important;
  color: rgba(255, 255, 255, 0.35) !important;
  font-size: 12px !important;
}
