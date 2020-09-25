<?php

/**
 * Get all submission
 *
 * @param $args array
 *
 * @return array
 */



function sfp20_get_all_submission( $args = array() ) {

    global $wpdb;

    $tablename = get_option('sfp20_form_option');
    $table = $wpdb->prefix.preg_replace('[\-]','_', sanitize_title($tablename['table_name']));

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'u_id',
        'order'      => 'ASC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'submission-all';
    $items     = wp_cache_get( $cache_key, 'save-form-plugin' );
 
    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM  '.$table.' ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'] );

        wp_cache_set( $cache_key, $items, 'save-form-plugin' );
    }

    return $items;
}

/**
 * Fetch all submission from database
 *
 * @return array
 */
function sfp20_get_submission_count() {

    $tablename = get_option('sfp20_form_option');
    $table = preg_replace('[\-]','_', sanitize_title($tablename['table_name']));
    global $wpdb;

    return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix.$table );
}

/**
 * Fetch a single submission from database
 *
 * @param int   $id
 *
 * @return array
 */
function sfp20_get_submission( $id = 0 ) {

    $tablename = get_option('sfp20_form_option');
    $table = preg_replace('[\-]','_', sanitize_title($tablename['table_name']));  
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix.$table. 'WHERE u_id = %d', $id ) );
}
