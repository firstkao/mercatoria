<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main">Lewati ke konten</a>

<div class="promo-bar">
    <div class="container">
        Gunakan kode promo <strong class="promo-code">NEWWEB</strong> dan nikmati potongan <strong>Rp25.000</strong> dengan minimal pembelian <strong>Rp150.000</strong>.
    </div>
</div>

<header id="site-header" class="site-header">
    <div class="container header-inner">

        <div class="header-left">
            <nav class="primary-nav" aria-label="Menu utama">
                <?php wp_nav_menu( [ 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'menu', 'fallback_cb' => false, 'depth' => 2 ] ); ?>
            </nav>
        </div>

        <div class="header-center">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo">
                <?php if ( has_custom_logo() ) the_custom_logo(); else echo '<span class="site-title">' . esc_html( get_bloginfo( 'name' ) ) . '</span>'; ?>
            </a>
        </div>

        <div class="header-right">
            <button type="button" class="header-action" aria-label="Cari"><?php echo merc_icon( 'search', 20 ); ?></button>

            <div class="header-social">
                <?php foreach ( merc_get_social_links() as $key => $s ) : ?>
                    <a href="<?php echo esc_url( $s['url'] ); ?>" class="header-action" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $s['label'] ); ?>">
                        <?php echo merc_icon( $key, 18 ); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <button type="button" class="cart-trigger" data-cart-open>
                <span class="cart-subtotal" data-cart-subtotal>Rp0</span>
                <span class="cart-icon-wrap">
                    <?php echo merc_icon( 'cart', 20 ); ?>
                    <span class="cart-count" data-cart-count>0</span>
                </span>
            </button>

            <a href="<?php echo esc_url( merc_url( 'akun' ) ); ?>" class="header-action" aria-label="Akun">
                <?php echo merc_icon( 'user', 20 ); ?>
            </a>
        </div>

    </div>
</header>

<?php get_template_part( 'template-parts/cart-drawer' ); ?>

<main id="main" class="site-main">