<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$current_min  = isset( $_GET['min'] ) ? (int) $_GET['min'] : 0;
$current_max  = isset( $_GET['max'] ) ? (int) $_GET['max'] : 0;
$current_sort = $_GET['sort'] ?? 'default';
$price_range  = merc_get_price_range();

$min_val = $current_min ?: $price_range['min'];
$max_val = $current_max ?: $price_range['max'];

$active_cat = is_tax( 'kategori_produk' ) ? get_queried_object() : null;
?>

<div class="m-container archive-wrapper-m">

    <!-- Header -->
    <header class="archive-header-m">
        <?php merc_breadcrumb(); ?>
        <h1 class="archive-title-m">
            <?php echo $active_cat ? esc_html( $active_cat->name ) : 'Semua Produk'; ?>
        </h1>
    </header>

    <!-- Toolbar -->
    <div class="archive-toolbar-m">
        <button type="button" class="filter-trigger" data-filter-open>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M3 17v2h6v-2zM3 5v2h10V5zm10 16v-2h8v-2h-8v-2h-2v6zM7 9v2H3v2h4v2h2V9zm14 4v-2H11v2zm-6-4h2V7h4V5h-4V3h-2z"/></svg>
            Filter
        </button>

        <form method="get" class="sort-form-m">
            <?php
            if ( $current_min ) echo '<input type="hidden" name="min" value="' . esc_attr( $current_min ) . '">';
            if ( $current_max ) echo '<input type="hidden" name="max" value="' . esc_attr( $current_max ) . '">';
            ?>
            <select name="sort" onchange="this.form.submit()">
                <option value="default"    <?php selected( $current_sort, 'default' ); ?>>Terbaru</option>
                <option value="harga_asc"  <?php selected( $current_sort, 'harga_asc' ); ?>>Termurah</option>
                <option value="harga_desc" <?php selected( $current_sort, 'harga_desc' ); ?>>Termahal</option>
                <option value="nama_asc"   <?php selected( $current_sort, 'nama_asc' ); ?>>A-Z</option>
            </select>
        </form>
    </div>

    <!-- Grid -->
    <?php if ( have_posts() ) : ?>
        <div class="product-grid">
            <?php while ( have_posts() ) : the_post(); merc_product_card(); endwhile; ?>
        </div>

        <?php the_posts_pagination( [ 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ] ); ?>
    <?php else : ?>
        <div class="empty-state">
            <p>Gak ada produk.</p>
        </div>
    <?php endif; ?>

</div>

<!-- ============ FILTER DRAWER (offcanvas) ============ -->
<div class="filter-drawer-backdrop" data-filter-backdrop></div>

<aside class="filter-drawer" data-filter-drawer aria-hidden="true">

    <div class="filter-drawer-header">
        <h3>Filter</h3>
        <button type="button" class="filter-drawer-close" data-filter-close aria-label="Tutup">
            <?php echo merc_icon( 'close', 20 ); ?>
        </button>
    </div>

    <div class="filter-drawer-body">

        <!-- Kategori -->
        <div class="filter-section">
            <h4>Kategori Game</h4>
            <?php
            $terms = get_terms( [
                'taxonomy'   => 'kategori_produk',
                'hide_empty' => true,
                'parent'     => 0,
                'orderby'    => 'count',
                'order'      => 'DESC',
                'number'     => 20,
            ] );

            if ( ! is_wp_error( $terms ) && $terms ) : ?>
                <ul class="filter-cat-list">
                    <?php foreach ( $terms as $t ) :
                        $is_active = $active_cat && $active_cat->term_id === $t->term_id;
                        ?>
                        <li class="<?php echo $is_active ? 'is-active' : ''; ?>">
                            <a href="<?php echo esc_url( get_term_link( $t ) ); ?>">
                                <span><?php echo esc_html( $t->name ); ?></span>
                                <span class="count"><?php echo (int) $t->count; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Filter Harga -->
        <div class="filter-section">
            <h4>Filter Harga</h4>

            <div class="price-range-labels">
                <span data-price-min-label><?php echo esc_html( merc_format_rupiah( $min_val ) ); ?></span>
                <span data-price-max-label><?php echo esc_html( merc_format_rupiah( $max_val ) ); ?></span>
            </div>

            <form method="get" class="price-filter-form" data-price-slider>
                <div class="price-slider">
                    <div class="slider-track"></div>
                    <div class="slider-fill"></div>
                    <input type="range" data-price-min
                           min="<?php echo esc_attr( $price_range['min'] ); ?>"
                           max="<?php echo esc_attr( $price_range['max'] ); ?>"
                           step="1000" value="<?php echo esc_attr( $min_val ); ?>">
                    <input type="range" data-price-max
                           min="<?php echo esc_attr( $price_range['min'] ); ?>"
                           max="<?php echo esc_attr( $price_range['max'] ); ?>"
                           step="1000" value="<?php echo esc_attr( $max_val ); ?>">
                </div>

                <input type="hidden" name="min" value="<?php echo esc_attr( $min_val ); ?>" data-price-min-hidden>
                <input type="hidden" name="max" value="<?php echo esc_attr( $max_val ); ?>" data-price-max-hidden>

                <?php if ( $current_sort !== 'default' ) : ?>
                    <input type="hidden" name="sort" value="<?php echo esc_attr( $current_sort ); ?>">
                <?php endif; ?>

                <button type="submit" class="btn btn-accent btn-block mt-3">Terapkan Filter</button>

                <?php if ( $current_min || $current_max ) : ?>
                    <a href="<?php echo esc_url( remove_query_arg( [ 'min', 'max' ] ) ); ?>" class="btn btn-outline btn-block mt-1">Reset</a>
                <?php endif; ?>
            </form>
        </div>

    </div>

</aside>

<?php get_footer(); ?>