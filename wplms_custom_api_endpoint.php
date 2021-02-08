<?php
/*
 * 1. getting course categories
 */

add_action('rest_api_init', function () {
  register_rest_route( 'wplmscourse/v1', 'categories',array(
                'methods'  => 'GET',
                'callback' => 'get_all_the_course_categories'
      ));
});

function get_all_the_course_categories($request) {


    $terms = get_terms( array(
        'taxonomy' => 'course-cat',
        'hide_empty' => false,
    ) );

    if (empty($terms)) {
        return new WP_Error( 'No category available', 'There is no category available right now! Please try again later', array('status' => 404) );

    }

    $response = new WP_REST_Response($terms);
    $response->set_status(200);

    return $response;
}

/*
 * 2. get courses by categories
 */

add_action('rest_api_init', function () {
  register_rest_route( 'wplmscourse/v1', 'category/(?P<category>\d+)',array(
                'methods'  => 'GET',
                'callback' => 'get_all_the_course_by_category'
      ));
});

function get_all_the_course_by_category($request) {

    $args = array(
        'post_type'     =>  'course',
        'numberposts'   =>  -1,
        'post_status'   => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'course-cat',
                'field' => 'id',
                'terms' => array($request['category']),
            )
         )
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return new WP_Error( 'No posts available', 'There is no posts available right now! Please try again later', array('status' => 404) );

    }

    $response = new WP_REST_Response($posts);
    $response->set_status(200);

    return $response;
}

/*
 * 3. filter by Alphabetical / Highest rated
 */

add_action('rest_api_init', function () {
  register_rest_route( 'wplmscourse/v1', 'course/(?P<filters>[a-zA-Z0-9-]+)/(?P<order>[a-zA-Z0-9-]+)',array(
                'methods'  => 'GET',
                'callback' => 'get_all_the_course'
      ));
});

function get_all_the_course($request) {

    $args = array(
            'post_type'      =>  'course',
            'numberposts'    =>  -1,
            'post_status'    => 'publish'
    );

    switch ($request['filters']) {
        case 'rating':
            $args['meta_key'] = 'rating_count';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = $request['order'];
            break;
        case 'title':
            $args['orderby']  = $request['filters'];
            $args['order']    = $request['order'];
            break;
        default:
            $args['orderby']  = 'id';
            $args['order']    = 'DESC';


    }

    $posts = get_posts($args);
    if (empty($posts)) {
        return new WP_Error( 'No course available', 'There are no posts available right now! Please try again later', array('status' => 404) );

    }

    $response = new WP_REST_Response($posts);
    $response->set_status(200);

    return $response;
}

/*
 * 4. getting course overview/detials
 */
add_action('rest_api_init', function () {
  register_rest_route( 'wplmscourse/v1', 'course-details/(?P<course_id>\d+)',array(
                'methods'  => 'GET',
                'callback' => 'get_course_details_by_id'
      ));
});

function get_course_details_by_id($request) {
    $args = array(
        'post_type' => 'course',
        'post__in' => array($request['course_id'])
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return new WP_Error( 'Course not available', 'The course you are looking for either is not available nor removed!', array('status' => 404) );

    }

    $response = new WP_REST_Response($posts);
    $response->set_status(200);

    return $response;
}

/*
 * 5. getting course modules with details
 */
add_action('rest_api_init', function () {
  register_rest_route( 'wplmscourse/v1', 'course-module/(?P<course_id>\d+)',array(
                'methods'  => 'GET',
                'callback' => 'get_course_module_by_id'
      ));
});

function get_course_module_by_id($request) {

    $data = bp_course_get_full_course_curriculum($request['course_id']);

    if (empty($data)) {
        return new WP_Error( 'Course not available', 'The course you are looking for either is not available or removed!', array('status' => 404) );

    }

    $response = new WP_REST_Response($data);
    $response->set_status(200);

    return $response;
}

/*
 * 6. add to cart
 */
add_action('rest_api_init', function () {
  register_rest_route( 'product/v1', 'add_to_cart',array(
                'methods'  => 'POST',
                'callback' => 'wplms_add_to_cart'
      ));
});

