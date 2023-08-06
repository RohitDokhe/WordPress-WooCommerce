<?php
/*
Plugin Name: Referral Plugin
Plugin URI:  https://www.example.com/referral-plugin
Description: A plugin for managing referrals.
Version:     1.0
Author:      Rohit Dokhe
Author URI:  https://www.example.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Define constants for the custom post type and shortcode
define('REFERRAL_POST_TYPE', 'referral');
define('REFERRAL_SHORTCODE', 'referral_form');

function your_plugin_enqueue_styles() {
    wp_enqueue_style( 'your-plugin-css', plugin_dir_url( __FILE__ ) . 'assets/css/form_styles.css' );
}

add_action( 'wp_enqueue_scripts', 'your_plugin_enqueue_styles' );

// Register custom post type
function referral_register_post_type() {
    $labels = array(
        'name' => 'Referrals',
        'singular_name' => 'Referral',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Referral',
        'edit_item' => 'Edit Referral',
        'new_item' => 'New Referral',
        'view_item' => 'View Referral',
        'search_items' => 'Search Referrals',
        'not_found' => 'No referrals found',
        'not_found_in_trash' => 'No referrals found in Trash',
        'parent_item_colon' => '',
        'menu_name' => 'Referrals'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'publicly_queryable' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'referral'),
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'),
        'menu_position' => 5,
        'menu_icon' => 'dashicons-smiley', // You can choose any icon from Dashicons
    );

    register_post_type(REFERRAL_POST_TYPE, $args);
}
add_action('init', 'referral_register_post_type');

// Shortcode for user form



// Enqueue the necessary scripts for Ajax and jQuery
function referral_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('referral-ajax', plugin_dir_url(__FILE__) . 'js/referral-ajax.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'referral_enqueue_scripts');

// Ajax callback for referral code validation
function referral_ajax_validate_code() {
    // Simulate referral code validation (replace this with your actual validation logic)
    $valid_referral_code = 'validcode123';

    if (isset($_POST['referral_code']) && $_POST['referral_code'] === $valid_referral_code) {
        echo 'valid';
    } else {
        echo 'invalid';
    }
    wp_die(); // Always use wp_die() at the end of an Ajax callback.
}
add_action('wp_ajax_nopriv_referral_validate_code', 'referral_ajax_validate_code');
add_action('wp_ajax_referral_validate_code', 'referral_ajax_validate_code');

// Shortcode for user registration form
function referral_form_shortcode() {
    // Process form submission and save referrals
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_registration'])) {
        // Sanitize and validate form data (you can add more validation as needed)
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        $referral_code = sanitize_text_field($_POST['referral_code']);

        // Check if the referral code is valid before creating the user
        $valid_referral_code = 'validcode123';

        if ($referral_code !== $valid_referral_code) {
            // Referral code is invalid, you can display an error message or handle it as per your requirement
            $error_message = 'Invalid referral code. Please enter a valid referral code.';
        } else {
            // Referral code is valid, create the user
            $user_id = wp_create_user($email, $password, $email);

            if (is_wp_error($user_id)) {
                // User creation failed, you can display an error message or handle it as per your requirement
                $error_message = 'User registration failed. Please try again later.';
            } else {
                // User created successfully, set first name and last name for the user
                wp_update_user(array('ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name));

                if (isset($_POST['referral_code'])) {
                    $referral_code = sanitize_text_field($_POST['referral_code']);
                    update_user_meta($user_id, 'referral_code', $referral_code);
                }
                // Display a success message or redirect the user to a success page
                $success_message = 'User registered successfully!';
            }
        }

        // Validate the referral code using Ajax
        // Prepare the referral code validation script
        wp_localize_script('referral-ajax', 'referral_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'referral_code' => $referral_code,
        ));
    }

    // Display the form
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Your Page Title</title>
        <!-- <link rel="stylesheet" href="assets/css/form_styles.css"> -->
    </head>
    <body>
    <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" required>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <label for="referral_code">Referral Code:</label>
        <input type="text" name="referral_code" id="referral_code" required>
        <span id="referral_status"></span> <!-- To show referral code validation status -->

        <input type="submit" name="submit_registration" value="Register">
    </form>
    </body>
    </html>
    
<style>
            /* Style for labels */
            label {
            display: block;
            margin-bottom: 5px;
        }

        /* Style for form input fields */
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            display: block;
            margin-bottom: 10px;
            padding: 5px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        /* Style for the form submit button */
        input[type="submit"] {
            display: block;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        /* Style for the referral code status message */
        #referral_status {
            color: red;
            font-size: 12px;
        }

