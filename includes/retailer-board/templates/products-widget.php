<?php

/**
 * Dokan Dashboard Template
 *
 * Dokan Dashboard Product widget template
 *
 * @since 2.4
 *
 * @package dokan
 */

?>

<div class="dashboard-widget products">
    <div class="widget-title">
        <i class="fa fa-briefcase" aria-hidden="true"></i> <?php esc_html_e( 'Products', 'dokan-lite' ); ?>
    </div>

    <ul class="list-unstyled list-count">
        <li>
            <a href="<?php echo esc_url( $products_url ); ?>">
                <span class="title"><?php esc_html_e( 'Total', 'dokan-lite' ); ?></span> <span class="count"><?php echo esc_attr( $post_counts->total ); ?></span>
            </a>
        </li>
    </ul>
</div> <!-- .products -->
