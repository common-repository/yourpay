<?php

if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if(!defined("YOURPAY__PLUGIN_DIR"))
    define( 'YOURPAY__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if(!defined("YOURPAY__PLUGIN_URL"))
    define( 'YOURPAY__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
require_once( YOURPAY__PLUGIN_DIR . 'sdk.php' );

function yourpay_syncronize_products() {
    $sdk = new yourpay_sdk();
    $all_ids = get_posts( array(
        'post_type' => 'product',
        'numberposts' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
    ) );
    foreach ( $all_ids as $id ) {
        $content_post = get_post($id);
        $content = $content_post->post_content;

        $product = wc_get_product( $id );

        $data["product_id"] = $id;

        $product_data = [];
        $product_data["name"]       = $product->get_name();
        $product_data["description"]= str_replace(["\n","\r","\t","&nbsp;"]," ", wp_strip_all_tags($content));
        $product_data["type"]       = $product->get_type();
        $product_data["created"]    = $product->get_date_created();
        $product_data["modified"]   = $product->get_date_modified();
        $product_data["status"]     = $product->get_status();
        $product_data["featured"]   = $product->get_featured();
        $product_data["sku"]        = $product->get_sku();
        $product_data["virtual"]    = $product->get_virtual();

        $product_data["price"]["price"]     = $product->get_price();
        $product_data["price"]["regular"]   = $product->get_regular_price();
        $product_data["price"]["sale"]      = $product->get_sale_price();
        $product_data["price"]["sale_start"]= $product->get_date_on_sale_from();
        $product_data["price"]["sale_end"]  = $product->get_date_on_sale_to();
        $product_data["price"]["sale_end"]  = $product->get_date_on_sale_to();


        $product_data["stock"]["managed"]   = $product->get_manage_stock();
        $product_data["stock"]["qty"]       = $product->get_stock_quantity();
        $product_data["stock"]["status"]    = $product->get_stock_status();
        $product_data["stock"]["backorder"] = $product->get_backorders();

        $data["product_data"] = json_encode($product_data);

        $post_thumbnail_id = get_post_thumbnail_id( $id );
        $product_images = [];
        $product_images["id"]       = $post_thumbnail_id;
        $product_images["url"] = wp_get_attachment_image_url( $post_thumbnail_id, "full" );

        $attachment_ids = $product->get_gallery_image_ids();
        $gallery_images = [];
        foreach( $attachment_ids as $attachment_id )
        {
            $gallery_images[] = [
                "id" => $attachment_id,
                "url" => wp_get_attachment_url( $attachment_id )
            ];
        }
        $product_images["gallery"] = $gallery_images;

        $data["product_images"] = json_encode($product_images);

        $sdk->v43productnoresponse("product_sync",$data);
    }
}