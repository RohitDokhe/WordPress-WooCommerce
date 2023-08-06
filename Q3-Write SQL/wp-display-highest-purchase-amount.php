<?php
/*
Plugin Name: My Custom Plugin
Description: Write a query to display highest purchase amount of customer name using WordPress
function with SQL Query.
Version: 1.0
Author: Your Name
*/

// Register the custom function
add_shortcode('display_highest_purchase', 'display_highest_purchase');

// The custom function
function display_highest_purchase() {
    global $wpdb;

    // Replace 'purchases' with the actual name of your custom table
    $table_name = $wpdb->prefix . 'purchases';

    $query = "SELECT customer_name, MAX(purchase_amount) AS highest_purchase_amount
              FROM $table_name
              GROUP BY customer_name";

    $results = $wpdb->get_results($query);

    if (!empty($results)) {
        $output = '<ul>';
        foreach ($results as $result) {
            $customer_name = $result->customer_name;
            $highest_purchase_amount = $result->highest_purchase_amount;

            // Display the customer name and highest purchase amount
            $output .= "<li>Customer: $customer_name | Highest Purchase Amount: $highest_purchase_amount</li>";
        }
        $output .= '</ul>';
    } else {
        $output = 'No data found.';
    }

    return $output;
}
