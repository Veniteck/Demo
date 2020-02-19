<?php
/**
 * Template Name: Cancel Order Page
 */
get_header();
the_post();

//GT plugin logic only if the plugin is active
if (is_plugin_active('sjdisplay-greentree/sjdisplay-greentree.php')) {

    if (isset($_GET['order_id']) && $_GET['order_id'] != '' && isset($_GET['cancellation_note']) && $_GET['cancellation_note'] != '') {

        //clean up
        $order_id = esc_attr($_GET['order_id']);
        $cancellation_note = esc_attr($_GET['cancellation_note']);

        //add the cancellation note first
        $cancellation_note_response = SJDisplay_Greentree_Order_Amendment::custom_order_add_cancellation_note($order_id, $cancellation_note);

        //cancel the order
        $cancellation_response = SJDisplay_Greentree_Order_Amendment::custom_order_email_action_cancel($order_id);

    } else {
        //make sure user is area-manager
        if (!SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager')) {
            $cancellation_response['result'] = false;
            $cancellation_response['message'] = 'Only area managers can cancel orders!';
            $cancellation_note_response['result'] = true;
            $cancellation_note_response['message'] = '';
        } else {
            // check that the order has not already been cancelled or approved
            $order_id = esc_attr($_GET['order_id']);
            $order = new WC_Order($order_id);

            if ($order->has_status('cancelled')) {
                $cancellation_response['result'] = false;
                $cancellation_response['message'] = 'Order has already been cancelled';
                $cancellation_note_response['result'] = true;
                $cancellation_note_response['message'] = 'Order cancellation note already present';

            } elseif ($order->has_status('approval')) {
                $cancellation_response['result'] = false;
                $cancellation_response['message'] = 'Please enter a reason for the order cancellation and hit submit';

                $cancellation_note_response['result'] = false;
                $cancellation_note_response['message'] = '';
            } else {
                $cancellation_response['result'] = false;
                $cancellation_response['message'] = 'Order can no longer be cancelled';
                $cancellation_note_response['result'] = true;
                $cancellation_note_response['message'] = '';
            }
        }
    }

} else {
    $cancellation_response['result'] = false;
    $cancellation_response['message'] = 'SJ GT plugin inactive, please enable the plugin...';
    $cancellation_note_response['result'] = false;
    $cancellation_note_response['message'] = '';
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

                <!-- cancellation logic output -->
                <?php if ($cancellation_response['result']): ?>

                    <h1>Order Cancelled!</h1>
                    <p><?= $cancellation_response['message'] ?></p>

                <?php else: ?>

                    <p class="woocommerce-thankyou-order-received"><?= $cancellation_response['message'] ?></p>

                <?php endif ?>

                <!-- cancellation note logic output -->
                <?php if ($cancellation_note_response['result']) : ?>

                    <p><?= $cancellation_note_response['message'] ?></p>

                <?php else : ?>

                    <?php
                    //check if we don't have existing cancellation note
                    $existing_cancellation_note = get_post_meta($_GET['order_id'], 'order_cancellation_note', true);
                    if (!$existing_cancellation_note || $existing_cancellation_note == '') : ?>

                        <p>You can optionally also leave a cancellation note by entering some text below and submitting
                            the
                            form:</p>

                        <form method="get">
                            <label for="cancellation-note"><strong>Cancellation Note</strong></label>
                            <br>
                            <textarea name="cancellation_note" id="cancellation_note" cols="30" rows="10"
                                      required></textarea>
                            <br>
                            <input type="hidden" name="order_id" value="<?= esc_attr($_GET['order_id']) ?>">
                            <input type="submit" value="submit">
                        </form>

                    <?php else : ?>

                        <p>A cancellation note has already been attached to this order</p>

                    <?php endif ?>

                <?php endif ?>

            </div><!-- /.main__body -->

        </div><!-- /.shell -->

    </div><!-- /.main__inner -->

</main><!-- /.main -->

<?php get_footer(); ?>
