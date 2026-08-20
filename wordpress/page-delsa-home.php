<?php
/**
 * Template Name: Delsa Homepage (New)
 * Description: صفحه اصلی مدرن کلینیک دلسا — HTML + Tailwind CDN
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
  <?php wp_head(); ?>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { vazir: ['Vazirmatn', 'Tahoma', 'sans-serif'] },
          colors: {
            delsa: {
              navy: '#1a365d', 'navy-light': '#234876', gold: '#c9a227',
              'gold-light': '#d4b84a', sage: '#5a8f7b', 'sage-light': '#7aad96',
              gray: '#f5f7fa', 'gray-dark': '#64748b',
            }
          },
        },
      },
    }
  </script>
  <style>
    html { scroll-behavior: smooth; }
    body { font-family: 'Vazirmatn', Tahoma, sans-serif; }
    .animate-on-scroll { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
    .animate-on-scroll.visible { opacity: 1; transform: translateY(0); }
    .hero-pattern {
      background-image:
        radial-gradient(circle at 20% 50%, rgba(201,162,39,0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(90,143,123,0.1) 0%, transparent 40%),
        linear-gradient(135deg, #1a365d 0%, #234876 50%, #1a365d 100%);
    }
    .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(26,54,93,0.12); }
    /* Hide default WP theme header/footer when using this template */
    #masthead, #colophon, .site-header, .site-footer { display: none !important; }
  </style>
</head>
<body <?php body_class('bg-white text-delsa-navy antialiased'); ?>>

<?php
// Include the main content from index.html body sections
// Upload index.html content or use get_template_part
include get_stylesheet_directory() . '/delsa-home-content.php';
?>

<script>
  document.querySelectorAll('.animate-on-scroll').forEach(el => {
    new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 }).observe(el);
  });
</script>
<?php wp_footer(); ?>
</body>
</html>
