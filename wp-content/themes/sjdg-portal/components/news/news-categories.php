<aside class="section__aside mobile-hidden">
    <div class="widget widget--nav">

        <h2 class="widget__title">News Categories</h2><!-- /.widget__title -->

        <ul>
            <?php $categories = get_categories();

            foreach( $categories as $category ) : ?>
                <li>
                    <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div><!-- /.widget widget-/-nav -->
</aside><!-- /.section__aside mobile-hidden -->
