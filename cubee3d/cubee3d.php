<?php

/**
 * Plugin Name: Cubee3D
 * Plugin URI: https://www.cubee3d.com/
 * Description: Let your customers upload a 3D file for On-Demand printing service
 * Version: 1.3
 * Author: Lidor Baum
 * Author URI: https://www.linkedin.com/in/lidorbaum/
 **/ 

add_action('wp_ajax_data', 'create_product_add_cart');
add_action('wp_ajax_nopriv_data', 'create_product_add_cart');
function create_product_add_cart()
{
  if (isset($_REQUEST)) {
    $fileName = $_REQUEST['fileName'];
    $printTime = $_REQUEST['printTime'];
    $material = $_REQUEST['material'];
    $layerHeight = $_REQUEST['layerHeight'];
    $infill = $_REQUEST['infill'];
    $isVase = $_REQUEST['isVase'];
    $isSupports = $_REQUEST['isSupports'];
    $color = $_REQUEST['color'];
    $fileId = $_REQUEST['fileId'];
    $copies = $_REQUEST['copies'];
    $price = $_REQUEST['price'];
    $weight = $_REQUEST['weight'];
    $dimensions = $_REQUEST['dimensions'];
    $downloadURL = $_REQUEST['downloadURL'];
    
    $post_id = wp_insert_post(
      array(
        // 'import_id' => 89999,
        'post_title' => $fileName,
        'post_type' => 'product',
        'post_status' => 'publish'
      )  
    );  
    wp_set_object_terms($post_id, 'simple', 'product_type'); // set product is simple/variable/grouped
    update_post_meta($post_id, '_printTime', $printTime);
    update_post_meta($post_id, '_layerHeight', $layerHeight);
    update_post_meta($post_id, '_material', $material);
    update_post_meta($post_id, '_infill', $infill);
    update_post_meta($post_id, '_isVase', $isVase);
    update_post_meta($post_id, '_isSupports', $isSupports);
    update_post_meta($post_id, '_color', $color);
    update_post_meta($post_id, '_fileId', $fileId);
    update_post_meta($post_id, '_copies', $copies);
    update_post_meta($post_id, '_price', $price);
    update_post_meta($post_id, '_dimensions', $dimensions);
    update_post_meta($post_id, '_downloadURL', $downloadURL);
    update_post_meta($post_id, '_weight', $weight);
    update_post_meta($post_id, '_cubeeFile', 'cubeeFile');
    update_post_meta($post_id, '_stock_status', 'instock');
    update_post_meta($post_id, 'total_sales', '0');
    update_post_meta($post_id, '_downloadable', 'no');
    update_post_meta($post_id, '_virtual', 'no');
    update_post_meta($post_id, '_regular_price', $price);
    update_post_meta($post_id, '_sale_price', '');
    update_post_meta($post_id, '_purchase_note', '');
    update_post_meta($post_id, '_featured', 'no');
    update_post_meta($post_id, '_length', '');
    update_post_meta($post_id, '_width', '');
    update_post_meta($post_id, '_height', '');
    update_post_meta($post_id, '_sku', 'CubeeProduct');
    update_post_meta($post_id, '_product_attributes', array());
    update_post_meta($post_id, '_sale_price_dates_from', '');
    update_post_meta($post_id, '_sale_price_dates_to', '');
    update_post_meta($post_id, '_price', $price);
    update_post_meta($post_id, '_sold_individually', '');
    update_post_meta($post_id, '_manage_stock', 'no'); // activate stock management
    update_post_meta($post_id, '_backorders', 'no');
    $terms = array('exclude-from-catalog', 'exclude-from-search');
    wp_set_object_terms($post_id, $terms, 'product_visibility');

    WC()->cart->add_to_cart($post_id, $copies);
  }  
}  


add_action('woocommerce_checkout_create_order_line_item', 'save_order_item_product', 10, 4);
function save_order_item_product($item, $cart_item_key, $values, $order)
{
  $key = __('Layer Height', 'woocommerce');
  $value = $item->get_product()->get_meta('_layerHeight');
  $item->update_meta_data($key, $value);

  $key = __('Print Time', 'woocommerce');
  $value = $item->get_product()->get_meta('_printTime');
  $item->update_meta_data($key, $value);

  $key = __('Material', 'woocommerce');
  $value = $item->get_product()->get_meta('_material');
  $item->update_meta_data($key, $value);

  $key = __('Infill', 'woocommerce');
  $value = $item->get_product()->get_meta('_infill');
  $item->update_meta_data($key, $value);

  $key = __('Color', 'woocommerce');
  $value = $item->get_product()->get_meta('_color');
  $item->update_meta_data($key, $value);

  $key = __('File ID', 'woocommerce');
  $value = $item->get_product()->get_meta('_fileId');
  $item->update_meta_data($key, $value);

  $key = __('Dimesions(mm)', 'woocommerce');
  $value = $item->get_product()->get_meta('_dimensions');
  $item->update_meta_data($key, $value);

  $key = __('Weight (g)', 'woocommerce');
  $value = $item->get_product()->get_meta('_weight');
  $item->update_meta_data($key, $value);

  $key = __('Download URL', 'woocommerce');
  $value = $item->get_product()->get_meta('_downloadURL');
  $item->update_meta_data($key, $value);

  $key = __('Supports?', 'woocommerce');
  $value = $item->get_product()->get_meta('_isSupports');
  $item->update_meta_data($key, $value);

  $key = __('Vase Mode?', 'woocommerce');
  $value = $item->get_product()->get_meta('_isVase');
  $item->update_meta_data($key, $value);
}