</style>
    <script>
    jQuery(document).ready(function($) {
        $('#referral_code').on('blur', function() {
            var referralCode = $(this).val();
            $.ajax({
                url: referral_ajax.ajax_url,
                type: 'post',
                data: {
                    action: 'referral_validate_code',
                    referral_code: referralCode,
                },

                success: function(response) {
                    console.log(data);                    
                    if (response === 'valid') {
                        $('#referral_status').text('✓ Referral code is correct.').css('color', 'green');
                    } else {
                        $('#referral_status').text('✗ Invalid referral code.').css('color', 'red');
                    }
                }
            });
        });
    });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode(REFERRAL_SHORTCODE, 'referral_form_shortcode');


// Add referral code field to user profile page
function referral_user_profile_fields($user) {
    $referral_code = get_user_meta($user->ID, 'referral_code', true);

    ?>
    <h3>Referral Information</h3>
    <table class="form-table">
        <tr>
            <th><label for="referral_code">Referral Code</label></th>
            <td><input type="text" name="referral_code" id="referral_code" value="<?php echo esc_attr($referral_code); ?>" class="regular-text"></td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'referral_user_profile_fields');
add_action('edit_user_profile', 'referral_user_profile_fields');

// Save referral code field
function referral_user_profile_fields_save($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    if (isset($_POST['referral_code'])) {
        $referral_code = sanitize_text_field($_POST['referral_code']);
        update_user_meta($user_id, 'referral_code', $referral_code);
    }
}
add_action('personal_options_update', 'referral_user_profile_fields_save');
add_action('edit_user_profile_update', 'referral_user_profile_fields_save');



// Add referral code column to the custom post type list
function referral_custom_post_columns($columns) {
    $columns['referral_code'] = 'Referral Code';
    return $columns;
}
add_filter('manage_referral_posts_columns', 'referral_custom_post_columns');

// Populate referral code column with data
function referral_custom_post_column_data($column, $post_id) {
    if ($column === 'referral_code') {
        $referral_code = get_post_meta($post_id, 'referral_code', true);
        echo esc_html($referral_code);
    }
}
add_action('manage_referral_posts_custom_column', 'referral_custom_post_column_data', 10, 2);

// Add sub-menu under the custom post type
function add_custom_submenu() {
    add_submenu_page(
        'edit.php?post_type=referral',
        'Referral Settings',
        'Generate Referral',
        'manage_options',
        'referral-settings',
        'referral_settings_page'
    );
}
add_action('admin_menu', 'add_custom_submenu');

function referral_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Generate a unique referral code
    $referral_code = generate_referral_code();

    // Output the settings page content
    ?>
    <div class="wrap">
        <h1>Referral Settings</h1>
        <p>Your unique referral code: <?php echo $referral_code; ?></p>
    </div>
    <?php
}

function generate_referral_code() {
    // You can implement your logic to generate a unique referral code here.
    // For example, you can use a combination of user ID, timestamp, and a random string.
    $referral_code = 'REF_' . uniqid();

    return $referral_code;
}

