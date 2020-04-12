<?php ?>
<div class="package-meta-container">
    <form method="post">
        <table class="package-meta-list">
            <thead>
                <tr>
                    <th class="left">Name</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="left">
                        <input type="text" class="adm-retailer-input" readonly placeholder="Quantity of products">
                    </td>
                    <td>
                        <input type="text" class="adm-retailer-input" name="quantity"  placeholder="enter number or 'unlimited'" value="<?php echo get_post_meta($post->ID, 'quantity_of_products', true); ?>">
                    </td>
                </tr>
                <tr>
                    <td class="left">
                        <input type="text" class="adm-retailer-input" readonly placeholder="Price">
                    </td>
                    <td>
                        <input type="text" class="adm-retailer-input" name="price"  placeholder="$" value="<?php echo get_post_meta($post->ID, 'price', true); ?>">
                    </td>
                </tr>
                <tr>
                    <td class="left">
                        <input type="text" class="adm-retailer-input" readonly placeholder="Trial">
                    </td>
                    <td>
                        <input type="text" class="adm-retailer-input" name="trial"  placeholder="enter quantity of months or '0'" value="<?php echo get_post_meta($post->ID, 'trial', true); ?>">
                    </td>
                </tr>
                <tr>
                    <td></td>                   
                    <td>
                        <input type="submit" id="save-rtp-meta" name="save_package_meta" value="Save Package Meta">
                    </td>
                </tr>      
            </tbody>
            <input type="hidden" name="post_id" data-post-id="<?= $post->ID ?>">
        </table>       
    </form>
</div>      
<?php 
