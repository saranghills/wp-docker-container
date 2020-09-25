<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

/**
 * All the functions that should happen here is in the deactivate hook
 * Since this is for testing, kept it that way so that we don't have to delete the plugin to see the full function.
 */
$table_name = get_option( 'sfp20_form_option' );
$table_name = $table_name['table_name'];
// drop the datatable
global $wpdb;

if(get_option( 'table_name' )){
    $table_name = $wpdb->prefix . $table_name;
}else{
    $table_name = $wpdb->prefix .'save_form_data';
}

$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

//remove saved plugin options
delete_option( 'sfp20_form_option' );