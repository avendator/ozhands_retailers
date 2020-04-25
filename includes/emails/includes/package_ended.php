<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'OZH_package_ended' ) ) {

    class OZH_package_ended extends WC_Email {

        function __construct() {
            $this->id = 'ozh_package_ended';
            $this->customer_email = true;
            $this->title = 'Ozhands package ended';
            $this->description= 'Send a letter to the seller after the package expires';
            $this->heading = 'Your Orzhands package has expired';
            $this->subject = '{site_title} your package has expired';
            $this->template_base = OZH_EMAIL_DIR.'/templates/';
            $this->template_html = 'emails/package_ended.php';
            $this->template_plain = 'emails/plain/package_ended.php';
            parent::__construct();
        }

        function trigger( $email ) {

            $this->send($email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        function get_content_html() {
            return wc_get_template_html(
                $this->template_html,
                array(
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'=>false,
                'email' => $this),
                $this->template_base,
                $this->template_base);
        }

        function get_content_plain() {
            return wc_get_template_html(
                $this->template_plain,
                array(
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'=>true,
                'email' => $this),
                $this->template_base,
                $this->template_base);
        }
    }
}
?>