function delete_or_keep_products($order_id)
{
  if (!$order_id)
    return;
  if (!get_post_meta($order_id, '_thankyou_action_done', true)) {
    $order = wc_get_order($order_id);
    foreach ($order->get_items() as $item_id => $item) {
      $productId = $item->get_product_id();
      $isCubee = $item->get_product()->get_meta('_cubeeFile');
      if ($isCubee == 'cubeeFile') {
        wp_delete_post($productId);
      }
    }
  }
}
add_action('woocommerce_thankyou', 'delete_or_keep_products', 10, 1);



// * Add the settings tab to the menu
function dbi_add_settings_page()
{
  add_options_page('Cubee 3D - OnDemand', 'Cubee 3D - OnDemand', 'manage_options', 'dbi-example-plugin', 'dbi_render_plugin_settings_page');
}
add_action('admin_menu', 'dbi_add_settings_page');


//* Render the settings page
function dbi_render_plugin_settings_page()
{
?>
  <h2>Cubee 3D</h2>
  <form action="options.php" method="post">
    <?php
    settings_fields('dbi_example_plugin_options');
    do_settings_sections('dbi_example_plugin'); ?>
    <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>" />
  </form>
<?php
}


function dbi_register_settings()
{
  register_setting('dbi_example_plugin_options', 'dbi_example_plugin_options');
  add_settings_section('api_settings', 'On Demand Settings', 'dbi_plugin_section_text', 'dbi_example_plugin');

  add_settings_field('dbi_plugin_setting_api_key', 'Cubee 3D Shop API Key', 'dbi_plugin_setting_api_key', 'dbi_example_plugin', 'api_settings');
}
add_action('admin_init', 'dbi_register_settings');

function dbi_example_plugin_options_validate($input)
{
  $newinput['api_key'] = trim($input['api_key']);
  if (!preg_match('/^[a-z0-9]{32}$/i', $newinput['api_key'])) {
    $newinput['api_key'] = '';
  }

  return $newinput;
}

function dbi_plugin_section_text()
{
  echo '<p>Here you can set all the options for using Cubee 3D OnDemand Printing Services</p>';
}

function dbi_plugin_setting_api_key()
{
  $options = get_option('dbi_example_plugin_options');
  echo "<input id='dbi_plugin_setting_api_key' name='dbi_example_plugin_options[api_key]' type='text' value='" . esc_attr($options['api_key']) . "' />";
}


function listen_to_postMessage()
{
  ob_start();
  $apikey = get_option('dbi_example_plugin_options');
  $currencyCode = get_woocommerce_currency();
  $cartURL = wc_get_cart_url();
?>
  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
  <script>
    var cubeeVars = <?php echo json_encode($apikey); ?>;
    var cartURL = <?php echo json_encode($cartURL); ?>;
    var currencyCode = <?php echo json_encode($currencyCode); ?>;
    window.addEventListener('message', event => {
      event.stopPropagation()
      // * If this is the handshake, then send the apikey
      // if (!event.origin.startsWith('https://ondemand.cubee3d.com')) {
      // if (!event.origin.startsWith('https://ondemand.staging.cubee3d.com')) {
      if (event.origin.startsWith('http://localhost')) {
        if (event.data.handshake) {
          document.querySelector('iframe').contentWindow.postMessage({
            handshake: {
              apiKey: cubeeVars.api_key,
              currencyCode
            }
          }, '*')
          // * In production, use real ondemand url
          // }, 'https://ondemand.staging.cubee3d.com')
          // }, 'https://ondemand.cubee3d.com')
          // * !

          // * if this is the submit button, initiate the add to cart
        } else if (event.data.onAddToCart) {
          document.querySelector('iframe').contentWindow.postMessage({
            isLoading: true
          }, '*')
          jQuery(document).ready(function($) {})
          const models = event.data.onAddToCart.models
          const numOfProducts = models.length
          const checkAndRedirect = (currentIDX) => {
            if (currentIDX + 1 === numOfProducts) window.location.href = cartURL
          }
          models.forEach((model, idx) => {
            setTimeout(() => {
              res = jQuery.ajax({
                url: "../../wpsite/wp-admin/admin-ajax.php",
                data: {
                  'action': 'data',
                  'fileName': model.fileName,
                  'material': model.material,
                  'color': model.color,
                  'layerHeight': model.layerHeight,
                  'infill': model.infill,
                  'isVase': model.isVase,
                  'isSupports': model.isSupports,
                  'printTime': model.printTime,
                  'fileId': model.fileId,
                  'copies': model.copies,
                  'price': model.price,
                  'weight': model.weight,
                  'dimensions': `${model.dimensions.width}x${model.dimensions.length}x${model.dimensions.height}`,
                  'downloadURL': model.downloadURL,
                },
                success: function() {
                  checkAndRedirect(idx)
                }
              })
            }, 2000 * idx)
          });
        }
      } else {
        // ! This event is not from us, unauthorized!
      }
    });
  </script>
<?php
}
add_action('template_redirect', 'listen_to_postMessage');
