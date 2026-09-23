<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main">Lewati ke konten</a>

<header class="m-header">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="m-logo">
        <?php if ( has_custom_logo() ) the_custom_logo(); else bloginfo( 'name' ); ?>
    </a>
    <div class="m-header-actions">
        <a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" class="m-icon-btn" aria-label="Cari"><?php echo merc_icon( 'search', 20 ); ?></a>
        <button type="button" class="m-icon-btn cart-trigger" data-cart-open aria-label="Keranjang">
            <?php echo merc_icon( 'cart', 20 ); ?>
            <span class="cart-count" data-cart-count>0</span>
        </button>
    </div>
</header>

<?php get_template_part( 'template-parts/cart-drawer' ); ?>

<main id="main" class="site-main">