<?php
/**
 * Loop Add to Cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/add-to-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>

<form action="" method="post" enctype='multipart/form-data'>

    <button name="add-to-cart" value="<?php echo $product->get_id(); ?>" class="btn btn--grey btn--add-to-cart">Add To Cart</button>

    <div class="product__popup">

        <span class="product__quantity">
            <span>QTY</span>
            <?php
            woocommerce_quantity_input( array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                'input_value' => $product->get_min_purchase_quantity(),
            ) );
            ?>
        </span>

        <?php $group_of = get_post_meta( get_the_ID(), 'group_of_quantity', true ); ?>

        <?php if( $group_of && $group_of > 0 ): ?>
            <p class="group-of--text">
                <?php echo sprintf( 'Can only be ordered in groups of %s', $group_of ); ?>
            </p>
        <?php endif; ?>

        <button type="submit" name="add-to-cart" value="<?php echo $product->get_id(); ?>" class="btn btn--bordered-orange">Add</button>

    </div><!-- /.product__popup -->

</form>


