<?php ?>
<div class="package-meta-container">
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
                    <input type="text" class="adm-retailer-input" readonly placeholder="Products Number / Month">
                </td>
                <td>
                    <input type="text" class="adm-retailer-input" name="quantity"  placeholder="enter number or 'unlimited'" value="<?php echo get_post_meta($post->ID, 'quantity_of_products', true); ?>">
                </td>
            </tr>
            <tr>
                <td class="left">
                    <input type="text" class="adm-retailer-input" readonly placeholder="Price / Month">
                </td>
                <td>
                    <input type="text" class="adm-retailer-input" name="price"  placeholder="$" value="<?php echo get_post_meta($post->ID, 'price', true); ?>">
                </td>
            </tr>
            <tr>
                <td class="left">
                    <input type="text" class="adm-retailer-input" readonly placeholder="Trial / Days">
                </td>
                <td>
                    <input type="text" class="adm-retailer-input" name="trial"  placeholder="enter quantity of days or '0'" value="<?php echo get_post_meta($post->ID, 'trial', true); ?>">
                </td>
            </tr>     
        </tbody>
    </table>       
</div>      
<?php 
