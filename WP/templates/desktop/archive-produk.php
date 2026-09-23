<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$current_min = isset( $_GET['min'] ) ? (int) $_GET['min'] : 0;
$current_max = isset( $_GET['max'] ) ? (int) $_GET['max'] : 0;
$current_sort = $_GET['sort'] ?? 'default';
$price_range = merc_get_price_range();

$min_val = $current_min ?: $price_range['min'];
$max_val = $current_max ?: $price_range['max'];

$active_cat = is_tax( 'kategori_produk' ) ? get_queried_object() : null;
?>

<div class="container archive-wrapper">

    <div class="archive-layout">

        <!-- ============ SIDEBAR ============ -->
        <aside class="archive-sidebar">

            <!-- Kategori -->
            <div class="widget-box">
                <h3 class="widget-title">Kategori Game</h3>
                <?php
                $terms = get_terms( [
                    'taxonomy'   => 'kategori_produk',
                    'hide_empty' => true,
                    'parent'     => 0,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ] );

                if ( ! is_wp_error( $terms ) && $terms ) : ?>
                    <ul class="category-list">
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
                <?php else : ?>
                    <p class="widget-empty">Belum ada kategori.</p>
                <?php endif; ?>
            </div>

            <!-- Filter Harga -->
            <div class="widget-box">
                <h3 class="widget-title">Filter Harga</h3>

                <div class="price-range-labels">
                    <span data-price-min-label><?php echo esc_html( merc_format_rupiah( $min_val ) ); ?></span>
                    <span data-price-max-label><?php echo esc_html( merc_format_rupiah( $max_val ) ); ?></span>
                </div>

                <form method="get" class="price-filter-form" data-price-slider>
                    <div class="price-slider">
                        <div class="slider-track"></div>
                        <div class="slider-fill"></div>
                        <input type="range"
                               data-price-min
                               min="<?php echo esc_attr( $price_range['min'] ); ?>"
                               max="<?php echo esc_attr( $price_range['max'] ); ?>"
                               step="1000"
                               value="<?php echo esc_attr( $min_val ); ?>">
                        <input type="range"
                               data-price-max
                               min="<?php echo esc_attr( $price_range['min'] ); ?>"
                               max="<?php echo esc_attr( $price_range['max'] ); ?>"
                               step="1000"
                               value="<?php echo esc_attr( $max_val ); ?>">
                    </div>

                    <!-- Hidden inputs buat submit -->
                    <input type="hidden" name="min" value="<?php echo esc_attr( $min_val ); ?>" data-price-min-hidden>
                    <input type="hidden" name="max" value="<?php echo esc_attr( $max_val ); ?>" data-price-max-hidden>

                    <?php if ( $current_sort !== 'default' ) : ?>
                        <input type="hidden" name="sort" value="<?php echo esc_attr( $current_sort ); ?>">
                    <?php endif; ?>

                    <!-- Pertahankan sort saat submit -->
                    <button type="submit" class="btn btn-block btn-sm">Terapkan</button>

                    <?php if ( $current_min || $current_max ) : ?>
                        <a href="<?php echo esc_url( remove_query_arg( [ 'min', 'max' ] ) ); ?>" class="price-reset">Reset filter</a>
                    <?php endif; ?>
                </form>
            </div>

        </aside>

        <!-- ============ MAIN CONTENT ============ -->
        <main class="archive-main">

            <!-- Header -->
            <header class="archive-header">
                <?php merc_breadcrumb(); ?>
                <h1 class="archive-title">
                    <?php
                    if ( $active_cat ) {
                        echo esc_html( $active_cat->name );
                    } else {
                        echo 'Semua Produk';
                    }
                    ?>
                </h1>
                <?php if ( $active_cat && $active_cat->description ) : ?>
                    <p class="archive-desc"><?php echo esc_html( $active_cat->description ); ?></p>
                <?php endif; ?>
            </header>

            <!-- Toolbar -->
            <div class="archive-toolbar">
                <span class="result-count">
                    <?php
                    global $wp_query;
                    $total = (int) $wp_query->found_posts;
                    printf( 'Menampilkan %d hasil', $total );
                    ?>
                </span>

                <form method="get" class="sort-form">
                    <?php
                    // Pertahankan filter harga
                    if ( $current_min ) echo '<input type="hidden" name="min" value="' . esc_attr( $current_min ) . '">';
                    if ( $current_max ) echo '<input type="hidden" name="max" value="' . esc_attr( $current_max ) . '">';
                    ?>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="default"    <?php selected( $current_sort, 'default' ); ?>>Terbaru</option>
                        <option value="harga_asc"  <?php selected( $current_sort, 'harga_asc' ); ?>>Harga: Rendah → Tinggi</option>
                        <option value="harga_desc" <?php selected( $current_sort, 'harga_desc' ); ?>>Harga: Tinggi → Rendah</option>
                        <option value="nama_asc"   <?php selected( $current_sort, 'nama_asc' ); ?>>Nama: A → Z</option>
                        <option value="nama_desc"  <?php selected( $current_sort, 'nama_desc' ); ?>>Nama: Z → A</option>
                    </select>
                </form>
            </div>

            <!-- Grid -->
            <?php if ( have_posts() ) : ?>
                <div class="product-grid">
                    <?php while ( have_posts() ) : the_post(); merc_product_card(); endwhile; ?>
                </div>

                <?php
                the_posts_pagination( [
                    'mid_size'  => 2,
                    'prev_text' => '←',
                    'next_text' => '→',
                ] );
                ?>
            <?php else : ?>
                <div class="empty-state">
                    <p>Gak ada produk yang cocok.</p>
                    <p class="empty-hint">Coba ubah filter atau <a href="<?php echo esc_url( get_post_type_archive_link( 'produk' ) ); ?>">lihat semua produk</a>.</p>
                </div>
            <?php endif; ?>

        </main>

    </div>

</div>

<?php get_footer(); ?>