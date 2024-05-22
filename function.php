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

/* Replace all words */
function replace_text_wps($text){
    global $linked_entities; // Array per memorizzare le entità collegate
    $linked_entities = array();

    if(function_exists("get_field") && !get_field("is_weekend_flag") ):
        // If there's a transiet set, than we take this instead of doin' the query again
        if(!empty(get_transient("wdlnk_replace_array"))){
            $replace = (array)json_decode(get_transient("wdlnk_replace_array"));
        }
        else{
            // Searching for all main keywords in posts
            $terms = get_terms( 'category', array(
                'hide_empty' => true,
            ));

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
			
            // Searching for all main keywords in posts of type "point"
            $args = array(
                'posts_per_page' => -1,
                'post_type' => 'point',
                'meta_key' => 'main_keyword',
                'meta_value' => array(''),
                'meta_compare' => 'NOT IN'
            );
            $meta_query = new WP_Query( $args );

            // Adding every word to the array of words to replace with the link to the post
            foreach($meta_query->posts as $p){
                $post_main_word = trim(get_field("main_keyword",$p->ID));
                $replace[mb_strtolower ($post_main_word)] = get_the_permalink($p->ID);
            }

            // Setting the transient for future use
            set_transient("wdlnk_replace_array",json_encode($replace),172800); // Expires within two days
        }
		
		// Tolgo dalla lista il post corrente
		unset($replace[mb_strtolower(trim(get_the_title(get_the_ID())))]);
		if(!empty(get_field("main_keyword",get_the_ID()))){
				unset($replace[mb_strtolower(trim(get_field("main_keyword",get_the_ID())))]);	
		}
        // Replace words with links and store the linked entities
        foreach($replace as $word => $link) {
            $text = preg_replace_callback("/\\b$word\\b|(<a .*?<\\/a>)|(<img .*?>)/si", function($matches) use ($word, $link, &$linked_entities) {
                if (strpos($matches[0], '<a') === 0 || strpos($matches[0], '<img') === 0) {
                    return $matches[0];
                } else {
                    $linked_entities[$word] = $link; // Memorizza la parola chiave e il link
                    return '<a href="' . $link . '" >' . ucwords($word) . '</a>';
                }
            }, $text);
        }

        // Aggiungi l'elenco delle entità collegate alla fine del contenuto del post
        $text .= generate_entities_list();
    endif;

    return $text;
}

/* If a post is published the transient must be deleted */
add_action('publish_post', 'wdlnk_transient_delete');
function wdlnk_transient_delete($post_id) {
    delete_transient("wdlnk_replace_array");
}

function generate_entities_list(){
    global $linked_entities;
    if(!empty($linked_entities) && (count($linked_entities) < 3) ):
        $list_html = "<div class=\"post-entities\"><span class=\"post-entities-pre\">In questo articolo:</span><ul>";
        foreach ($linked_entities as $entity => $link) {
            $list_html .= "<li><a href='" . esc_url($link) . "'>" . esc_html($entity) . "</a></li>";
        }
        $list_html .= "</ul></div>";
        return $list_html;
    endif;
}

add_filter('the_content', 'replace_text_wps', 20);

add_action('wp_head', 'wps_custom_css');

function wps_custom_css(){
    echo '<style type="text/css">
        .post-entities {
            display: flex;
            font-size: 0.8em;
        }
        
        .post-entities ul {
            margin-top: 0px;
            margin-bottom: 0px;
            list-style: none;
            padding-left: 1em;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
        }
    </style>';
}