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
    <?php 
        $user_id = get_current_user_id();
        $data = ozh_get_subscriptions_data( $user_id );
    ?>
    <ul class="list-unstyled list-count">
        <li>
            <a href="<?php  ?>">
                <span class="title"><?php echo 'Name: '; ?></span> <span class="count"><?php echo strtoupper( get_the_title($data['product_id']) ); ?></span>
            </a>
        </li>
        <li>
            <a href="<?php  ?>">
                <span class="title"><?php echo 'Status: '; ?></span> <span class="count"><?php echo $data['status']; ?></span>
            </a>
        </li>
        <?php if( ($data['product_id'] == '' ) && ($data['status'] == 'inactive' ) ) : ?>

        <li class="subscriptions-pack">
            <a href="<?php echo home_url().'/retailer-packages'; ?>">
                <span class="choose-subscription">Choose a Retailer Package to Active</span>
            </a>
        </li>
        
        <?php endif; ?>
        <li>
        	<a href="<?php  ?>">
            	<span class="title"><?php echo 'Quantity of products: '; ?></span> <span class="count"><?php echo $data['products_limit']; ?></span>
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
</div> <!-- .orders -->