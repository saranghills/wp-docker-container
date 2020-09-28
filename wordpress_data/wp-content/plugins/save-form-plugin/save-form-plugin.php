<?php
/**
 * Plugin Name:       Save Form Plugin
 * Plugin URI:        http://gauthamsarang.in/plugins/save-form-plugin/
 * Description:       Displays a form using [save-form] shortcode. The form data is submitted to a separate table. The submitted data can be seen on the plugin admin page. The table in the database can be renamed on the plugin admin page. 
 * Version:           1.00
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Gautham Sarang
 * Author URI:        http://gauthamsarang.in/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       save-form-plugin
 * Domain Path:       /languages
 */

 // If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//version
global $save_form_version;
$save_form_version = '1.00';

//activate plugin actions
function sfp20_activation() {

    //create table 
    global $wpdb;
	$table_save_form   = $wpdb->prefix . 'save_form_data';
	$charset_collate   = $wpdb->get_charset_collate();

    //SQL Query
	$sql = "CREATE TABLE $table_save_form (
	  u_id mediumint(9) NOT NULL AUTO_INCREMENT,
      u_fname varchar(255) DEFAULT '' NOT NULL,
      u_lname varchar(255) DEFAULT '' NOT NULL,
      u_email varchar(255) DEFAULT '' NOT NULL,
      u_phone varchar(255) DEFAULT '' NOT NULL,
      u_country varchar(255) DEFAULT '' NOT NULL,
      u_dob varchar(255) DEFAULT '' NOT NULL,
	  u_notes varchar(255) DEFAULT '' NOT NULL,
	  PRIMARY KEY  (u_id)
	) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( array($sql) );
    
    //Add plugin options
    add_option( 'save_form_version', $save_form_version );
    add_option( 'sfp20_form_option', array('table_name' => 'Save Form Data') );

    //Arguements to create page with shortcode to display the Application Form
    $form_page = array(
        'post_title'    => 'Submit Your Application',
        'post_content'  => '[save-form]',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type'     => 'page',
    );
   
    //Create Page
    wp_insert_post( $form_page );

}
register_activation_hook( __FILE__, 'sfp20_activation' );

//deactivating plugin actions
function sfp20_deactivation() {

    /**
     * The Following is to be moved to uninstall.php 
     * Keeping it here for the convenience of testing it on 'Deactivate' rather than 'Delete' the plugin
     */

    
    global $wpdb;

    if(get_option( 'table_name' )){
        $table_name = $wpdb->prefix . preg_replace('[\-]','_', sanitize_title(get_option( 'table_name')));
    }else{
        $table_name = $wpdb->prefix .'save_form_data';
    }

    //drop table
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

    //remove saved plugin options
    delete_option( 'sfp20_form_option' );
    delete_option( 'save_form_version' );

}
register_deactivation_hook( __FILE__, 'sfp20_deactivation' );


