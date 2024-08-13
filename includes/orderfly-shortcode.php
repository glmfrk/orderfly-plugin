<?php

function orderfly_create_shortcode() {
    ob_start(); ?>
    
    <section class="order__page_sec">
        <div class="container text-center">
            <div class="row">
                <div class="col-12 mx-auto">
                    <div class="order__box">
                        <a href="#" class="logo">
                            <img src="<?php echo plugins_url('../assets/images/pran.png', __FILE__); ?>" alt="pran.png" />
                        </a>
                        <h2 class="heading"><?php esc_html_e( 'আপনার অর্ডারকৃত পণ্য নির্ধারণ করুন', 'orderfly' ); ?></h2>
                        
                        <?php 
                        $args = array(
                            'post_type'        => 'order_fly',
                            'posts_per_page'   => 5,
                        ); 

                        $the_query = new WP_Query( $args );
                        
                        // The Loop
                        if ( $the_query->have_posts() ) {
                            echo '<div class="product__table">';
                            while ( $the_query->have_posts() ) {
                                $the_query->the_post();
                                $product_id = get_the_ID();
                                $product_name = get_the_title();
                                $product_price = get_post_meta($product_id, '_price', true);
                                $product_img = get_the_post_thumbnail_url($product_id, 'full');
                                ?>
                                <div class="product__table_row" data-id="table_row_<?php echo esc_attr($product_id); ?>">
                                    <div class="single_item item_left">
                                        <figure class="product_demo">
                                        <img src="<?php echo esc_url($product_img); ?>" alt="<?php echo esc_attr($product_name); ?>" />
                                        </figure>
                                        <div class="pd_unit">
                                            <input type="checkbox" id="pd_item_<?php echo esc_attr($product_id); ?>" name="pd_item_<?php echo esc_attr($product_id); ?>" />
                                            <label for="pd_item_<?php echo esc_attr($product_id); ?>"> <?php echo esc_html($product_name); ?> </label>
                                        </div>
                                    </div>
                                    <div class="single_item item_right">
                                        <div class="pd_pricing">
                                            Tk <span><?php echo esc_html($product_price); ?></span>
                                        </div>
                                        <div class="quantity">
                                            <input type="button" value="-" class="minus" />
                                            <input type="number" value="0" min="0" class="input-box" />
                                            <input type="button" value="+" class="plus" />
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            echo '</div>';
                            /* Restore original Post Data */
                            wp_reset_postdata();
                        } else {
                            // No posts found
                            echo '<p>No products found.</p>';
                        }?>
                        
                        <div class="shifting_table_wrapper">
                            <h3 class="heading_h3 text-left"><?php esc_html_e( 'শিপিং মেথড', 'orderfly' ); ?></h3>
                            <div class="shifting_table">
                                <?php 
                                $shifting_methods = [
                                    ['id' => '01', 'label' => 'ঢাকা সিটির ভিতরে', 'cost' => '70.00'],
                                    ['id' => '02', 'label' => 'চট্টগ্রাম সিটির ভিতরে', 'cost' => '70.00'],
                                    ['id' => '03', 'label' => 'ঢাকা ও চট্টগ্রাম সিটির বাহিরে', 'cost' => '130.00']
                                ];
                                foreach ($shifting_methods as $method) { ?>
                                    <div class="shifting_table_row" data-id="shifting_row_<?php echo esc_attr($method['id']); ?>">
                                        <div class="pd_unit">
                                            <input type="radio" id="radio_item_<?php echo esc_attr($method['id']); ?>" name="shiftingCost" />
                                            <label for="radio_item_<?php echo esc_attr($method['id']); ?>"> <?php echo esc_html($method['label']); ?> </label>
                                        </div>
                                        <strong>
                                            Tk <span class="shiftingCost"><?php echo esc_html($method['cost']); ?></span>
                                        </strong>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="product_price_details">
                            <div class="pd_grandTotal_price">
                                <p><?php esc_html_e( 'সাব টোটাল', 'orderfly' ); ?><strong class="net_price"><?php esc_html_e( 'Tk', 'orderfly' ); ?> <span>00.00</span></strong></p>
                                <p><?php esc_html_e( 'ডেলিভারি চার্জ', 'orderfly' ); ?> <strong><?php esc_html_e( 'Tk', 'orderfly' ); ?> <span class="delivery_charge">00.00</span></strong></p>
                                <p><?php esc_html_e( 'সর্বমোট', 'orderfly' ); ?><strong class="total_price"><?php esc_html_e( 'Tk', 'orderfly' ); ?> <span>00.00</span></strong></p>
                            </div>
                            <div class="order_note_box">
                                <label for="orderNote"><?php esc_html_e( 'Order note', 'orderfly' ); ?></label>
                                <input type="text" name="orderNote" id="orderNote" placeholder="Order note" />
                            </div>
                        </div>

                        <?php esc_html_e( '', 'orderfly' ); ?>

                        <div class="delivary_order_form">
                            <h2> <?php esc_html_e( 'ক্যাশ অন ডেলিভারিতে', 'orderfly' ); ?> <br>  <?php esc_html_e( 'অর্ডার করতে আপনার তথ্য দিন', 'orderfly' ); ?></h2>

                            <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="userInfoForm" class="userInfoForm">
                                <input type="hidden" name="action" value="save_order_data">
                                <input type="hidden" name="orderData" id="orderData">
                                <div class="index_box">
                                    <label for="fullName"> <?php esc_html_e( 'আপনার নাম', 'orderfly' ); ?> <span>*</span></label>
                                    <div class="input_item">
                                        <span><i class="fa-solid fa-user"></i></span>
                                        <input type="text" name="userName" id="userName" placeholder="API Solutions" required />
                                    </div>
                                </div>
                                <div class="index_box">
                                    <label for="phoneNumber"><?php esc_html_e( 'আপনার নাম্বার', 'orderfly' ); ?> <span>*</span></label>
                                    <div class="input_item">
                                        <span><i class="fa-solid fa-phone"></i></span>
                                        <input type="number" name="userPhone" id="userPhone" placeholder="02-55035911" required />
                                    </div>
                                </div>
                                <div class="index_box">
                                    <label for="userEmail"><?php esc_html_e( 'আপনার ইমেল আইডি', 'orderfly' ); ?> <span>*</span></label>
                                    <div class="input_item">
                                        <span><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" name="userEmail" id="userEmail" placeholder="hello@apisolutionsltd.com" required />
                                    </div>
                                </div>
                                <div class="index_box">
                                    <label for="address"><?php esc_html_e( 'এড্রেস', 'orderfly' ); ?> <span>*</span></label>
                                    <div class="input_item">
                                        <span><i class="fa-solid fa-location-dot"></i></span>
                                        <input type="text" name="userAddress" id="userAddress" placeholder="Block B, House -4 Road 23/A, Dhaka 1213" required />
                                    </div>
                                </div>
                                <input type="submit" value="আপনার অর্ডার কনফার্ম করতে ক্লিক করুন" class="btn submit_btn" />
                                <p class="mgs_info"><?php esc_html_e( 'উপরের বাটনে ক্লিক করলে আপনার অর্ডারটি সাথে সাথে কনফার্ম হয়ে যাবে !', 'orderfly' ); ?></p>
                            </form>
                        </div>
                        <div id="invoice-confirmation" class="invoice-confirmation" style="display: none;">
                            <p style="color: green;">Your order has been confirmed! You can download or print your invoice below:</p>
                            <div class="invoice-actions">
                                <a id="download-invoice" href="#" download="invoice.pdf" class="btn btn-primary">Download PDF</a>
                                <button id="print-invoice" class="btn btn-secondary">Print Invoice</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php
    return ob_get_clean();
}

add_shortcode('view-order-form-shortcode', 'orderfly_create_shortcode');
?>
