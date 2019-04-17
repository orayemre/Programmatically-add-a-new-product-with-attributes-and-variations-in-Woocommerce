<?php
/* By Remodeus */
add_action('save_post', 'auto_add_product_attributes', 50, 3);
function auto_add_product_attributes($post_id, $post, $update){
    if ($post->post_type != 'product') return; // Only products
    if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;
    if( $update )
        return $post_id;
    if ( ! current_user_can( 'edit_product', $post_id))
        return $post_id;
    global $wpdb;

$attributes = array(
  array("name"=>"Size","options"=>array("14 x 14","16 x 16","18 x 18"),"position"=>1,"visible"=>1,"variation"=>1),
  array("name"=>"Materials","options"=>array("Cotton Twill","Spun Polyester"),"position"=>2,"visible"=>1,"variation"=>1)
);
if($attributes){
  $productAttributes=array();
  foreach($attributes as $attribute){
    $objProduct = new WC_Product_Variable($post_id);
    $attr = wc_sanitize_taxonomy_name(stripslashes($attribute["name"])); 
    $attr = 'pa_'.$attr;
    if($attribute["options"]){
      foreach($attribute["options"] as $option){
        wp_set_object_terms($post_id,$option,$attr,true);
      }
    }
    $productAttributes[sanitize_title($attr)] = array(
      'name' => sanitize_title($attr),
      'value' => $attribute["options"],
      'position' => $attribute["position"],
      'is_visible' => $attribute["visible"],
      'is_variation' => $attribute["variation"],
      'is_taxonomy' => '1'
    );
$objProduct->save();
  }
  update_post_meta($post_id,'_product_attributes',$productAttributes);

// Create random number for SKU
$num1 = mt_rand(100000000, 999999999);
$num2 = mt_rand(100000000, 999999999);
$num3 = mt_rand(100000000, 999999999);
$num4 = mt_rand(100000000, 999999999);

$variations = array(
  array("regular_price"=>"10","price"=>"","sku"=>$num1,"attributes"=>array(array("name"=>"Size","option"=>"14 x 14"),array("name"=>"Materials","option"=>"Cotton Twill")),"manage_stock"=>"1","stock_quantity"=>"999"),
  array("regular_price"=>"12","price"=>"","sku"=>$num2,"attributes"=>array(array("name"=>"Size","option"=>"14 x 14"),array("name"=>"Materials","option"=>"Spun Polyester")),"manage_stock"=>"1","stock_quantity"=>"999"),
  array("regular_price"=>"14","price"=>"","sku"=>$num3,"attributes"=>array(array("name"=>"Size","option"=>"18 x 18"),array("name"=>"Materials","option"=>"Cotton Twill")),"manage_stock"=>"1","stock_quantity"=>"999"),
  array("regular_price"=>"16","price"=>"","sku"=>$num4,"attributes"=>array(array("name"=>"Size","option"=>"18 x 18"),array("name"=>"Materials","option"=>"Spun Polyester")),"manage_stock"=>"1","stock_quantity"=>"999")
);
if($variations){
    foreach($variations as $variation){
      $objVariation = new WC_Product_Variation();
      $objVariation->set_price($variation["price"]);
      $objVariation->set_regular_price($variation["regular_price"]);
      $objVariation->set_parent_id($post_id);
      if(isset($variation["sku"]) && $variation["sku"]){
        $objVariation->set_sku($variation["sku"]);
      }
      $objVariation->set_manage_stock($variation["manage_stock"]); // 1 or 0
      $objVariation->set_stock_quantity($variation["stock_quantity"]);
      $objVariation->set_stock_status('instock');
      $var_attributes = array();
      foreach($variation["attributes"] as $vattribute){
        $taxonomy = "pa_".wc_sanitize_taxonomy_name(stripslashes($vattribute["name"]));
        $attr_val_slug =  wc_sanitize_taxonomy_name(stripslashes($vattribute["option"]));
        $var_attributes[$taxonomy]=$attr_val_slug;
      }
      $objVariation->set_attributes($var_attributes);
      $objVariation->save();
    }
}
}
}