//register scripts and styles. Enqueue them only within shortcode
add_action( 'wp_enqueue_scripts', 'sfp20_scripts' );
function sfp20_scripts() {
    wp_register_style('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',array(),'4.0.0', false);
    wp_register_style('sfp20_style', plugin_dir_url( __FILE__ ).'includes/css/sfp20_style.css', array('bootstrap'),'1.0.0', false);
    wp_register_style('exofont', 'https://fonts.googleapis.com/css2?family=Exo:wght@400;700&display=swap', '','', false);
    wp_register_script('bootstrapjs', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js',array('jquery'),'4.0.0', false);
    wp_register_script('sfp20_main', plugin_dir_url( __FILE__ ).'includes/js/sfp20_main.js',array('jquery'),'4.0.0', false);
    wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
}


/* *
* plugin options page
* 
*/
class SFPSettingsPage
{
    //Holds the values to be used in the fields callbacks
    private $options;

    //Startup
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_filter( 'pre_update_option_sfp20_form_option', array($this, 'replace_table'), 10, 2);
    }

    //Plugin Options Page
    public function add_plugin_page()
    {
        //Navigation, placing it under Settings
        add_options_page(
            'Settings Admin', 
            'Save Form Settings', 
            'manage_options', 
            'sfp20-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    //Options page callback function
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'sfp20_form_option' );
        ?>
        <div class="wrap">
            <h1>Application Form Settings</h1>
            <form action="options.php" method="post">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'sfp20_form_group' );
                do_settings_sections( 'sfp20-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    //Register and add settings
    public function page_init()
    {    
        
        register_setting(
            'sfp20_form_group', 
            'sfp20_form_option', 
            array( $this, 'tablename_sanitize' ) 
        );

        add_settings_section(
            'sfp20_section_id', 
            '', // No title given here. We are using tabs
            array( $this, 'table_settings_info' ), 
            'sfp20-setting-admin' 
        );       
    }

    /**
     * Renaming table 
     * 1. Delete existing table
     * 2. Create new table
     * 3. Updates 'table_name' option to the new table name
     */
    public function replace_table( $new_value, $old_value ) {

        if($old_value){
            $old_table_name = preg_replace('[\-]','_', sanitize_title($old_value['table_name'])); 
        }else{
            $old_table_name = 'save_form_data';
        }

        if($new_value['table_name'] === $old_value['table_name']){
                //Maybe display a notice? Or prevent the form from submitting?
        }else{ 
        global $wpdb;

        //remove old table 
        $drop_table_name = $wpdb->prefix . $old_table_name ; 
        $wpdb->query("DROP TABLE IF EXISTS {$drop_table_name}");

        //create new table with the new name
        $charset_collate   = $wpdb->get_charset_collate();
        $new_table_name = $wpdb->prefix.preg_replace('[\-]','_', sanitize_title($new_value['table_name'])); 
        
            $sql = "CREATE TABLE $new_table_name (
              u_id mediumint(9) NOT NULL AUTO_INCREMENT,
              u_fname varchar(255) DEFAULT '' NOT NULL,
              u_lname varchar(255) DEFAULT '' NOT NULL,
              u_email varchar(255) DEFAULT '' NOT NULL,
              u_phone varchar(255) DEFAULT '' NOT NULL,
              u_country varchar(255) DEFAULT '' NOT NULL,
              u_dob varchar(255) DEFAULT '' NOT NULL,
              u_notes varchar(255) DEFAULT '' NOT NULL,
              PRIMARY KEY  (u_id)
            ) $charset_collate;";
            
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( array($sql) );  
        }
        return $new_value;
     
     }
    //Sanitize table_name field
    public function tablename_sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['table_name'] ) )
            $new_input['table_name'] = sanitize_text_field( $input['table_name'] );
        return $new_input;
    }

    /**
     * Display submissions in a table. 
     * Using WP_List_Table Class
     */

    //Get all submission
    function sfp20_get_all_submission( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'number'     => 20,
            'offset'     => 0,
            'orderby'    => 'id',
            'order'      => 'ASC',
        );

        $args      = wp_parse_args( $args, $defaults );
        $cache_key = 'submission-all';
        $items     = wp_cache_get( $cache_key, 'save-form-plugin' );

        if ( false === $items ) {
            $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'save_form_data ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'] );

            wp_cache_set( $cache_key, $items, 'save-form-plugin' );
        }

        return $items;
    }

    //Fetch all submission from database
    function sfp20_get_submission_count() {
        global $wpdb;
        return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'save_form_data' );
    }

    //Fetch a single submission from database
    function sfp20_get_submission( $id = 0 ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'save_form_data WHERE id = %d', $id ) );
    }

    /** 
     * Section Tabs
     * Tab one displays the data from the table
     * Tab two displays the Table name input field prefilled with current table name
     */

    public function table_settings_info() 
    {

    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    include_once(plugin_dir_path( __FILE__).'includes/class-submission-list-table.php');
    include_once(plugin_dir_path( __FILE__).'includes/submission-functions.php');

    //Get the active tab from the $_GET param
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'form_data';
    ?>
        <div class="wrap">
            <!-- Tab Navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="?page=sfp20-setting-admin&tab=form_data" class="nav-tab <?php echo $active_tab == 'form_data' ? 'nav-tab-active' : ''; ?>">Form Data</a>
            <a href="?page=sfp20-setting-admin&tab=rename_table" class="nav-tab <?php echo $active_tab == 'rename_table' ? 'nav-tab-active' : ''; ?>">Rename Table</a>     
        </h2>
    <?php
        if( $active_tab == 'form_data' ) {
        ?>
    <!-- Tab 1 content -->
    <h2><?php _e( 'Submission Data', 'save-form-plugin' ); ?></h2>
        
    <!-- Table for listing the submissions -->
    <form method="post">
        <input type="hidden" name="page" value="ttest_list_table">

        <?php
        $list_table = new sfp20_WP_List();
        $list_table->prepare_items();
        $list_table->display();
        ?>
    </form>
    
    <?php } else { ?>
    
        <h2>From Data Table</h2>
            <div class="notice notice-error">
                <h2><strong>Warning</strong></h2>
                <p>Renaming the table might reset the table data. Proceed with care. You are warned!  </p>
            </div>
        
        <?php
            //Table name field
            printf(
                '<label>Table Name: </label><input type="text" id="table_name" name="sfp20_form_option[table_name]" value="%s" maxlength="25" />',
                 isset( $this->options['table_name'] ) ? esc_attr( $this->options['table_name']) : 'Save Form Data'
                    );
        } 
        ?>
        </div>
  <?php
    }
}

