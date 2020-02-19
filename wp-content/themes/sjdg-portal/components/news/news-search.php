<div class="search search--default">
    <div class="shell">

        <form action="?" method="get" id="news__search_form">
            <label for="s" class="search__label">Search</label>

            <div class="search__controls">

                <?php $value = ( isset( $_GET['search'] ) ) ? filter_var( $_GET['search'], FILTER_SANITIZE_STRING ) : '' ; ?>

                <input type="search" name="s" id="s" value="<?php esc_attr_e( $value ); ?>" placeholder="" class="search__field">

                <button type="submit" class="btn btn--primary btn--primary-blue search__btn">Go</button>
            </div><!-- /.search__controls -->
        </form>

    </div><!-- /.shell -->
</div><!-- /.search search-/-default -->