// Define the User_Metadata_List_Table class
class User_Metadata_List_Table {
    // Method to display the user metadata table
    public function display_table() {

        // Add this code at the beginning of the display_table function

        // Inside the display_table() function:
        
        if(isset($_GET['action'])){
            $action = $_GET['action'];
        
        if ($action === 'edit_user' && isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            check_admin_referer('edit_user_' . $user_id);

            // Handle the edit action here. Redirect to the edit page or display a form to edit the user data.
            if (isset($_POST['submit_edit'])) {
                // Handle form submission to update user metadata
                // Example: Update the 'referral_code' and 'referral_user_name' metadata
                update_user_meta($user_id, 'referral_code', $_POST['referral_code']);
                update_user_meta($user_id, 'referral_user_name', $_POST['referral_user_name']);

                // Redirect back to the table after updating
                wp_redirect(admin_url('edit.php?post_type=referral&page=user_metadata_table'));
                exit;
            } else {
                // Display the edit form
                $referral_code = get_user_meta($user_id, 'referral_code', true);
                $referral_user_name = get_user_meta($user_id, 'referral_user_name', true);

                echo '<h2>Edit User Metadata</h2>';
                echo '<form method="post">';
                echo 'Referral Code: <input type="text" name="referral_code" value="' . esc_attr($referral_code) . '"><br>';
                echo 'Referral User Name: <input type="text" name="referral_user_name" value="' . esc_attr($referral_user_name) . '"><br>';
                echo '<input type="submit" name="submit_edit" value="Update">';
                wp_nonce_field('edit_user_' . $user_id);
                echo '</form>';
            }
        }

        if ($action === 'delete_user' && isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            check_admin_referer('delete_user_' . $user_id);

            // Handle the delete action here. Delete the user record from the database.
            // Example: Delete the user and their metadata
            wp_delete_user($user_id);

            // Redirect back to the table after deleting
            wp_redirect(admin_url('edit.php?post_type=referral&page=user_metadata_table'));
            exit;
        }
    }


        $users = get_users(); // Fetch all users using WordPress function get_users()

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">User Referral History Table</h1>';
        echo '<form method="post" action="?action=bulk_delete">';
        echo '<table class="wp-list-table widefat fixed striped">';
        // echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th class="manage-column column-id">User ID</th><th class="manage-column column-login">User Login</th><th class="manage-column column-email">User Email</th><th class="manage-column column-referral">Referral Code</th><th class="manage-column column-referral-user">Referral User name</th><th class="manage-column column-join-commission">Join Commission</th><th class="manage-column column-price">Price (INR)</th><th></th><th></th></tr></thead>';
        echo '<tbody>';

        foreach ($users as $user) {
            $user_id = $user->ID;
            $user_login = $user->user_login;
            $user_email = $user->user_email;
            $referral_code = get_user_meta($user_id, 'referral_code', true); // Assuming 'referral_code' is the meta key for the referral code.
            $referral_user_name = get_user_meta($user_id, 'referral_user_name', true); // Assuming 'referral_user_name' is the meta key for the referral user name.
            $join_commission = get_user_meta($user_id, 'join_commission', true); // Assuming 'join_commission' is the meta key for the join commission.
            $price_inr = floatval(get_user_meta($user_id, 'price_inr', true)); // Convert the value to float.

            echo '<tr>';
            echo '<td class="column-id">' . $user_id . '</td>';
            echo '<td class="column-login">' . $user_login . '</td>';
            echo '<td class="column-email">' . $user_email . '</td>';
            echo '<td class="column-referral">' . $referral_code . '</td>';
            echo '<td class="column-referral-user">' . $referral_user_name . '</td>';
            echo '<td class="column-join-commission">' . $join_commission . '</td>';
            echo '<td class="column-price">' . number_format($price_inr, 2) . ' INR</td>';
            
            // Action buttons
            echo '<td class="column-actions">';
            echo '<a href="' . wp_nonce_url('?action=edit_user&user_id=' . $user_id, 'edit_user_' . $user_id) . '">Edit</a>';
            echo ' | ';
            echo '<a href="' . wp_nonce_url('?action=delete_user&user_id=' . $user_id, 'delete_user_' . $user_id) . '">Delete</a>';
            echo '</td>';
            
            // Add checkbox for bulk delete
            echo '<td class="check-column">';
            echo '<input type="checkbox" name="users[]" value="' . $user_id . '">';
            echo '</td>';
            
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '<button type="submit" class="button">Delete Selected</button>';
        echo '</form>';
        echo '</div>';
    }
    
}

// Function to add submenu page for the "Referral" custom post type
function referral_add_submenu_page() {
    add_submenu_page(
        'edit.php?post_type=referral', // Parent slug: 'edit.php?post_type=referral' represents the "Referral" custom post type menu
        'User Metadata Table', // Page title
        'User Referral History', // Menu title
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
}

