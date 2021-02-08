<?php
/*
 * Template Name: Dev Test
 */
get_header();
?>
    <section class="inner-page news-lists">
        <div class="container aos-init aos-animate" data-aos="fade-up" data-aos-delay="100">
            <div class="section-title">
                <h2 style="font-family: Raleway, Bangla835, sans-serif;">News</h2>
            </div>
            <div class="row no-gutters">
                <?php
                    $page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

                    $query_args = array(
                        'post_type'      =>  'news',
                        'posts_per_page' =>  6,
                        'paged'          =>  $page,
                        'orderby'          => 'id',
                        'order'        => 'DESC',
                        'post_status'    => 'publish'

                    );

                    $data = new WP_Query ( $query_args );

                    if ( $data->have_posts() ) {

                        while ( $data->have_posts() ) : $data->the_post();

                            get_template_part( 'template-parts/content', 'news' );

                        endwhile;

                    }

                    if (  $data->max_num_pages > 1 ) echo customPagination($data->max_num_pages);
                ?>

            </div>
        </div>
    </section>
<?php
get_footer();