<?php


add_action('after_setup_theme', 'multi_news_action_calls', 11);
function multi_news_action_calls()
{
    // REMOVE BREADCRUMB
    remove_action('geodir_detail_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_listings_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_author_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_search_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_home_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_location_before_main_content', 'geodir_breadcrumb', 20);

    //ADD BREADCRUMS
    add_action('geodir_detail_before_main_content', 'gd_mn_replace_breadcrums', 20);
    add_action('geodir_listings_before_main_content', 'gd_mn_replace_breadcrums', 20);
    add_action('geodir_author_before_main_content', 'gd_mn_replace_breadcrums', 20);
    add_action('geodir_search_before_main_content', 'gd_mn_replace_breadcrums', 20);
    //add_action('geodir_home_before_main_content', 'gd_mn_replace_breadcrums', 20);
    add_action('geodir_location_before_main_content', 'gd_mn_replace_breadcrums', 20);


    // fix breadcrums
    add_filter('breadcrumbs_plus_items', 'gd_breadcrumbs_plus_items', 1);

    // REMOVE PAGE TITLES
    remove_action('geodir_listings_page_title', 'geodir_action_listings_title', 10);
    // remove_action( 'geodir_add_listing_page_title', 'geodir_action_add_listing_page_title',10);
    remove_action('geodir_details_main_content', 'geodir_action_page_title', 20);
    remove_action('geodir_search_page_title', 'geodir_action_search_page_title', 10);
    remove_action('geodir_author_page_title', 'geodir_action_author_page_title', 10);


    add_action('geodir_wrapper_content_open', 'gd_mn_extra_wrap', 30, 1);
    add_action('geodir_wrapper_content_close', 'gd_mn_extra_wrap_end', 3, 1);
}

function gd_mn_extra_wrap($page)
{
    if ($page == 'add-listing-page') {
        echo '<div class="site-content page-wrap">';
    } elseif ($page == 'signup-page') {
        echo '</div><div class="section full-width-section" style="float: left;width:100%;">';
    }

}


function gd_mn_extra_wrap_end($page)
{
    if ($page == 'add-listing-page') {
        echo '</div>';
    }

}


function gd_mn_replace_breadcrums()
{

    if (mom_option('breadcrumb') != 0) { ?>
        <?php if (mom_option('cats_bread')) {
            $cclass = '';
            if (mom_option('cat_slider') == false) {
                $cclass = 'post-crumbs ';
            }
            ?>
            <div class="<?php echo $cclass; ?>entry-crumbs" xmlns:v="http://rdf.data-vocabulary.org/#">

                <?php mom_breadcrumb(); ?>


            </div>
        <?php } ?>
    <?php }

}


function gd_get_breadcrum_links()
{
    ob_start();
    geodir_breadcrumb();
    $crums = ob_get_contents();
    ob_get_clean();
    $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    if (preg_match_all("/$regexp/siU", $crums, $matches)) {
        return $matches[0];
    } else return '';
}


function gd_breadcrumbs_plus_items($items)
{   //print_r($items);exit;
    $bits = array();
    $pieces = gd_get_breadcrum_links();
    //unset($pieces[0]);
    $bits = $pieces;

    $title = $items['last'];
    if (is_page_geodir_home() || geodir_is_page('location')) {

    } elseif (geodir_is_page('listing')) {

    } elseif (geodir_is_page('detail')) {
        ob_start();
        geodir_action_page_title();
        $title = ob_get_contents();
        ob_end_clean();
    } elseif (geodir_is_page('search')) {
    } elseif (geodir_is_page('author')) {
    }

    $title = strip_tags($title);
    $items = gd_breadcrumbs_plus_items_add($items, $bits, $title);


    return $items;
}


function gd_breadcrumbs_plus_items_add($items, $bits, $last)
{
    //$pieces = explode("</div>", $items[0]);
    if (is_array($bits)) {
        $items = array();
        $pieces = '';
        foreach ($bits as $bit) {
            $pieces .= $bit;
        }
        $items[0] = '<div class="vbreadcrumb" typeof="v:Breadcrumb">' . $pieces . "</div>";
        if (isset($last) && $last) {
            $items['last'] = $last;
        }
    }

    //print_r($items);
    return $items;

}


?>