<?php
/**
 *  Hooks when a post is saved with ACF Fields in it
 */
add_action('acf/save_post', 'wdlnk_acf_save_post');
function wdlnk_acf_save_post( $post_id ) {
    global $wpdb;
    // Get newly saved values.
    $values = get_fields( $post_id );
    $wpdb_prefix = $wpdb->prefix;
    $wpdb_tablename = $wpdb_prefix.'wdlnk';
    // Check the new value of the keyword field.
    $keyword = get_field('field_601ad2d7c9da0', $post_id);
    $istances = get_field('field_601ad3a1c9da1', $post_id);

    if( $keyword ) {
        // Cerco la keyword a database
        $sql = "SELECT `id` FROM `$wpdb_tablename` WHERE ('keyword' = '$keyword') LIMIT 1";
        $result = $wpdb->get_results($sql);

        if(empty($result)): 
            // La keyword non è presente a database, va inserita
            $data = array(
                'keyword' => $keyword, 
                'post_id' => $post_id,
                'max_istances' => $istances,
                'url' => get_permalink($post_id)
            );
            $format = array('%s','%d', '%d', '%s');
            $wpdb->insert($wpdb_tablename,$data,$format);
            $my_id = $wpdb->insert_id;
            
            error_log(dump($my_id, true));
        endif;
    }
}