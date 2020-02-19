<section class="section-twitter">
  <div class="shell">
    <span class="fa fa-twitter"></span>

    <div class="slider-twitter">
      <div class="swiper-container">
        <div class="swiper-wrapper twitter-feed" id="twitter" data-settings='{"dataOnly": true, "maxTweets": <?php the_field( 'feed_count', 'option' ); ?>, "profile": {"screenName": "<?php the_field( 'feed_handler', 'option' ); ?>"}}'></div><!-- /.swiper-wrapper -->
      </div><!-- /.swiper-container -->
    </div><!-- /.slider-twitter -->

    <template id="tweet">
      <div class="swiper-slide">
        <div class="tweet"></div><!-- /.tweet -->
      </div><!-- /.swiper-slide -->
    </template><!-- /#tweet -->
  </div><!-- /.shell -->
</section><!-- /.section-twitter -->
