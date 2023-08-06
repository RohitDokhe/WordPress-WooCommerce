<?php
// Load required WordPress files
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Custom WP_List_Table class to display user metadata
class User_Metadata_List_Table extends WP_List_Table {
    function __construct() {
        parent::__construct(array(
            'singular' => 'user_metadata',
            'plural' => 'user_metadata',
            'ajax' => false,
        ));
    }

    // Fetch user metadata
    function get_user_metadata() {
        // Get all users
        $users = get_users();

        // Prepare user metadata
        $user_metadata = array();
        foreach ($users as $user) {
            $user_id = $user->ID;
            $user_data = get_userdata($user_id);

            // Example: Displaying user ID, username, email, and role
            $user_metadata[] = array(
                'ID' => $user_id,
                'Username' => $user_data->user_login,
                'Email' => $user_data->user_email,
                'Role' => implode(', ', $user_data->roles),
            );

            // Add more user metadata fields as needed
        }

        return $user_metadata;
    }

    // Define columns for the table
    function get_columns() {
        return array(
            'cb' => '<input type="checkbox">',
            'ID' => 'User ID',
            'Username' => 'Username',
            'Email' => 'Email',
            'Role' => 'Role',
            'actions' => 'Actions', // Column for action buttons
        );
    }

    // Define bulk actions
    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete',
        );
        return $actions;
    }

    // Process bulk actions
    function process_bulk_action() {
        // Check if the action is delete and perform the necessary delete operation.
        if ('delete' === $this->current_action()) {
            $user_ids = isset($_REQUEST['user_metadata']) ? $_REQUEST['user_metadata'] : array();
            // Implement your code to delete the selected users based on their IDs.
            foreach ($user_ids as $user_id) {
                // Code to delete the user with ID $user_id
            }
        }
    }

    // Define default column values if a row does not have a value for a specific column
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'ID':
            case 'Username':
            case 'Email':
            case 'Role':
                return isset($item[$column_name]) ? $item[$column_name] : '';
            case 'actions':
                $edit_url = admin_url('admin.php?page=user_metadata_table&action=edit&user_id=' . $item['ID']);
                $actions = sprintf(
                    '<a href="%s" class="button button-primary">Edit</a> <a href="#" class="button button-secondary">Delete</a>',
                    esc_url($edit_url)
                );
                return $actions;
            default:
                return '';
        }
    }

    // Display the table
    function display_table() {
        $user_metadata = $this->get_user_metadata();
        $this->items = $user_metadata;
        $this->prepare_items();
        $this->display();
    }
}

// Function to add submenu page for the "Referral" custom post type
function referral_add_submenu_page() {
    add_submenu_page(
        'edit.php?post_type=referral', // Parent slug: 'edit.php?post_type=referral' represents the "Referral" custom post type menu
        'User Metadata Table', // Page title
        'User Metadata Table', // Menu title
        'manage_options', // Capability
        'user_metadata_table', // Submenu slug
        'referral_user_metadata_submenu_page' // Callback function to display the submenu page content
    );
}
add_action('admin_menu', 'referral_add_submenu_page');

// Callback function to display the submenu page content
function referral_user_metadata_submenu_page() {
    $user_metadata_table = new User_Metadata_List_Table();
    $user_metadata_table->display_table();

    // Process bulk actions if the form is submitted
    if ('delete' === $user_metadata_table->current_action()) {
        $user_metadata_table->process_bulk_action();
    }
}

