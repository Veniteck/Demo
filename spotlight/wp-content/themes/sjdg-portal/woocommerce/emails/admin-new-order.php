<?php
/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author WooThemes
 * @package WooCommerce/Templates/Emails/HTML
 * @version 2.5.0
 */

 if ( ! defined( 'ABSPATH' ) ) {
 	exit;
 }

/**
 * @var WC_Order $order
 */

$subject_order_id = SJDisplay_Greentree_Woo_Emails::get_order_id_for_email_subject($order);

 /**
  * @hooked WC_Emails::email_header() Output the email header
  */
 do_action( 'woocommerce_email_header', 'SJ Order # ' . $subject_order_id . ' - Created', $email ); ?>

 <p><?php printf( __( 'You have received an order from %s. The order is as follows:', 'woocommerce' ), $order->get_formatted_billing_full_name() ); ?></p>

 <?php

 /**
  * @hooked WC_Emails::order_details() Shows the order details table.
  * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
  * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
  * @since 2.5.0
  */
 do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

 /**
  * @hooked WC_Emails::order_meta() Shows order meta data.
  */
 do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

 /**
  * @hooked WC_Emails::customer_details() Shows customer details
  * @hooked WC_Emails::email_address() Shows email address
  */

/*$customer = get_user_by( 'id', $order->get_customer_id() );

$stores = get_field( 'associated_stores', $customer );

/*reach( $stores as $store ):

    $_store = get_post( $store );

   ?>

    <div style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;">
       <h2><?php _e( 'Customer details', 'woocommerce' ); ?></h2>
       <ul>
           <li><strong>Store Name :</strong> <?php echo $_store->post_title; ?></li>
           <li><strong>Address :</strong></li>
           <li>
           <?php foreach ( [ 'address', 'address_2', 'address_3' ] as $field ): ?>
               <?php $val = get_field( $field, $store ); ?>
               <?php if( $val ): ?>
                   <?php echo $val; ?><br />
               <?php endif; ?>
           <?php endforeach; ?>
           <?php echo get_field( 'suburb', $store ); ?>, <?php echo get_field( 'state', $store ); ?>, <?php echo get_field( 'postcode', $store ); ?><br />
           <?php echo get_field( 'country', $store ); ?></li>
           <li><strong>Email Address :</strong> <?php echo get_field( 'email', $store ); ?></li>
       </ul>
    </div>

   <?php

endforeach;*/

//do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
 do_action( 'woocommerce_email_footer', $email );
