<?php
/**
* Breadcrumbs
*/

function dt_breadcrumbs()
{
    $home = 'Home'; // text for the 'Home' link
    $before = '<li><span>  '; // tag before the current crumb
    $after = '</span></li>'; // tag after the current crumb


    if (is_home()) {
        $homeLink = get_bloginfo('url');

        $out = '<nav class="breadcrumbs mobile-hidden">	<div class="shell"><ul class="breadcrumbs">';
        $out .= '<li><a href="' . $homeLink . '">' . $home . '</a></li>';
        $out .= $before . wp_title('', false) . $after;
        $out .= '</ul></div><!-- /.shell --></nav><!-- /.breadcrumbs mobile-hidden -->';

        echo $out;
    } else {
        if (!is_home() && !is_front_page() || is_paged()) {
            global $post;

            $homeLink = get_bloginfo('url');
            $page_for_posts_id = get_option( 'page_for_posts' );

            $out = '<nav class="breadcrumbs mobile-hidden"><div class="shell"><ul class="breadcrumbs">';

            $out .= '	<li><a href="' . $homeLink . '">' . $home . '</a>	</li>' ;

            if (is_category() || is_tax()) {

                $out .= '	<li><a href="' . get_the_permalink( $page_for_posts_id ) . '">' . get_the_title ( $page_for_posts_id ) . '</a>	</li>' ;

                global $wp_query;
                $queried_obj = get_queried_object();

                if (is_taxonomy_hierarchical($queried_obj->name)) {
                    if ($queried_obj->parent != 0) $out .= get_term_parents($queried_obj->ID, $queried_obj->taxonomy, true, ' ');
                }

                $out .= $before . single_cat_title('', false) . $after;

            } elseif (is_day()) {
                $out .= '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>';
                $out .= '<li><a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a></li>';
                $out .= $before . get_the_time('d') . $after;

            } elseif (is_month()) {
                $out .= '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>';
                $out .= $before . get_the_time('F') . $after;

            } elseif (is_year()) {
                $out .= $before . get_the_time('Y') . $after;

            } elseif (is_single() && !is_attachment()) {

                $out .= '	<li><a href="' . get_the_permalink( $page_for_posts_id ) . '">' . get_the_title ( $page_for_posts_id ) . '</a>	</li>' ;

                if (get_post_type() != 'post') {
                    $post_type = get_post_type_object(get_post_type());
                    $slug = $post_type->rewrite;
                    $out .= '<li><a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a></li>';
                    $cleanTitle = get_the_title();

                    $out .= $before . preg_replace("/<br\W*?\/>/", "&nbsp;", $cleanTitle) . $after ;
                } else {
                    $cat = get_the_category();

                    if ($cat && !is_wp_error($cat)) {
                        $cat = $cat[0];
                        // $out .= '<li>' . get_category_parents($cat, TRUE, '</li><li>') . '</li>' ;
                        $out .= '<li>' . get_category_parents($cat, TRUE, '') . '</li>' ;
                    }

                    $out .= $before . get_the_title() . $after;
                }

            } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404() && !is_search()) {

                $out .= $before . get_queried_object()->labels->name . $after ;

            } elseif (is_attachment()) {
                $parent = get_post($post->post_parent);
                $cat = get_the_category($parent->ID);
                $cat = $cat[0];
                $out .= get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
                $out .= '<li><a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a></li>';
                $out .= $before . get_the_title() . $after;

            } elseif (is_page() && !$post->post_parent) {
                $out .= $before . get_the_title() . $after;

            } elseif (is_page() && $post->post_parent) {
                $parent_id = $post->post_parent;
                $breadcrumbs = array();
                while ($parent_id) {
                    $page = get_page($parent_id);
                    $breadcrumbs[] = '<li><a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a></li>';
                    $parent_id = $page->post_parent;
                }
                $breadcrumbs = array_reverse($breadcrumbs);
                foreach ($breadcrumbs as $crumb) $out .= $crumb . ' ';
                $out .= $before . get_the_title() . $after;

            } elseif (is_search()) {
                $out .= $before . 'Search results for "' . get_search_query() . '"' . $after;

            } elseif (is_tag()) {
                $out .= $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;

            } elseif (is_author()) {
                global $author;
                $userdata = get_userdata($author);
                $out .= $before . 'Articles posted by ' . $userdata->display_name . $after;

            } elseif (is_404()) {
                $out .= $before . 'Page not found' . $after;
            }

            if (get_query_var('paged')) {
                if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) echo ' (';
                $out .= __('Page') . ' ' . get_query_var('paged');
                if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) echo ')';
            }

            $out .= '</ul></div><!-- /.shell --></nav><!-- /.breadcrumbs mobile-hidden -->';

            echo $out;
        }
    }
} // end dt_breadcrumbs()


if (!function_exists('get_term_parents')) {
    function get_term_parents($id, $taxonomy, $link = false, $nicename = false, $visited = array())
    {
        $chain = '';
        $parent = & get_term($id, $taxonomy);
        if (is_wp_error($parent))
            return $parent;

        if ($nicename)
            $name = $parent->slug;
        else
            $name = $parent->name;

        if ($parent->parent && ($parent->parent != $parent->term_id) && !in_array($parent->parent, $visited)) {
            $visited[] = $parent->parent;
            $chain .= get_term_parents($parent->parent, $taxonomy, $link, $nicename, $visited);
        }

        if ($link)
            $chain .= '<li><a href="' . get_term_link($parent->slug, $taxonomy) . '" title="' . esc_attr(sprintf(__("View all posts in %s"), $parent->name)) . '">' . $name . '</a></li>';
        else
            $chain .= $name;
        return $chain;
    }
}
