<?php
Class DbHandler{
    function db_install () {
        global $wpdb;
     
        $table_name = $wpdb->prefix . "wdlnk"; 

        $charset_collate = $wpdb->get_charset_collate();
        
        /*
        --Word id
        --Descrizione
        --ID del post a cui linkare
        --Numero massimo di ripetizioni della parola linkabili in un singolo post
        -- URL del post a cui linkare
        */

        $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          keyword tinytext NOT NULL, 
          post_id mediumint(99), 
          max_istances mediumint(99), 
          url varchar(99) DEFAULT '', 
          PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        try{
          dbDelta( $sql );
        }
        catch(Exception $e){
          error_log($e);
        }
     }
}