<?php

/**
 * class extends WP_List_Table class, gets data from the table and creates a table with pagination according to the data.
 * 
 * 
 */
class CRED_Custom_Fields_List_Table extends WP_List_Table implements CRED_Singleton {

    private $_post_type = '';
    private $_show_private = false;

    /**
     * method calls parent's construct with array parameters  
     * 
     */
    function __construct() {
        parent::__construct( array(
            'plural' => 'list_customfields', //plural label, also this well be one of the table css class
            'singular' => 'list_customfield', //Singular label
            'ajax' => false //We won't support Ajax for this table
        ) );
    }

    function no_items() {
        _e( 'No fields were found.', 'wp-cred' );
    }

    function get_bulk_actions() {

        $actions = array();
        return $actions;
    }

    /**
     * Display the bulk actions dropdown.
     * Added input $args in order to have compatibility with WP4+ https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/187413931/comments
     *
     * @since 3.1.0
     * @access public
     */
    function bulk_actions($args = array()) {
        $screen = get_current_screen();

        if ( is_null( $this->_actions ) ) {
            $no_new_actions = $this->_actions = $this->get_bulk_actions();
            // This filter can currently only be used to remove actions.
            //$this->_actions = apply_filters( 'bulk_actions-cred' . $screen->id, $this->_actions );
            $this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
            $two = '';
        } else {
            $two = '2';
        }

        if ( empty( $this->_actions ) )
            return;

        echo "<select name='action$two'>\n";
        echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions', 'wp-cred' ) . "</option>\n";

        foreach ( $this->_actions as $name => $title ) {
            $class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

            echo "\t<option value='$name'$class>$title</option>\n";
        }

        echo "</select>\n";

        submit_button( __( 'Apply', 'wp-cred' ), 'button-secondary action', false, false, array('id' => "doaction$two") );
        echo "\n";
    }

    /**
     * method overwrites WP_List_Table::get_columns() method and sets the names of the table fields 
     * 
     */
    function get_columns() {
        return $columns = array(
            'cred_field_name' => __( 'Field', 'wp-cred' ),
            'cred_cred_type' => __( 'Toolset Forms Field Type', 'wp-cred' ),
            'cred_actions' => __( 'Actions', 'wp-cred' )
        );
    }

    /**
     * method sets the names of the sortable fields 
     * 
     */
    function get_sortable_columns() {
        return $sortable = array();
    }

    /**
     * method gets data to be display inside the table sets pagination data and sets items fields of the parent class 
     * 
     */
    function prepare_items() {
        global $wpdb, $_wp_column_headers;

	    $show_private = false;
	    if ( isset( $_POST['posttype'] )
		    && ( ! isset( $_POST['show_private'] )
			    || '1' != $_POST['show_private'] )
	    ) {
		    $show_private = false;
	    } elseif ( isset( $_REQUEST['show_private'] ) && '1' == $_REQUEST['show_private'] ) {
		    $show_private = true;
	    }


        if ( isset( $_POST['posttype'] ) ) {
            $post_type = $_POST['posttype'];
        } elseif ( isset( $_REQUEST['posttype'] ) ) {
            $post_type = $_REQUEST['posttype'];
        } else {
            $post_type = '';
        }

        $this->_post_type = $post_type;
        $this->_show_private = $show_private;

        $screen = get_current_screen();

        // sorting
        $orderby = (!empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'post_title';
        $order = (!empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
        //How many to display per page?
        $perpage = CRED_Helper::get_current_screen_per_page();
        if ( isset( $_REQUEST['wp_screen_options'] ) ) {
            if ( isset( $_REQUEST['wp_screen_options']['option'] )
                && 'cred_per_page' == $_REQUEST['wp_screen_options']['option']
                && isset( $_REQUEST['wp_screen_options']['value'] )
            ) {
                $perpage = intval( $_REQUEST['wp_screen_options']['value'] );
            }
        } elseif ( isset( $_REQUEST['per_page'] ) ) {
            $perpage = intval( $_REQUEST['per_page'] );
        }

        //Which page is this?
        $paged = intval( array_key_exists( 'paged', $_GET ) ? $_GET['paged'] : 1  );
        /**
         * sanitize paged
         */
        if ( $paged < 1 ) {
            $paged = 1;
        }

        $this->items = array();
        $totalitems = 0;

        $fm = CRED_Loader::get( 'MODEL/Fields' );
        /* -- Fetch the items -- */
        if ( !empty( $post_type ) ) {
            $totalitems = $fm->getPostTypeCustomFields( $post_type, array(), $show_private, -1, $perpage );
            if ( ($paged - 1) * $perpage > $totalitems ) {
                $paged = 1;
            }
            $this->items = $fm->getPostTypeCustomFields( $post_type, array(), $show_private, $paged, $perpage, $orderby, $order );
        }

        /* -- Register the pagination -- */
        //How many pages do we have in total?
        $totalpages = ceil( $totalitems / $perpage );
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
            'paged' => $paged,
            'posttype' => $post_type,
            'show_private' => ($show_private) ? '1' : '0'
        ) );


        //The pagination links are automatically built according to those parameters

        /* � Register the Columns � */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
    }

