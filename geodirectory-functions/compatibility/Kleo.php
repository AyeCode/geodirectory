<?php
/**
 * Kleo theme compatibility functions.
 *
 * This file lets the GeoDirectory Plugin use the X theme HTML wrappers to fit and work perfectly.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

// Page titles translatable CPT names
function geodir_kelo_title_translation( $args) {
    if(function_exists('geodir_is_geodir_page') && geodir_is_page('preview') ){
        $args['title'] = __(stripslashes_deep(esc_html($_POST['post_title'])),'geodirectory');
    }elseif(function_exists('geodir_is_geodir_page')){
        $args['title'] = __($args['title'],'geodirectory');
    }

    return $args;
}
add_filter( 'kleo_title_args', 'geodir_kelo_title_translation', 10, 1 );

/**
 * Fix search returns all the posts for Kleo theme.
 *
 * Kleo sets the search page to use whether post or page, we need it to be 'any'.
 *
 * @since 1.0.0
 *
 * @param object $query Current query object.
 * @return object Modified query object.
 */
function geodir_kleo_search_filter( $query ) {
    if ( !empty( $query->is_search ) && geodir_is_page('search') && is_search() ) {
        $query->set( 'post_type', 'any' );
    }
    return $query;
}
if ( !is_admin() ) {
    add_filter( 'pre_get_posts', 'geodir_kleo_search_filter', 11 );
}

if( ! function_exists( 'kleo_title' ) ){
    function kleo_title(){ return geodir_kleo_custom_the_title();}
}

/**
 * Alter the CPT and Cat page titles
 *
 * 1.6.19
 * @return mixed
 */
function geodir_kleo_custom_the_title()
{

    $output = '';
    if (is_tag()) {
        $output = __('Tag Archive for:','kleo_framework')." ".single_tag_title('',false);
    }
    elseif(is_tax()) {
        $term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
        $output = $term->name;
    }
    elseif ( is_category() ) {
        $output = __('Archive for category:', 'kleo_framework') . " " . single_cat_title('', false);
    }
    elseif (is_day())
    {
        $output = __('Archive for date:','kleo_framework')." ".get_the_time('F jS, Y');
    }
    elseif (is_month())
    {
        $output = __('Archive for month:','kleo_framework')." ".get_the_time('F, Y');
    }
    elseif (is_year())
    {
        $output = __('Archive for year:','kleo_framework')." ".get_the_time('Y');
    }
    elseif (is_author())  {
        $curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
        $output = __('Author Archive','kleo_framework')." ";

        if( isset( $curauth->nickname ) ) {
            $output .= __('for:','kleo_framework')." ".$curauth->nickname;
        }
    }
    elseif ( is_archive() )  {
        $output = post_type_archive_title( '', false );
    }
    elseif (is_search())
    {
        global $wp_query;
        if(!empty($wp_query->found_posts))
        {
            if($wp_query->found_posts > 1)
            {
                $output =  $wp_query->found_posts ." ". __('search results for:','kleo_framework')." ".esc_attr( get_search_query() );
            }
            else
            {
                $output =  $wp_query->found_posts ." ". __('search result for:','kleo_framework')." ".esc_attr( get_search_query() );
            }
        }
        else
        {
            if(!empty($_GET['s']))
            {
                $output = __('Search results for:','kleo_framework')." ".esc_attr( get_search_query() );
            }
            else
            {
                $output = __('To search the site please enter a valid term','kleo_framework');
            }
        }

    }
    elseif ( is_front_page() && !is_home() ) {
        $output = get_the_title(get_option('page_on_front'));

    } elseif ( is_home() ) {
        if (get_option('page_for_posts')) {
            $output = get_the_title(get_option('page_for_posts'));
        } else {
            $output = __( 'Blog', 'kleo_framework' );
        }

    } elseif ( is_404() ) {
        $output = __('Error 404 - Page not found','kleo_framework');
    }
    else {
        $output = get_the_title();
    }

    if (isset($_GET['paged']) && !empty($_GET['paged']))
    {
        $output .= " (".__('Page','kleo_framework')." ".$_GET['paged'].")";
    }


    $gd_page = '';
    if(geodir_is_page('pt')){
        $gd_page = 'pt';
        $output = (get_option('geodir_page_title_pt')) ? get_option('geodir_page_title_pt') : '';
    }
    elseif(geodir_is_page('listing')){
        $gd_page = 'listing';
        $output = (get_option('geodir_page_title_cat-listing')) ? get_option('geodir_page_title_cat-listing') : '';
    }



    /**
     * Filter page meta title to replace variables.
     *
     * @since 1.5.4
     * @param string $title The page title including variables.
     * @param string $gd_page The GeoDirectory page type if any.
     */
    return apply_filters('geodir_seo_meta_title', __($output, 'geodirectory'), $gd_page);

}