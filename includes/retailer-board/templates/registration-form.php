<?php
/**
 * Registarion Retailer Form Template
 *
 */
?>
<div class="account-register-retailer">
	<form method="post" class="woocommerce-form woocommerce-form-register register">
		<p><?php echo $register; ?></p>
		<p class="form-row form-group form-row-wide">
			<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="" />
		</p>
		<p class="form-row form-group form-row-wide">
			<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
		</p>

		<div class="show_if_seller" style="<?php echo esc_attr( $role_style ); ?>">
		    <div class="split-row form-row-wide">
		        <p class="form-row form-group">
		            <label for="first-name"><?php esc_html_e( 'First Name', 'dokan-lite' ); ?> <span class="required">*</span></label>
		            <input type="text" class="input-text form-control" name="fname" id="first-name" value="" required="required" />
		        </p>
		        <p class="form-row form-group">
		            <label for="last-name"><?php esc_html_e( 'Last Name', 'dokan-lite' ); ?> <span class="required">*</span></label>
		            <input type="text" class="input-text form-control" name="lname" id="last-name" value="" required="required" />
		        </p>
		    </div>

		    <p class="form-row form-group form-row-wide">
		        <label for="company-name"><?php esc_html_e( 'Shop Name', 'dokan-lite' ); ?> <span class="required">*</span></label>
		        <input type="text" class="input-text form-control" name="shopname" id="company-name" value="" required="required" />
		    </p>
		    <p class="form-row form-group form-row-wide">
		        <label for="seller-url" class="pull-left"><?php esc_html_e( 'Shop URL', 'dokan-lite' ); ?> <span class="required">*</span></label>
		        <strong id="url-alart-mgs" class="pull-right"></strong>
		        <input type="url" class="input-text form-control" name="shopurl" id="seller-url" value="" required="required" />
		        <small><?php echo 'e.g.  '.esc_url( home_url()); ?>/<strong id="url-alart"></strong></small>
		    </p>
		    <p class="form-row form-group form-row-wide">
		        <label for="shop-phone"><?php esc_html_e( 'Phone Number', 'dokan-lite' ); ?><span class="required">*</span></label>
		        <input type="text" class="input-text form-control" name="phone" id="shop-phone" value="" required="required" />
		    </p>
		</div>

        <input type="tel" name="role" value="retailer">
		<p class="woocommerce-FormRow form-row">
			<button type="submit" class="ozh-register button" name="retailer_register" value="Register">Register</button>
		</p>
	</form>
</div>
