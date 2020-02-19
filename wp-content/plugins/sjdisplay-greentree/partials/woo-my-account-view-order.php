<?php if ($order_packing_slips = json_decode(get_post_meta($order_id, 'order_greentree_packing_slip', true))) :
    $tracking_base_url = 'https://sjdg.global/track?consignment=';
    ?>
    <section class="woocommerce-packing-slip-details">

        <h2>Shipping Details</h2>

        <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tr>
                <th>Reference</th>
                <th>Data</th>
                <th>Tracking</th>
            </tr>
            <?php foreach ($order_packing_slips as $packing_slip) :
                if (is_array($packing_slip->LineItems->LineItem)) {
                    $line_items = $packing_slip->LineItems->LineItem;
                } else {
                    $line_items[] = $packing_slip->LineItems->LineItem;
                }
                ?>
                <?php foreach ($line_items as $line_item) :
                if ($line_item->StockItem == 'Z-FREIGHT') :
                    $consignment_number_array = (array)$line_item->ConsignNumber;
                    if (!empty($consignment_number_array)) :
                        ?>
                        <tr>
                            <td><?= $packing_slip->Reference ?></td>
                            <td>
                                <strong>Consignment Number: </strong>
                                <a href="<?= $tracking_base_url . $line_item->ConsignNumber ?>" target="_blank">
                                    <?= $line_item->ConsignNumber ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?= $tracking_base_url . $line_item->ConsignNumber ?>" target="_blank">
                                    Click to view current tracking status
                                </a>
                            </td>
                        </tr>
                    <?php endif; endif;
            endforeach ?>
            <?php endforeach ?>
        </table>

    </section>
<?php endif ?>