//Call the class
if( is_admin() )
    $fdc_settings_page = new SFPSettingsPage();

/**
 * Shortcode to display the form [save-form]
 * Enqueuing necessary scripts and styles
 * The form is on a seperate file for easy editing
 */

function save_form_shortcode($atts){

    wp_enqueue_style( 'bootstrap' );
    wp_enqueue_style( 'sfp20_style' ); 
    wp_enqueue_style( 'exofont');
    wp_enqueue_style( 'jquery-ui' );  
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'bootstrapjs' );
    wp_enqueue_script( 'sfp20_main' );  
    
    ob_start();
    include_once(WP_PLUGIN_DIR . '/save-form-plugin/includes/theform.php'); 
    return ob_get_clean();

}
add_shortcode('save-form', 'save_form_shortcode');

/**
 * Load special page template for the Application Submit Page where the shortcode lives
 * This style is applicable only on this page, which is created on activating this plugin
 */
function sfp20_template(){
    if(is_page('submit-your-application')){
        $template = WP_PLUGIN_DIR . '/save-form-plugin/includes/page-template/page-submit-your-application.php';
    }else{
        $template = get_template_part('index');
    }
    return $template;
} 
add_filter('template_include', 'sfp20_template');

function wpse255804_add_page_template ($templates) {
    $templates['page.php'] = 'Submit Your Application';
    return $templates;
    }
add_filter ('theme_page_templates', 'wpse255804_add_page_template');

function wpse255804_redirect_page_template ($template) {
    if ('page.php' == basename ($template))
        $template = WP_PLUGIN_DIR . '/save-form-plugin/includes/page-template/page-submit-your-application.php';
    return $template;
    }
add_filter ('page_template', 'wpse255804_redirect_page_template');

/**
 * Ajax operations for saving data from the form
 * Checks for duplicate on the email field (ignoring other fields for now)
 * Prepares the message and send to the ajax output
 */

add_action('wp_head', 'sfp20_ajaxurl');
add_action('wp_ajax_sfp20_ajax', 'sfp20_ajax');  // for admins only
add_action('wp_ajax_nopriv_sfp20_ajax', 'sfp20_ajax'); // for ALL users

//so that we don't have the Bad request error
function sfp20_ajaxurl() {
   echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

function sfp20_ajax(){

    if ( ! isset( $_POST['u_nonce'] ) || ! wp_verify_nonce( $_POST['u_nonce'], 'sfp20_form_nonce' ) ) 
    {
        $message = '<div class="p-5 mb-2 bg-danger text-white text-lg sfp20_ajax_msg"><h3>Sorry, your nonce did not verify.</h3></div>';
    if(! isset($_POST['u_fname']) || ! isset($_POST['u_lname']) || ! isset($_POST['u_email'])){
        $message = '<div class="p-5 mb-2 bg-danger text-white text-lg sfp20_ajax_msg"><h3>Sorry, did you forget to enter your first name, last name or email? Try again.</h3></div>';
    }
    echo $message;
    exit;

    } else {

       //making variables from $_POST
        foreach ($_POST as $key => $value){ 
            if($key == 'action' || $key == 'u_nonce'){
                //leave this part for now
            }else{
                $data[$key] = $value;
            }
        }

        global $wpdb;
        $tablename = get_option('sfp20_form_option');
        $table = $wpdb->prefix.preg_replace('[\-]','_', sanitize_title($tablename['table_name']));
        
        //Check whether the email is already there
        $email = $data['u_email'];
        $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE u_email = '$email' ");
        if($rowcount > 0){
            $message = '<div class="p-5 mb-2 bg-danger text-white text-lg sfp20_ajax_msg"><h3>Sorry! <br>You have already registered! Ref:<strong>'.$rowcount.'</strong></h3></div>';
            echo $message ;
            exit;
        }
        
        //format for insert
        $format = array('%s','%s','%s','%d','%s','%d');

        //insert data
        $wpdb->prepare($wpdb->insert($table,$data,$format));
        $my_id = $wpdb->insert_id;
        
        if($my_id > 0){
            $message = '<div class="p-5 mb-2 bg-success text-white text-lg sfp20_ajax_msg"><h3>Congratulations! Your Application has been submitted</h3></div>';
        }else{
            $message = '<div class="p-5 mb-2 bg-danger text-white text-lg sfp20_ajax_msg"><h3>Sorry! <br>An error occured!</h3></div>';
        }

        echo $message ;
    }

   wp_die();//obviously! :D
}

