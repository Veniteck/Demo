<div class="wrap">
    <?php include_once(SJDISPLAY_GREENTREE_PLUGIN_DIR . '/partials/header.php') ?>

    <h2>API Import Scripts & Endpoints</h2>
    <p>Below is a list of URLs to use for importing/updating various data across the site via the API</p>
    <?php
    $scripts = array(
        'Import CRON scripts' => array(
            array(
                'description' => '',
                'url' => 'cron-import-customers.php'
            ),
            array(
                'description' => '',
                'url' => 'cron-import-products.php'
            ),
            array(
                'description' => '',
                'url' => 'cron-sync-orders.php'
            )
        ),
        'Helper / Test scripts' => array(
            array(
                'description' => 'You must supply a product SKU as the "sku" query string parameter e.g. ?sku=A-DBSS',
                'url' => 'script-import-single-product.php'
            ),
            array(
                'description' => 'You must supply a customer code as the "customer" query string parameter e.g. ?customer=20706',
                'url' => 'script-import-single-customer.php'
            )
        )
    );
    foreach ($scripts as $category => $script_array) : ?>
        <h3><?= $category ?></h3>
        <?php foreach ($script_array as $script) : ?>
            <p>
                <?php if ($script['description']) : ?>
                    <span><?= $script['description'] ?></span><br>
                <?php endif ?>
                <a href="<?= SJDISPLAY_GREENTREE_SCRIPTS_URL . $script['url'] ?>" target="_blank">
                    <?= SJDISPLAY_GREENTREE_SCRIPTS_URL . $script['url'] ?>
                </a>
            </p>
        <?php endforeach ?>
    <?php endforeach ?>

    <hr>

</div>