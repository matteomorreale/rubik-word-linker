<?php
	/*
	Plugin Name: Rubik Words Autolinker
	Version: 1.0
	Description: This plugin automatically creates links on certain words if they have been associated with a certain post
	Author: Matteo Morreale
	Author URI: https://matteomorreale.it
	*/

	// Matteo Morreale - CC Attribuzione non Commerciale
	// Hai libertà di riutilizzare, modificare e condividere questo sorgente ma non a fini commerciali, ed è necessario citarne l'autore

/**
 * This plugin creates automatic links in the posts according to the keywords that have been indicated in the various posts.
 * For example, if you have created a tab for the site CRISTO VELATO and you have associated the keyword "cristo velato", every time this keyword
 * will be present in a post, it will be automatically linked to the post that is talking 'bout to this place
 */

require_once("inc/db-handler.php");
require_once("inc/saving-catcher.php");
require_once("structured-data.php");

/** Hooks  
 *  Deactivated since we're using a query on posts, but may be usefull in the future with too many posts
register_activation_hook( __FILE__, 'wdlnk_activation' );
function wdlnk_activation(){
	global $wpdb;
	$table_name = $wpdb->prefix . "wdlnk";
	
	// If there's no table, we'll create one
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		// Initializing the DB
		$db_handler = new DbHandler();
		$db_handler->db_install();
	}
}
*/

/* Replace all words */
function replace_text_wps($text){
    if(function_exists("get_field") && !get_field("is_weekend_flag") ):
        // If there's a transiet set, than we take this instead of doin' the query again
        if(!empty(get_transient("wdlnk_replace_array"))){
            $replace = (array)json_decode(get_transient("wdlnk_replace_array"));
        }
        else{
            // Searching for all main keywords in posts
			$terms = get_terms( 'category', array(
				'hide_empty' => true,
			) );

			$categories_id_list = array();
			foreach($terms as $term){
				$categories_id_list[] = $term->term_id;
			}

            // Searching for all main keywords in posts
            $args = array(
                'posts_per_page' => -1,
                'post_type' => array("post"),
				'category__in' => $categories_id_list,
                'meta_key' => 'main_keyword',
                'meta_value' => array(''),
                'meta_compare' => 'NOT IN'
            );
            $meta_query = new WP_Query( $args );
    
            $replace = array();
    
            // Adding every word to the array of words to replace with the link to the post
            foreach($meta_query->posts as $p){
                $post_main_word = trim(get_field("main_keyword",$p->ID));
                $replace[mb_strtolower ($post_main_word)] = get_the_permalink($p->ID);
            }
			
            // Searching for all main keywords in points and pages
            $args = array(
                'posts_per_page' => -1,
                'post_type' => array("point","page"),
                'meta_key' => 'main_keyword',
                'meta_value' => array(''),
                'meta_compare' => 'NOT IN'
            );
            $meta_query = new WP_Query( $args );
    
            // Adding every word to the array of words to replace with the link to the post
            foreach($meta_query->posts as $p){
                $post_main_word = trim(get_field("main_keyword",$p->ID));
                //$replace[mb_strtolower ($post_main_word)] = '<a href="' . get_the_permalink($p->ID) . '">'.$post_main_word.'</a>';
				$replace[mb_strtolower ($post_main_word)] = get_the_permalink($p->ID);
            }
            
            // Getting all entity Titles
            $args = array(
                'posts_per_page' => -1,
                'post_type' => "point",
				'post__not_in' => array( $post->ID )
            );
            $meta_query = new WP_Query( $args );

            // Adding every title to the array of words to replace with the link to the post
            foreach($meta_query->posts as $p){
                $post_main_word = trim(get_the_title($p->ID));
                $replace[mb_strtolower ($post_main_word)] = get_the_permalink($p->ID);
            }
    
            // Setting the transient for future use
            set_transient("wdlnk_replace_array",json_encode($replace),172800); // Expires within two days
        }
		
		// Removing current post from the list, otherwise it'll link to himself
		unset($replace[mb_strtolower(trim(get_the_title(get_the_ID())))]);
		unset($replace[mb_strtolower(trim(get_field("main_keyword",get_the_ID())))]);
		
        // Replacing

		foreach( $replace as $word => $link ){
			$pattern = "/(?si)<a[^<]*>.*?<\\/a>(*SKIP)(*F)|\\b" . preg_quote($word) . "\\b/";
			$replacement = '<a href="' . $link . '" data="wordlink">' . ucfirst(preg_quote($word)) . '</a>';
			$text = preg_replace( $pattern, $replacement, $text );	
		} 
		
        //$text = str_ireplace(array_keys($replace), $replace, $text);
    endif;
    return $text;
}
 
add_filter('the_content', 'replace_text_wps');

/* If a post is published the transient must be deleted */
add_action('publish_post', 'wdlnk_transient_delete');
function wdlnk_transient_delete($post_id) {
    delete_transient("wdlnk_replace_array");
}