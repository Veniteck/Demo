<?php
/**
 * Template Name: Approve Order Page
 */
get_header();
the_post();

//GT plugin logic only if the plugin is active
if (is_plugin_active('sjdisplay-greentree/sjdisplay-greentree.php')) {
    if (isset($_GET['order_id']) && $_GET['order_id'] != '') {
        $response = SJDisplay_Greentree_Order_Amendment::custom_order_email_action_approve(esc_attr($_GET['order_id']));
    } else {
        $response['result'] = false;
        $response['message'] = 'Order ID not passed, please check the order approval notification email';
    }
} else {
    $response['result'] = false;
    $response['message'] = 'SJ GT plugin inactive, please enable the plugin...';
}
?>

<main class="main main--portal">

    <div class="main__inner">

        <div class="shell">

            <?php woocommerce_breadcrumb([
                'wrap_before' => '<nav class="breadcrumbs"><ul>',
                'wrap_after' => '</ul></nav>',
                'home' => 'Products',
                'before' => '<li>',
                'after' => '</li>',
                'delimiter' => ''
            ]); ?>

            <div class="main__body">

                <?php if ($response['result']): ?>

                    <h1>Approval Success!</h1>
                    <p><?= $response['message'] ?></p>

                <?php else: ?>

                    <p class="woocommerce-thankyou-order-received"><?= $response['message'] ?></p>

                <?php endif ?>

            </div><!-- /.main__body -->

        </div><!-- /.shell -->

    </div><!-- /.main__inner -->

</main><!-- /.main -->

<?php get_footer(); ?>
