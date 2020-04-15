<?php

/**
 * Importing of Products - widget
 */
?>
<?php if ( $_GET['percentage'] ) : ?>
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

