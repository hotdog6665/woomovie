<?php

/**
 * Register Movie Type
 */

add_action( 'init', 'wmv_movie_cpt' );
function wmv_movie_cpt() {

    $labels = array(
        'name' => _x( 'Movie', 'post type general name', 'woomovie' ),
        'singular_name' => _x( 'Movie', 'post type singular name', 'woomovie' ),
        'menu_name' => _x( 'Movies', 'admin menu', 'woomovie' ),
        'name_admin_bar' => _x( 'Movie', 'add new on admin bar', 'woomovie' ),
        'add_new' => _x( 'Add New', 'Movie', 'woomovie' ),
        'add_new_item' => __( 'Add New Movie', 'woomovie' ),
        'new_item' => __( 'New Movie', 'woomovie' ),
        'edit_item' => __( 'Edit Movie', 'woomovie' ),
        'view_item' => __( 'View Movie', 'woomovie' ),
        'all_items' => __( 'All Movies', 'woomovie' ),
        'search_items' => __( 'Search Movies', 'woomovie' ),
        'parent_item_colon' => __( 'Parent Movie:', 'woomovie' ),
        'not_found' => __( 'No Movies found.', 'woomovie' ),
        'not_found_in_trash' => __( 'No Movies found in Trash.', 'woomovie' )
    );

    $args = array(
        'description' => __( 'Movie', 'woomovie' ),
        'labels' => $labels,
        'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'revisions' ),
        'hierarchical' => false,
        'public' => true,
        'publicly_queryable' => true,
        'query_var' => true,
        'show_ui' => true,
        'menu_icon' => 'dashicons-format-video',
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        // 'menu_position' => 5,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'capability_type' => 'post',
    );

    register_post_type( 'movie', $args );

}

//* Create Movie Type custom taxonomy (category)
add_action( 'init', 'custom_type_taxonomy' );
function custom_type_taxonomy() {

    register_taxonomy( 'movie-type', 'movie',
        array(
            'labels' => array(
                'name' => _x( 'Movie Category', 'taxonomy general name', 'text_domain' ),
                'add_new_item' => __( 'Add New Movie Category', 'text_domain' ),
                'new_item_name' => __( 'New Movie Type', 'text_domain' ),
            ),
            'exclude_from_search' => true,
            'has_archive' => true,
            'hierarchical' => true,
            'rewrite' => array( 'slug' => 'movie-type', 'with_front' => false ),
            'show_ui' => true,
            'show_tagcloud' => false,
        )
    );

}

add_action('add_meta_boxes', 'my_extra_fields', 1);

function my_extra_fields() {
    add_meta_box( 'extra_fields', 'Additional field for movie', 'movie_price_field', 'movie', 'normal', 'high'  );
}

function movie_price_field( $post ){
    ?>
    <p><label>Movie price <input type="number" name="extra[price]" value="<?php echo get_post_meta($post->ID, 'price', 1); ?>"/></label></p>

    <input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
    <?php
}

// Save field when on save_post
add_action('save_post', 'my_extra_fields_update', 0);

/* Save data when on save_post */
function my_extra_fields_update( $post_id ){
    if ( !isset($_POST['extra_fields_nonce']) || !wp_verify_nonce($_POST['extra_fields_nonce'], __FILE__) ) return false;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return false;
    if ( !current_user_can('edit_post', $post_id) ) return false;

    if( !isset($_POST['extra']) ) return false;

    // ОК! Now need to save/delete data
    $_POST['extra'] = array_map('trim', $_POST['extra']);
    foreach( $_POST['extra'] as $key=>$value ){
        if( empty($value) ){
            delete_post_meta($post_id, $key); // delete if empty value
            continue;
        }

        update_post_meta($post_id, $key, $value);
    }
    return $post_id;
}
add_filter('woocommerce_get_price','reigel_woocommerce_get_price',20,2);
function reigel_woocommerce_get_price($price,$post){
    if ($post->post->post_type === 'movie') // change this to your post type
        $price = get_post_meta($post->id, "price", true); // assuming your price meta key is price
    return $price;
}

add_action( 'register_form', 'crf_registration_form' );
function crf_registration_form() {

    $skype = ! empty( $_POST['skype'] ) ? intval( $_POST['skype'] ) : '';

    ?>
    <p>
        <label for="year_of_birth"><?php esc_html_e( 'Skype', 'crf' ) ?><br/>
            <input type="text"
                   id="skype"
                   name="skype"
                   value="<?php echo esc_attr( $skype ); ?>"
                   class="input"
            />
        </label>
    </p>
    <?php
}