<?php
/**
 *  Dashboard Widget Template
 *
 *  Get dokan dashboard widget template
 *
 *  @since 2.4
 *
 *  @package dokan
 *
 */
?>

<div class="dashboard-widget orders">
    <div class="widget-title">
        <i class="fa fa-briefcase" aria-hidden="true"></i> <?php echo 'My Packages'; ?>
    </div>
    <?php $sub_data = get_subscriptions_data(); ?>
    <?php foreach ( $sub_data as $data ) : ?>

        <ul class="list-unstyled list-count">
            <li>
                <a href="<?php  ?>">
                    <span class="title"><?php echo 'Name: '; ?></span> <span class="count"><?php echo strtoupper( $data['product_name'] ); ?></span>
                </a>
            </li>
            <li>
                <a href="<?php  ?>">
                    <span class="title"><?php echo 'Status: '; ?></span> <span class="count"><?php echo $data['status']; ?></span>
                </a>
            </li>
            <li>
            	<a href="<?php  ?>">
                	<span class="title"><?php echo 'Quantity of products: '; ?></span> <span class="count"><?php echo $data['products_count']; ?></span>
                </a>
            </li>
            <li>
            	<a href="<?php  ?>">
                	<span class="title"><?php echo 'Start: '; ?></span> <span class="count"><?php echo $data['start']; ?></span>
                </a>
            </li>
            <li>
            	<a href="<?php  ?>">
                	<span class="title"><?php echo 'Finish: '; ?></span> <span class="count"><?php echo $data['finish']; ?></span>
                </a>
            </li>
        </ul>
        <?php if( ($data['product_name'] == '-' ) && ($data['status'] == '-' ) ) :

            $category = get_term_by( 'slug', 'retailer-packages', 'product_cat' );
            $category_id = $category->term_id;
            $args = array( 'include' => $category_id, 'style' => 'none', 'taxonomy' => 'product_cat', 'title_li' => '');
            ?>
            <span class="subscription_title">You have not packages.&emsp;Avaliable packages for buy below:</span>
            <div class="subscriptions-pack">
                <?php echo wp_list_categories( $args ); ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div> <!-- .orders -->