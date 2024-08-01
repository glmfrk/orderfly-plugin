<?php

// Register item view post type
function register_orderfly_post_type() {
    $labels = array(
      'name'                  => __( 'Products', 'orderfly' ),
      'singular_name'         => __( 'Orderfly', 'orderfly' ),
      'menu_name'             => __( 'Orderfly Products', 'orderfly' ),
      'name_admin_bar'        => __( 'Orderflies', 'orderfly' ),
      'add_new'               => __( 'Add Product', 'orderfly' ),
      'add_new_item'          => __( 'Add New Orderfly Product', 'orderfly' ),
      'new_item'              => __( 'New Orderfly Product', 'orderfly' ),
      'edit_item'             => __( 'Edit Orderfly Product', 'orderfly' ),
      'view_item'             => __( 'View Orderfly Product', 'orderfly' ),
      'all_items'             => __( 'All Products', 'orderfly' ),
    );
  
    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array('slug' => 'item'),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-products',
      'supports'           => array('title', 'editor', 'thumbnail'),
    );
  
    register_post_type('order_fly', $args);
  }
  add_action('init', 'register_orderfly_post_type');
  

// Add meta box for product rate
function add_product_list_orderfly_meta_box() {
    add_meta_box(
      'product_list_orderfly_meta_box',
      'Set Your Product Rate',
      'product_list_orderfly_meta_box_callback',
      'order_fly',
      'normal',
      'high'
    );
  }
  add_action('add_meta_boxes', 'add_product_list_orderfly_meta_box');
  
  // Meta box callback
  function product_list_orderfly_meta_box_callback($post) {
    wp_nonce_field('save_product_list_orderfly_meta', 'product_list_orderfly_meta_nonce');
    $price = get_post_meta($post->ID, '_price', true);
    ?>
    <label for="product_list_orderfly_price">Add Price: </label>
    <input type="number" id="product_list_orderfly_price" name="product_list_orderfly_price" value="<?php echo esc_attr($price); ?>" />
    <?php
  }
  

// Save meta fields
function save_product_list_orderfly_meta($post_id) {
    if (!isset($_POST['product_list_orderfly_meta_nonce']) || !wp_verify_nonce($_POST['product_list_orderfly_meta_nonce'], 'save_product_list_orderfly_meta')) {
      return;
    }
  
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }
  
    if (isset($_POST['post_type']) && 'order_fly' === $_POST['post_type'] && !current_user_can('edit_post', $post_id)) {
      return;
    }
  
    if (isset($_POST['product_list_orderfly_price'])) {
      $price = sanitize_text_field($_POST['product_list_orderfly_price']);
      update_post_meta($post_id, '_price', $price);
    }
  }
  add_action('save_post', 'save_product_list_orderfly_meta');



// Display Order List in Admin Dashboard
add_action('admin_menu', 'orderfly_add_submenu');

function orderfly_add_submenu() {
    add_submenu_page(
        'edit.php?post_type=order_fly', // Parent slug - This is the slug for your custom post type
        'All Orders',                   // Page title
        'All Orders',                   // Menu title
        'manage_options',               // Capability required
        'orderfly_all_orders',          // Menu slug
        'orderfly_display_orders'       // Callback function
    );
}

function orderfly_display_orders() {
    global $wpdb;

    if (isset($_GET['view'])) {
        $order_id = intval($_GET['view']);
        $order_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}customer_information WHERE id = %d", $order_id));
        $order_items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}customer_orders WHERE order_id = %d", $order_id));
        $order_confirmation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}order_confirmation WHERE order_id = %d", $order_id));

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Order Details</h1>
            <h2>Customer Information</h2>
            <table class="wp-list-table widefat fixed striped">
                <tr><th>ID</th><td><?php echo esc_html($order_info->id); ?></td></tr>
                <tr><th>Name</th><td><?php echo esc_html($order_info->userName); ?></td></tr>
                <tr><th>Phone</th><td><?php echo esc_html($order_info->userPhone); ?></td></tr>
                <tr><th>Email</th><td><?php echo esc_html($order_info->userEmail); ?></td></tr>
                <tr><th>Address</th><td><?php echo esc_html($order_info->userAddress); ?></td></tr>
            </table>
            <h2>Order Items</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo esc_html($item->product_id); ?></td>
                            <td><?php echo esc_html($item->quantity); ?></td>
                            <td><?php echo esc_html($item->price); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h2>Order Confirmation</h2>
            <table class="wp-list-table widefat fixed striped">
                <tr><th>Sub Total</th><td><?php echo esc_html($order_confirmation->subTotal); ?></td></tr>
                <tr><th>Shipping Charge</th><td><?php echo esc_html($order_confirmation->shippingCharge); ?></td></tr>
                <tr><th>Grand Total</th><td><?php echo esc_html($order_confirmation->grandTotal); ?></td></tr>
                <tr><th>Order Note</th><td><?php echo esc_html($order_confirmation->orderNote); ?></td></tr>
            </table>
        </div>
        <?php
    } else {
        // Display list of orders
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}customer_information");

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">All Orders</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $order): ?>
                        <tr>
                            <td><?php echo esc_html($order->id); ?></td>
                            <td><?php echo esc_html($order->userName); ?></td>
                            <td><?php echo esc_html($order->userPhone); ?></td>
                            <td><?php echo esc_html($order->userEmail); ?></td>
                            <td><?php echo esc_html($order->userAddress); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=orderfly_all_orders&view=' . $order->id); ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
