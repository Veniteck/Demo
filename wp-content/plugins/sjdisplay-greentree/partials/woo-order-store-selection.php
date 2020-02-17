<?php if ($stores = SJDisplay_Greentree::get_stores_associated_with_user(get_current_user_id())) :
    $selected_store = $_GET['store-selected'];
    ?>

    <div class="area-manager-store-selection">
        <h2 class="form__title">Order store selection</h2>

        <p class="form-row form-row-wide form__col" id="store_selection_field">
            <label for="store_id" class="">You must select a store for this order before checking out.</label>
            <span class="woocommerce-input-wrapper">
                <select name="store_id" id="store">
                    <option value="">Select store</option>
                    <?php foreach ($stores as $store) :
                        $selected = '';
                        if ($selected_store == $store['store_customer_code']) {
                            $selected = 'selected';
                        }
                        ?>
                        <option <?= $selected ?>
                            value="<?= $store['store_customer_code'] ?>"><?= $store['store_post_name'] ?></option>
                    <?php endforeach ?>
                </select>
            </span>
        </p>
    </div>

<?php endif ?>