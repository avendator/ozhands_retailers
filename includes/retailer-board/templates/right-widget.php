<?php

/**
 * Importing of Products - widget
 */
?>
<?php
$user_id = get_current_user_id();
$ozhands_connection = get_user_meta( $user_id, 'ozh_zip_name', true );
$ozhands_connection = base64_encode( $ozhands_connection );

 if ( $_GET['percentage'] ) : ?>
	<div class="dashboard-widget sells-graph">
	    <div class="widget-title"> <i class="fa fa-briefcase"></i> <?php echo 'Importing of Products'; ?></div>
			<ul class="list-unstyled list-count">
		        <li>
					<span class="title">Percentage:</span> <span class="count"><?php echo $_GET['percentage']; ?></span>
		        </li>
		        <li>
					<span class="title">Imported Products:</span> <span class="count"><?php echo $_GET['products-imported']; ?></span>
		        </li>
		        <li>
					<span class="title">Failed Products:</span> <span class="count"><?php echo $_GET['products-failed']; ?></span>
		        </li>
		        <li>
					<span class="title">Updated Products:</span> <span class="count"><?php echo $_GET['products-updated']; ?></span>
		        </li>
		        <li>
					<span class="title">Skipped Products:</span> <span class="count"><?php echo $_GET['products-skipped']; ?></span>
		        </li>
			</ul>
	</div> <!-- .sells-graph -->
<?php endif ?>
<?php 
/**
 * Donwload the plugin "ozhands_connection"
 */
?>
<div class="connection-link-cont">
	<a class="connection-link" href="<?php echo plugins_url().'/ozhands_retailers/zip_archives/'.$ozhands_connection.'/ozhands_connection.zip'; ?>" download>ItemSync Plugin</a>
</div>

<?php
/**
 * Additional Information
 */
?>

<div class="dashboard-widget additional-info" style="margin-top: 18px;">
    <div class="widget-title">
    </div>

    <ul class="list-unstyled">
        <li id="add-info">
        	<span class="title" style="color: #1A1919;">- Sync/Add All Products Instantly from your site</span>
        </li>
        <li id="add-info">
            <span class="title" style="color: #1A1919;">- Automatic Update when updated in your site</span>
        </li>
        <li id="add-info">
            <span class="title" style="color: #1A1919;">- Secure Connection</span>
        </li>       
        <li id="add-info">
        	<a href="<?php echo home_url().'/itemsync/'; ?>">
            	<span class="title learn-more">Learn More</span>
            </a>
        </li>
    </ul>
</div> <!-- .orders -->
