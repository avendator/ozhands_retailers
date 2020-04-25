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
	        <label for="ret-email"><?php echo 'Email address'; ?> <span class="required">*</span></label>
	        <input type="email" class="input-text form-control" name="email" id="ret-email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr($_POST['email']); ?>" required="required" />
	        <label generated="true" class="reg_email_error"></label>
    	</p>
	    <p class="form-row form-group form-row-wide">
	        <label for="ret-password"><?php echo 'Password'; ?> <span class="required">*</span></label>
	        <input type="password" class="input-text form-control" name="password" id="ret-password" value="<?php if ( ! empty( $_POST['password'] ) ) echo esc_attr( $_POST['password'] ); ?>" required="required" minlength="6" />
    	</p>

		<div class="show_if_retailer">
		    <div class="split-row form-row-wide">
		        <p class="form-row form-group">
		            <label for="ret-first-name"><?php echo'First Name'; ?> <span class="required">*</span></label>
		            <input type="text" class="input-text form-control" name="fname" id="ret-first-name" value="<?php if ( ! empty( $_POST['fname'] ) ) echo esc_attr( $_POST['fname'] ); ?>" required="required" />
		        </p>
		        <p class="form-row form-group">
		            <label for="ret-last-name"><?php echo 'Last Name'; ?> <span class="required">*</span></label>
		            <input type="text" class="input-text form-control" name="lname" id="ret-last-name" value="<?php if ( ! empty( $_POST['lname'] ) ) echo esc_attr( $_POST['lname'] ); ?>" required="required" />
		        </p>
		    </div>

		    <p class="form-row form-group form-row-wide">
		        <label for="ret-company-name"><?php echo 'Shop Name'; ?> <span class="required">*</span></label>
		        <input type="text" class="input-text form-control" name="shopname" id="ret-company-name" value="<?php if ( ! empty( $_POST['shopname'] ) ) echo esc_attr( $_POST['shopname'] ); ?>" required="required" />
		        <small><?php echo 'the same that your login - can not to change it in the future'; ?></small>
		    </p>
		    <p class="form-row form-group form-row-wide">
		        <label for="retailer-url" class="pull-left"><?php echo 'Shop URL'; ?> <span class="required">*</span></label>
		        <strong id="url-alart-mgs" class="pull-right"></strong>
		        <input type="url" class="input-text form-control" name="shopurl" id="retailer-url" value="<?php if ( ! empty( $_POST['shopurl'] ) ) echo esc_attr( $_POST['shopurl'] ); ?>" required="required" />
		        <small><?php echo 'e.g.  '.esc_url( home_url()); ?>/<strong id="url-alart"></strong></small>
		    </p>
		    <p class="form-row form-group form-row-wide">
		        <label for="retailer-phone"><?php echo 'Phone Number'; ?><span class="required">*</span></label>
		        <input type="tel" class="input-text form-control" name="phone" id="retailer-phone" value="<?php if ( ! empty( $_POST['phone'] ) ) echo esc_attr( $_POST['phone'] ); ?>" required="required" />
		    </p>
		</div>

        <input type="hidden" name="role" value="retailer">
		<p class="woocommerce-FormRow form-row">
			<button type="submit" class="ozh-register button" name="retailer_register" value="Register">Register</button>
		</p>
	</form>
</div>

<script type="text/javascript">
    (function($) {
        $('#retailer-phone').keydown(function(e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 107, 109, 110, 187, 189, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                     // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    })(jQuery);
</script>
