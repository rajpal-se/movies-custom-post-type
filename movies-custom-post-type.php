<?php
/**
 * Plugin Name:     Movies Custom Post Type
 * Plugin URI:      https://www.rps34.com/
 * Description:     Register Custom Post Type for Movies and add more many features
 * Author:          RPS34
 * Author URI:      https://www.rps34.com/
 * Version:         1.0
**/

/* 
 * Register Custom Post Type (movie)
 * */
function movie_cpt_init_handler() {
    $labels = array(
        'name'                  => __( 'Movies'),
        'singular_name'         => __( 'Movie'),
        'menu_name'             => __( 'Movies'),
        'name_admin_bar'        => __( 'Movie'),
        'add_new'               => __( 'Add New'),
        'add_new_item'          => __( 'Add New movie'),
        'new_item'              => __( 'New movie'),
        'edit_item'             => __( 'Edit movie'),
        'view_item'             => __( 'View movie'),
        'all_items'             => __( 'All movies'),
        'search_items'          => __( 'Search movies'),
        'parent_item_colon'     => __( 'Parent movies:'),
        'not_found'             => __( 'No movies found.'),
        'not_found_in_trash'    => __( 'No movies found in Trash.'),
        'featured_image'        => __( 'Movie Cover Image'),
        'set_featured_image'    => __( 'Set cover image'),
        'remove_featured_image' => __( 'Remove cover image'),
        'use_featured_image'    => __( 'Use as cover image'),
        'archives'              => __( 'Movie archives'),
        'insert_into_item'      => __( 'Insert into movie'),
        'uploaded_to_this_item' => __( 'Uploaded to this movie'),
        'filter_items_list'     => __( 'Filter movies list'),
        'items_list_navigation' => __( 'Movies list navigation'),
        'items_list'            => __( 'Movies list'),
    );     
    $args = array(
        'labels'             => $labels,
        'description'        => 'Movie custom post type.',
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'movie' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
        // 'taxonomies'         => array( 'post_tag', 'category' ),
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-format-video'
    );
      
    register_post_type( 'Movie', $args );
}
add_action( 'init', 'movie_cpt_init_handler' );


/* 
 * Add MetaBox
 * */
add_action('add_meta_boxes', function($post_type, $post){
    $custom_args = [
        'foo' => 'bar'
    ];
    add_meta_box('movies-cpt-id', 'Producer\'s Details', function($post, $custom_args){
        // echo '<pre>';
        // var_dump($custom_args);
        // echo $custom_args['args']['foo'];

        $name = get_post_meta($post->ID, 'metabox_producer_name', true);
        $email = get_post_meta($post->ID, 'metabox_producer_email', true);
        ?>
            <p>
                <label for="metabox_producer_name">Name</label><br>
                <input type="text" value="<?= $name ?>" name="metabox_producer_name" id="metabox_producer_name" placeholder="Producer name">
            </p>
            <p>
                <label for="metabox_producer_email">Email</label><br>
                <input type="text" value="<?= $email ?>" name="metabox_producer_email" id="metabox_producer_email" placeholder="Producer email">
            </p>
        <?php
    }, 'movie', 'side', 'high', $custom_args);
}, 10, 2);
add_action( 'save_post_movie', function($post_id, $post, $update){
    $metabox_producer_name = $_POST['metabox_producer_name'] ?? '';
    $metabox_producer_email = $_REQUEST['metabox_producer_email'] ?? '';
    update_post_meta($post_id, 'metabox_producer_name', $metabox_producer_name);
    update_post_meta($post_id, 'metabox_producer_email', $metabox_producer_email);
}, 10, 3);


/* 
 * Render Data to Custom Column And display meta data
 * */

add_action('manage_movie_posts_columns', function($post_columns){
    $post_columns = [
        'cb'            => '<input type="checkbox">',
        'title'         => 'Movie Title',
        'producer_name' => 'Producer Name',
        'producer_email'=> 'Producer Email',
        'date'          => 'Date'
    ];
    return $post_columns;
});

add_action('manage_movie_posts_custom_column', function($column_name, $post_id){
    switch($column_name){
        case 'producer_name':
            echo get_post_meta($post_id, 'metabox_producer_name', true);
            break;
            
        case 'producer_email':
        echo get_post_meta($post_id, 'metabox_producer_email', true);
        break;
    }
}, 10, 2);


/* 
 * Enable Sortable functionality for Custom Column
 * */
add_filter('manage_edit-movie_sortable_columns', function($sortable_columns){
    $sortable_columns['producer_name'] = 'ProducerName';
    $sortable_columns['producer_email'] = 'ProducerEmail';
    return $sortable_columns;

    /*
     * $sortable_columns[ $key ] = $value
     * $key   = <th>-tag-ID  : Used to identify the column (It is the ID of Custom Column)
     * $value = <any-name>   : It is displayed in URL
     * 
     * https://abc.com/wp-admin/edit.php?post_type=movie&orderby=ProducerName&order=asc
     * https://abc.com/wp-admin/edit.php?post_type=movie&orderby=ProducerEmail&order=desc
     * 
     * */
});



/* 
 * Add Custom Filters on Posts Listing (Table)
 * Here we have Implemented, List all Posts by Selected Author
 * */

add_action('add_meta_boxes', function($post_type, $post){
    add_meta_box('author-cpt-id', 'Choose Author', function($post){
        $author_id = get_post_meta($post->ID, 'metabox_author_id', true);
        ?>
            <p>
                <label for="metabox_author_id">Select Author</label><br>
                <select name="metabox_author_id" id="metabox_author_id">
                    <?php
                        $users = get_users([
                            // 'role' => 'my-book-reader'     //  etc... 
                        ]);
                        foreach($users as $user){
                            $selected = ($user->ID == $author_id) ? 'selected' : '';
                            echo "<option value='{$user->ID}' {$selected}>{$user->display_name}</option>";
                        }
                    ?>
                </select>
            </p>
        <?php
    }, 'movie', 'side', 'high');
}, 10, 2);

add_action( 'save_post_movie', function($post_id, $post, $update){
    $metabox_author_id = $_POST['metabox_author_id'] ?? '';
    update_post_meta($post_id, 'metabox_author_id', $metabox_author_id);
}, 10, 3);

add_action('restrict_manage_posts', function($post_type, $which){
    if($post_type == 'movie'){
        $author_id = isset($_GET['filter_by_author']) ? intval($_GET['filter_by_author']) : 0;
        wp_dropdown_users([
            'show_option_all'  => 'All Author',
            // 'role'              => 'my-book-reader',
            'id'                => 'filter_author_id',      // Any custom <id-text>
            'name'              => 'filter_by_author',      // Any custom <name-text>
            'show_count'        => true,
            'selected'          => $author_id
        ]);
    }
}, 10, 2);

add_filter('parse_query', function($query){
    global $typenow;    // "movie"
    global $pagenow;    // "edit.php"   // In URL (address) bar
    if($typenow == 'movie'){
        $author_id = isset($_GET['filter_by_author']) ? intval($_GET['filter_by_author']) : 0;
        if($author_id < 1) $author_id = '';
        $query->query_vars['meta_key'] = 'metabox_author_id';
        $query->query_vars['meta_value'] = $author_id;
    }
});