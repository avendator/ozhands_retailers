<?php
/*
* Custom_emails classes activate
*/

if (!defined('ABSPATH')) {
    exit;
}

class OZH_custom_emails{

    function __construct(){

        if(!defined('OZH_EMAIL_DIR')){
            @define('OZH_EMAIL_DIR', __DIR__);
        }
        add_filter('woocommerce_email_classes', array($this, 'ozh_add_custom_emails_classes'),100,1);
    }

    function ozh_add_custom_emails_classes($email_classes)    {
		require_once('includes/package_ends.php');
        require_once('includes/package_ended.php');
		$email_classes['OZH_package_ends'] = new OZH_package_ends();
        $email_classes['OZH_package_ended'] = new OZH_package_ended();
        return $email_classes;
    }
}
?>