function wplms_add_to_cart($request) {

    $cart = wc()->cart;
    if( ! WC()->cart->find_product_in_cart( $product_cart_id ) ){
        // The product ID is NOT in the cart, let's add it then!
       $added = $cart->add_to_cart( $request['product_id']);
    }

    if ($added == false) {
        return new WP_Error( 'Already in cart', 'The product is in already cart!', array('status' => 404) );

    }

    $response = new WP_REST_Response('Success! '.get_the_title($request['product_id']) .' is added to your cart!', array('status' => 200));
    $response->set_status(200);

    return $response;
}

/*
 * 6. remove cart
 */
add_action('rest_api_init', function () {
  register_rest_route( 'product/v1', 'remove_from_cart',array(
                'methods'  => 'DELETE',
                'callback' => 'wplms_remove_from_cart',
      ));
});

function wplms_remove_from_cart($request) {

    $product_id = $request['product_id'];
    $product_cart_id = WC()->cart->generate_cart_id( $product_id );
    $cart_item_key = WC()->cart->find_product_in_cart( $product_cart_id );
    if ( $cart_item_key ) $removed = WC()->cart->remove_cart_item( $cart_item_key );

    if ($removed == false) {
        return new WP_Error( 'Nothing to remove', 'The product , you are trying to remove from cart, is not in the cart!', array('status' => 404) );

    }

    $response = new WP_REST_Response('Success! '.get_the_title($request['product_id']) .' is removed from your cart!', array('status' => 200) );
    $response->set_status(200);

    return $response;
}

/*
 * 7. enroll to courses/ purchase course
 */
add_action('rest_api_init', function () {
  register_rest_route( 'course/v1', 'enroll',array(
                'methods'  => 'POST',
                'callback' => 'wplms_enroll_to_course',
      ));
});

function wplms_enroll_to_course($request) {
    $enroll = bp_course_add_user_to_course($request['user_id'],$request['course_id']);

    if(!$enroll){
        return new WP_Error( 'Not enrolled', 'You can not be enrolled to this course', array('status' => 404) );
    }

    $response = new WP_REST_Response( 'Success! You are enrolled to the course' );
    $response->set_status(200);

    return $response;
}

/*
 * 8. save course progress
 */
add_action('rest_api_init', function () {
  register_rest_route( 'course/v1', 'save_progress',array(
                'methods'  => 'GET',
                'callback' => 'wplms_save_course_progress',
      ));
});

function wplms_save_course_progress($request) {
    $save = bp_course_update_user_progress($request['user_id'],$request['course_id'],$request['progress']);

    $response = new WP_REST_Response( 'Success! Course Progress Updated.' );
    $response->set_status(200);

    return $response;
}

/*
 * 9. User authentication
 */
add_action('rest_api_init', function () {
  register_rest_route( 'user/v1', 'login_dashboard',array(
                'methods'  => 'POST',
                'callback' => 'wplms_usr_login',
      ));
});

function wplms_usr_login($request) {

    $cred = array();
    $cred['user_login']    = $request["username"];
    $cred['user_password'] = $request["password"];
    $cred['remember']      = false;

    $user = wp_signon( $cred, false );  // Verify the user.

    // message reveals if the username or password are correct.
    if ( is_wp_error($user) ) {
        echo $user->get_error_message();
        return $user;
    }

    wp_set_current_user( $user->ID, $user->user_login );



    $response = new WP_REST_Response('Success! You are logged in', array('status' => 200) );
    $response->set_status(200);

    return $response;
}

/*
 * 10. User dashboard
 */
add_action('rest_api_init', function () {
  register_rest_route( 'usr/v1', 'dashboard',array(
                'methods'  => 'POST',
                'callback' => 'wplms_usr_dashboard',
      ));
});

function wplms_usr_dashboard($request) {
    $dashboard = bp_course_member_userview($request['user_id']);

    $response = new WP_REST_Response( $dashboard);
    $response->set_status(200);

    return $response;
}

/*
 * 11. User enrolled courses
 */

