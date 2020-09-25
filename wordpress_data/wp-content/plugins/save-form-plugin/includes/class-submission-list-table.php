<?php
/**
 * Generated Class 
 */
if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class sfp20_WP_List extends \WP_List_Table {

    function __construct() {
        parent::__construct( array(
            'singular' => 'submission',
            'plural'   => 'submissions',
            'ajax'     => false
        ) );
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        _e( 'No Submissions so far', 'save-form-plugin' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'u_id':
                return $item->u_id;

            case 'u_fname':
                return $item->u_fname;

            case 'u_lname':
                return $item->u_lname;

            case 'u_email':
                return $item->u_email;

            case 'u_phone':
                return $item->u_phone;

            case 'u_country':
                return $item->u_country;

            case 'u_dob':
                return $item->u_dob;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'u_id'         => __( 'ID', 'save-form-plugin' ),
            'u_fname'      => __( 'First Name', 'save-form-plugin' ),
            'u_lname'      => __( 'Last Name', 'save-form-plugin' ),
            'u_email'      => __( 'Email', 'save-form-plugin' ),
            'u_phone'      => __( 'Phone Number', 'save-form-plugin' ),
            'u_country'    => __( 'Country', 'save-form-plugin' ),
            'u_dob'        => __( 'DOB', 'save-form-plugin' ),

        );

        return $columns;
    }

    /**
     * Render the designation name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_u_id( $item ) {

        $actions           = array();
        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=sfp20-setting-admin&action=edit&id=' . $item->id ), $item->id, __( 'Edit this item', 'save-form-plugin' ), __( 'Edit', 'save-form-plugin' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=sfp20-setting-admin&action=delete&id=' . $item->id ), $item->id, __( 'Delete this item', 'save-form-plugin' ), __( 'Delete', 'save-form-plugin' ) );

        return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'admin.php?page=sfp20-setting-admin&action=view&id=' . $item->id ), $item->u_id, $this->row_actions( $actions ) );
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array( 'name', true ),
        );

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'trash'  => __( 'Move to Trash', 'save-form-plugin' ),
        );
       // return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="submission_id[]" value="%d" />', $item->id
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views_() {
        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=sample-page' );

        foreach ($this->counts as $key => $value) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 10;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        $this->items  = sfp20_get_all_submission( $args );

        $this->set_pagination_args( array(
            'total_items' => sfp20_get_submission_count(),
            'per_page'    => $per_page
        ) );
    }


}


?>