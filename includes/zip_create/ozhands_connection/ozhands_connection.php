<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       Ozhands Connection
 * Description:       Plugin to connection Wordpress/Woocommerce site to https://www.ozhands.com.au/
 * Version:           1.0.0
 * Author:            upsite.top
 * Author URI:        /team
 */
 
require_once plugin_dir_path(__FILE__) . 'includes/admin_functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/requests_functions.php';
require_once plugin_dir_path(__FILE__) . 'ozhands_token.php';

// $token, $site, $name from ozhands_token.php
global $ozhands_token;
$ozhands_token = $token;
global $ozhands_site;
$ozhands_site = $site;
global $ozhands_name;
$ozhands_name = $name;



