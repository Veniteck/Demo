<?php
/**
 * Template Name: Track Page
 */
get_header();
the_post();
?>

<main class="main main--portal">

    <section class="section-intro">

        <div class="shell">

            <header class="section__head">
                <h3><?php the_title(); ?>: <?php echo $_GET['consignment'] ?></h3>
            </header><!-- /.section__head -->

            <div class="section__body">

            </div><!-- /.section__body -->

        </div><!-- /.shell -->

    </section><!-- /.section-intro -->


    <div class="main__inner">

        <div class="shell">

            <div class="main__body">

                <?php the_content(); ?>

                <?php if (isset($_GET['consignment']) && $_GET['consignment'] != '') : ?>

                    <br>
                    <iframe src="https://trackmyparcel.com.au/track/<?php echo $_GET['consignment'] ?>" width="100%"
                            height="600px"></iframe>
                    <br>

                <?php else: ?>

                    <p><strong>Consignment Number missing or invalid</strong></p>

                <?php endif ?>

            </div><!-- /.main__body -->

        </div><!-- /.shell -->

    </div><!-- /.main__inner -->

</main><!-- /.main -->

<?php get_footer(); ?>