    /**
     * Get the current page number
     *
     * @since 3.1.0
     * @access protected
     *
     * @return int
     */
    function get_pagenum() {
        $pagenum = isset( $this->_pagination_args['paged'] ) ? absint( $this->_pagination_args['paged'] ) : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    /**
     * Display the pagination.
     *
     * @since 3.1.0
     * @access protected
     */
    function pagination($which) {
        if ( empty( $this->_pagination_args ) ) {
            return;
        }

        $total_items = $this->_pagination_args['total_items'];
        $total_pages = $this->_pagination_args['total_pages'];
        $per_page = $this->_pagination_args['per_page'];
        $paged = $this->_pagination_args['paged'];
        $posttype = $this->_pagination_args['posttype'];
        $show_private = $this->_pagination_args['show_private'];

        $output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current = $this->get_pagenum();

        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $current_url = remove_query_arg( array('hotkeys_highlight_last', 'hotkeys_highlight_first'), $current_url );

        $current_url = remove_query_arg( array('posttype', 'show_private'), $current_url );

        $current_url = add_query_arg( 'posttype', $posttype, $current_url );
        $current_url = add_query_arg( 'show_private', $show_private, $current_url );

        //cred-155
        //Fixed pagination issue
        //$current_url = esc_url($current_url);
        $page_links = array();

        $disable_first = $disable_last = '';
        if ( $current == 1 ) {
            $disable_first = ' disabled';
        }
        if ( $current == $total_pages ) {
            $disable_last = ' disabled';
        }

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>", 'first-page' . $disable_first, esc_attr__( 'Go to the first page' ), esc_url( remove_query_arg( 'paged', $current_url ) ), '&laquo;' );

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>", 'prev-page' . $disable_first, esc_attr__( 'Go to the previous page' ), esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ), '&lsaquo;' );

        if ( 'bottom' == $which ) {
            $html_current_page = $current;
        } else {
            $html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='paged' value='%s' size='%d' />", esc_attr__( 'Current page' ), $current, strlen( $total_pages ) );
        }

        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging', 'wp-cred' ), $html_current_page, $html_total_pages ) . '</span>';

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>", 'next-page' . $disable_last, esc_attr__( 'Go to the next page' ), esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ), '&rsaquo;' );

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>", 'last-page' . $disable_last, esc_attr__( 'Go to the last page' ), esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ), '&raquo;' );

        $pagination_links_class = 'pagination-links';
        if ( !empty( $infinite_scroll ) ) {
            $pagination_links_class = ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }

        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav($which) {
        if ( $which == "top" ) {
            // get custom post types not managed by Types
            $custom_posts = CRED_Loader::get( 'MODEL/Fields' )->getPostTypesWithoutTypes();
            ?>
            <span style='margin-right:20px'><?php _e( 'Post Type:&nbsp;', 'wp-cred' ); ?></span>
            <select id='cred_custom_posts' name='posttype'>
                <option value="" disabled="disabled" selected="selected" style="display:none"><?php _e( 'Select Post Type', 'wp-cred' ); ?></option>
                <?php
                foreach ( $custom_posts as $cp ) {
                    if ( isset( $cp['type'] ) && $cp['type'] == $this->_post_type )
                        echo '<option value="' . $cp['type'] . '" selected="selected">' . $cp['name'] . '</option>';
                    else
                        echo '<option value="' . $cp['type'] . '">' . $cp['name'] . '</option>';
                }
                ?>
            </select>
            <span style='margin: 0 20px'><?php _e( 'Show Hidden Fields', 'wp-cred' ); ?></span>
            <input name='show_private' type='checkbox' value='1' <?php if ( $this->_show_private ) echo 'checked="checked"'; ?> />
            <input type='submit' style='margin-left:20px' class='button' value="<?php _e( 'Apply', 'wp-cred' ); ?>" />
            <span style='margin-left:15px;'><img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-feedback" title="" alt="" /></span>
            <?php
        }
    }

    /**
     * method forms the data output style 
     * 
     */
    function display_rows() {
        $path = admin_url( 'admin.php' ) . '?page=CRED_Fields';

        //Get the records registered in the prepare_items method
        $records = $this->items;
        $cred_fields = CRED_Loader::get( 'MODEL/Fields' )->getCustomFields( $this->_post_type );
        $default_types = CRED_Loader::get( 'MODEL/Fields' )->getTypesDefaultFields( true );

        //Get the columns registered in the get_columns and get_sortable_columns methods
        list( $columns, $hidden ) = $this->get_column_info();

        //Loop for each record
        if ( empty( $records ) ) {
            return false;
        }

        foreach ( $records as $rec ) {
            //Open the line
            $field_id = $rec;

            $ignore = false;
            $credfieldtype = '<strong>' . __( 'Not Set', 'wp-cred' ) . '</strong>';
            $credfieldname = '<strong>' . __( 'Not Set', 'wp-cred' ) . '</strong>';
            if ( isset( $cred_fields[$rec] ) ) {
                $credfieldtype = $cred_fields[$rec]['type'];
                $credfieldname = isset( $default_types[$cred_fields[$rec]['type']] ) ? $default_types[$cred_fields[$rec]['type']]['title'] : $credfieldname;

                if ( isset( $cred_fields[$rec]['_cred_ignore'] ) )
                    $ignore = true;
            }
            // set api ajax paths
            $setfieldpath = CRED_CRED::route( '/Generic_Fields/getCustomField' . '?post_type=' . $this->_post_type . '&field_name=' . $rec . '&_wpnonce=' . wp_create_nonce( '_cred_customfield' ) . '&TB_iframe=true&width=600&height=450' );
            $editfieldpath = CRED_CRED::route( '/Generic_Fields/getCustomField' . '?post_type=' . $this->_post_type . '&field_name=' . $rec . '&_wpnonce=' . wp_create_nonce( '_cred_customfield' ) . '&TB_iframe=true&width=600&height=450' );
            $removefieldpath = CRED_CRED::route( '/Generic_Fields/removeCustomField' . '?post_type=' . $this->_post_type . '&field_name=' . $rec . '&_wpnonce=' . wp_create_nonce( '_cred_customfield' ) );

            echo '<tr id="' . $field_id . '">';

            foreach ( $columns as $column_name => $column_display_name ) {
                //Style attributes for each col
                $class = "class='$column_name column-$column_name'";
                $style = "";
                if ( in_array( $column_name, $hidden ) )
                    $style = ' style="display:none;"';
                $attributes = $class . $style;

                //Display the cell
                switch ($column_name) {
                    case "cred_field_name":
                        echo '<td ' . $attributes . '><strong><a class="thickbox" href="' . $editfieldpath . '" title="" title="' . __( 'Edit', 'wp-cred' ) . '">' . stripslashes( $rec ) . '</a>';
                        echo '</td>';
                        break;
                    case "cred_cred_type":
                        echo '<td ' . $attributes . '><span class="cred-field-type" style="margin-right:15px">' . $credfieldname . '</span></td>';
                        break;
                    case "cred_actions":
                        $actions = array(
                            '<a style="margin-right:10px" class="cred-field-actions _cred-field-set thickbox" href="' . $setfieldpath . '" title=\'' . esc_attr( __( 'Set field type', 'wp-cred' ) ) . '\'>' . __( 'Add', 'wp-cred' ) . '</a>',
                            '<a style="margin-right:10px" class="cred-field-actions _cred-field-edit thickbox" href="' . $editfieldpath . '" title=\'' . esc_attr( __( 'Edit field settings', 'wp-cred' ) ) . '\'>' . __( 'Edit', 'wp-cred' ) . '</a>',
                            '<a style="margin-right:10px" class="cred-field-actions _cred-field-remove" href="' . $removefieldpath . '" title=\'' . esc_attr( __( 'Remove this field as a Toolset Forms field type', 'wp-cred' ) ) . '\'>' . __( 'Remove', 'wp-cred' ) . '</a>',
                        );
                        $act_out = implode( '', $actions );
                        echo '<td ' . $attributes . '>' . $act_out . '</td>';
                        break;
                }
            }
            echo'</tr>';
        }
    }

}
