<li class="widget widget--filters">

	<ul>

        <li class="parent--category-item">
            <div class="filter__checkbox">
                <input name="categories[]" data-permalink="<?php echo get_permalink( wc_get_page_id( 'shop' ) ); ?>" value="" type="radio" class="parent--category-checkbox" id="category-0">
                <label for="category-0">All Items</label>
            </div><!-- /.checkbox -->
        </li>

		<?php

		/**
		 * @TODO if there are more than 30-40 categories all up, cache this list in a transient and clear it when updating a term.
		 */
		$categories = get_terms([
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0
		] ); ?>

		<?php foreach( $categories as $category ): ?>

        <?php
        $title_override = get_field( 'category_list_title_override', $category );
        $display_title = ( $title_override ) ? $title_override : $category->name ;
        ?>

		<li class="parent--category-item">
			<div class="filter__checkbox">

                <?php $checked = (
                        get_queried_object_id() === $category->term_id ||
                        ( isset( $_POST['search_category'] ) && intval( $_POST['search_category'] ) === $category->term_id )
                ) ? 'checked="checked"' : '' ; ?>

				<input data-permalink="<?php echo get_term_link( $category ); ?>" <?php echo $checked; ?>  name="categories[]" value="<?php echo $category->term_id; ?>" type="radio" class="parent--category-checkbox" id="category-<?php echo $category->term_id; ?>">

				<label for="category-<?php echo $category->term_id; ?>"><?php echo esc_html( $display_title ); ?></label>
			</div><!-- /.checkbox -->

			<?php $child_categories = get_terms( [
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => $category->term_id
			] ); ?>

			<?php if( $child_categories && ! empty( $child_categories ) ): ?>

				<div class="filter__arrow">
					<i class="ico-arrow-orange"></i>
				</div><!-- /.filter__arrow -->

				<ul>

					<?php foreach( $child_categories as $child_category ): ?>

                        <?php
                        $title_override = get_field( 'category_list_title_override', $child_category );
                        $display_title = ( $title_override ) ? $title_override : $child_category->name ;
                        ?>

						<li class="child--category-item">
							<div class="filter__checkbox">

								<?php
                                // Check this if the PARENT category is searched from the search bar (IE POSTED not through AJAX)
                                $checked = (
									get_queried_object_id() === $child_category->term_id ||
									( isset( $_POST['search_category'] ) && intval( $_POST['search_category'] ) === $category->term_id )
								) ? 'checked="checked"' : '' ; ?>

								<input data-permalink="<?php echo get_term_link( $category ); ?>" <?php echo $checked; ?> name="categories[]" value="<?php echo $child_category->term_id; ?>" type="radio" class="child--category-checkbox" id="category-<?php echo $child_category->term_id; ?>">

								<label for="category-<?php echo $child_category->term_id; ?>"><?php echo esc_html( $display_title ); ?></label>
							</div><!-- /.checkbox -->
						</li>

					<?php endforeach; ?>

				</ul>

			<?php endif; ?>

			<?php endforeach; ?>

	</ul>

</li><!-- /.widget -->