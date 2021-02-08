<?php
/*
* Creating a function to create our CPT
*/

function custom_post_type() {

// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'News', 'twentytwentyone' ),
        'singular_name'       => _x( 'News', 'twentytwentyone' ),
        'menu_name'           => __( 'News', 'twentytwentyone' ),
        'parent_item_colon'   => __( 'Parent News', 'twentytwentyone' ),
        'all_items'           => __( 'All News', 'twentytwentyone' ),
        'view_item'           => __( 'View News', 'twentytwentyone' ),
        'add_new_item'        => __( 'Add New News', 'twentytwentyone' ),
        'add_new'             => __( 'Add New News', 'twentytwentyone' ),
        'edit_item'           => __( 'Edit News', 'twentytwentyone' ),
        'update_item'         => __( 'Update News', 'twentytwentyone' ),
        'search_items'        => __( 'Search News', 'twentytwentyone' ),
        'not_found'           => __( 'Not Found', 'twentytwentyone' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwentyone' ),
    );

    // Set other options for Custom Post Type

    $args = array(
        'label'               => __( 'news', 'twentytwentyone' ),
        'description'         => __( 'News of twenty twenty one', 'twentytwentyone' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'thumbnail','editor'),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,

    );

    // Registering your Custom Post Type
    register_post_type( 'news', $args );

}

/* Hook into the 'init' action so that the function
* Containing our post type registration is not
* unnecessarily executed.
*/

add_action( 'init', 'custom_post_type', 0 );



//create a custom taxonomy

function twentyOneChildTaxonomies() {

    //Custom Taxonomy For Team Custom Post Type
    $labels = array(
        'name' => _x( 'Types', 'taxonomy general name' ),
        'singular_name' => _x( 'Type', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Types' ),
        'all_items' => __( 'All Types' ),
        'parent_item' => __( 'Parent Type' ),
        'parent_item_colon' => __( 'Parent Type:' ),
        'edit_item' => __( 'Edit Type' ),
        'update_item' => __( 'Update Type' ),
        'add_new_item' => __( 'Add New Type' ),
        'new_item_name' => __( 'New Type Name' ),
        'menu_name' => __( 'Types' ),
    );

    register_taxonomy('news_types',array('news'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'show_in_rest' => true,
        'publicly_queryable' => false,
        'rewrite' => array( 'slug' => 'type' ),
    ));
}
// Let us create Taxonomy for Custom Post Type
add_action( 'init', 'twentyOneChildTaxonomies', 0 );

// Creating custom meta field at custom post
function twentyOneChildCustomMeta() {
    /*
     * Create Designation Field For Custom Post Type News
     * */
    add_meta_box( 'news_post_meta', 'Author', 'display_news_post_meta','news', 'side', 'low' );

}
add_action( 'admin_init', 'twentyOneChildCustomMeta' );

function display_news_post_meta($post) {
$html = '';
 $html .= '<div class="newsAuthor">';

     $html .= '<div class="form-group">';
            $html .= '<input type="text" required class="form-control" name="meta[news_author_name]" value="'.esc_html( get_post_meta( $post->ID, 'news_author_name', true ) ).'">';
     $html .='</div>';
 $html .= '</div>';

 echo $html;

}

//Save the metabox
add_action( 'save_post', 'save_all_postmeta', 10, 2 );
function save_all_postmeta( $post_id, $post ) {
    if ( isset( $_POST['meta'] ) ) {
        foreach( $_POST['meta'] as $key => $value ){
            update_post_meta( $post_id, $key, $value );
        }
    }
}

/*
 * Pagination with Bootstrap
 */
function customPagination ($maxPage) {
    $html = "";
    $html .= '<div class="col-lg-12 col-md-12 text-center">';
    $pagination =  paginate_links( array(
        'base' => str_replace( PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
        'format' => '?paged=%#%',
        'total' => $maxPage,
        'type' => 'array',
        'mid_size' => 4,
        'prev_text'          => 'Previous',
        'next_text'          => 'Next'
    ));

    $html .= '<nav aria-label="Page navigation example">';
    $html .= '<ul class="pagination pagination justify-content-center">';

    foreach ( $pagination as $key => $page_link ) :
        if ( strpos( $page_link, 'current' ) !== false ) { $active = ' active'; }  else {$active = ' ';}
            $page_link = str_replace( 'page-numbers', 'page-link', $page_link );

            $html .= '<li class="page-item'.$active.'">'.$page_link.'</li>';

    endforeach;

    $html .= '</ul>';
    $html .= '</nav>';

    $html .= '</div>';

    return $html;
}