<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'OZH_package_ends' ) ) {

    class OZH_package_ends extends WC_Email {

        function __construct() {
            $this->id = 'ozh_package_ends';
            $this->customer_email = true;
            $this->title = 'Ozhands package ends';
            $this->description= 'Send email to retailer befor package ends';
            $this->heading = 'Your Orzhands package ends';
            $this->subject = '{site_title} your package ends in 3 days';
            $this->template_base = OZH_EMAIL_DIR.'/templates/';
            $this->template_html = 'emails/package_ends.php';
            $this->template_plain = 'emails/plain/package_ends.php';
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