add_action('rest_api_init', function () {
  register_rest_route( 'wplmscourse/v1', 'user_courses/(?P<user>\d+)',array(
                'methods'  => 'GET',
                'callback' => 'get_all_courses_of_an_user'
      ));
});

function get_all_courses_of_an_user($request) {

    $courses = bp_course_get_user_courses($request['user']);

    if (empty($courses)) {
        return new WP_Error( 'No posts available', 'There is no posts available right now! Please try again later', array('status' => 404) );

    }

    $response = new WP_REST_Response(array_unique($courses));
    $response->set_status(200);

    return $response;
}

/*
 * 12. Insert course review
 */
add_action('rest_api_init', function () {
  register_rest_route( 'review/v1', 'add',array(
                'methods'  => 'POST',
                'callback' => 'add_post_review',
                'permission_callback' => '__return_true',

      ));
});

function add_post_review($request) {

    $time = current_time('mysql');

//    if ('post_type' == 'course') {

        $data = array(
            'comment_post_ID' => $request['post_id'],
            'comment_author' => $request['name'],
            'comment_author_email' => $request['email'],
            'comment_content' => $request['content'],
            'comment_date' => $time,
            'comment_approved' => 1,
            'comment_type' => 'custom-comment-class'
        );

        $added = wp_insert_comment($data);

        $rating_add = update_post_meta($request['post_id'], 'rating_count', $request['star']);
//    }

    if ($added == false || $rating_add == false) {
        return new WP_Error( 'Nothing to add', 'You can not add a comment right now! Please try again later!', array('status' => 404) );

    }

    $response = new WP_REST_Response('Success! Review added to '.get_the_title($request['post_id']), array('status' => 200));
    $response->set_status(200);

    return $response;
}

/*
 * Get Review
 */
add_action('rest_api_init', function () {
  register_rest_route( 'wplmscourse/v1', 'getreviews/(?P<course_id>\d+)',array(
                'methods'  => 'GET',
                'callback' => 'get_course_review'
      ));
});

function get_course_review($request) {

    $args = array(
		'status' => 'approve',
		'post_id' => $request['course_id']
    );
    $comments = bp_course_get_course_reviews( $args );


    if (empty($comments)) {
        return new WP_Error( 'No comments', 'No comment found for the post!', array('status' => 404) );

    }

    $response = new WP_REST_Response($comments);
    $response->set_status(200);

    return $response;
}

/*
 * 13. Get Course reviews
 */
add_action('rest_api_init', function () {
  register_rest_route( 'product_reviews/v1', 'get/(?P<course_id>\d+)/(?P<user_id>\d+)',array(
                'methods'  => 'GET',
                'callback' => 'certificate_validation_and_get_certificate'
      ));
});

function certificate_validation_and_get_certificate($request) {

    $args = array (
        'post_type' => 'product',
        'post_id' => $request['product_id']
    );

    $comments = get_comments( $args );

    if (empty($comments)) {
        return new WP_Error( 'No reviews', 'There is review published for '.get_the_title($request['product_id']).' yet!', array('status' => 404) );

    }

    $response = new WP_REST_Response($comments);
    $response->set_status(200);

    return $response;
}

/*
 * 15. Certificate validation and show the certificate
 */
add_action('rest_api_init', function () {
  register_rest_route( 'wplmscourse/v1', 'certificate/(?P<course_id>\d+)/(?P<user_id>\d+)',array(
                'methods'  => 'GET',
                'callback' => 'get_all_reviews'
      ));
});

function get_all_reviews($request) {

    $certificate_validation = bp_course_validate_certificate(array('course_id' =>$request['course_id'],'user_id'=> $request['user_id']));

    if (!is_null($certificate_validation)) {
        return new WP_Error( 'Not valid', 'The certificate is not valid', array('status' => 404) );

    }

    if (!filter_var(bp_get_course_certificate(array('course_id' =>73,'user_id'=> 1)),FILTER_VALIDATE_URL)) {
        return new WP_Error( 'Not availabe', 'Certificate not found', array('status' => 404) );
    }

    $response = new WP_REST_Response(bp_get_course_certificate(array('course_id' =>73,'user_id'=> 1)));
    $response->set_status(200);

    return $response;
}