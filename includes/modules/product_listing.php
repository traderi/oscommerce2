<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  $listing_split = new splitPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id');
?>

        <div class="contentText">

<?php
  if ( ($listing_split->number_of_rows > 0) && ( (PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3') ) ) {
?>

          <div class="row-fluid pagination pagination-right">
            <div style="float:left"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
            <?php echo $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, osc_get_all_get_params(array('page'))); ?>

          </div>

<?php
  }

  $prod_list_contents = '          <table class="table table-striped">' . "\n" .
                        '            <thead><tr>' . "\n";

  for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
    $lc_align = '';

    switch ($column_list[$col]) {
      case 'PRODUCT_LIST_MODEL':
        $lc_text = TABLE_HEADING_MODEL;
        $lc_align = '';
        break;
      case 'PRODUCT_LIST_NAME':
        $lc_text = TABLE_HEADING_PRODUCTS;
        $lc_align = '';
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $lc_text = TABLE_HEADING_MANUFACTURER;
        $lc_align = '';
        break;
      case 'PRODUCT_LIST_PRICE':
        $lc_text = TABLE_HEADING_PRICE;
        $lc_align = 'right';
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $lc_text = TABLE_HEADING_QUANTITY;
        $lc_align = 'right';
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $lc_text = TABLE_HEADING_WEIGHT;
        $lc_align = 'right';
        break;
      case 'PRODUCT_LIST_IMAGE':
        $lc_text = TABLE_HEADING_IMAGE;
        $lc_align = 'center';
        break;
      case 'PRODUCT_LIST_BUY_NOW':
        $lc_text = TABLE_HEADING_BUY_NOW;
        $lc_align = 'center';
        break;
    }

    if ( ($column_list[$col] != 'PRODUCT_LIST_BUY_NOW') && ($column_list[$col] != 'PRODUCT_LIST_IMAGE') ) {
      $lc_text = osc_create_sort_heading($_GET['sort'], $col+1, $lc_text);
    }

    $prod_list_contents .= '              <td' . (osc_not_null($lc_align) ? ' style="text-' . $lc_align . '"' : '') . '>' . $lc_text . '</td>' . "\n";
  }

  $prod_list_contents .= '            </tr></thead>' . "\n";

  if ($listing_split->number_of_rows > 0) {
    $rows = 0;
    $listing_query = osc_db_query($listing_split->sql_query);

    $prod_list_contents .= '            <tbody>' . "\n";

    while ($listing = osc_db_fetch_array($listing_query)) {
      $rows++;

      $prod_list_contents .= '            <tr>';

      for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
        switch ($column_list[$col]) {
          case 'PRODUCT_LIST_MODEL':
            $prod_list_contents .= '<td>' . $listing['products_model'] . '</td>';
            break;
          case 'PRODUCT_LIST_NAME':
            if (isset($_GET['manufacturers_id']) && osc_not_null($_GET['manufacturers_id'])) {
              $prod_list_contents .= '<td><a href="' . osc_href_link('products', 'manufacturers_id=' . $_GET['manufacturers_id'] . '&id=' . $listing['products_id']) . '">' . $listing['products_name'] . '</a></td>';
            } else {
              $prod_list_contents .= '<td><a href="' . osc_href_link('products', ($cPath ? 'cPath=' . $cPath . '&' : '') . 'id=' . $listing['products_id']) . '">' . $listing['products_name'] . '</a></td>';
            }
            break;
          case 'PRODUCT_LIST_MANUFACTURER':
            $prod_list_contents .= '<td><a href="' . osc_href_link(null, 'manufacturers_id=' . $listing['manufacturers_id']) . '">' . $listing['manufacturers_name'] . '</a></td>';
            break;
          case 'PRODUCT_LIST_PRICE':
            if (osc_not_null($listing['specials_new_products_price'])) {
              $prod_list_contents .= '<td class="text-right"><del>' .  $currencies->display_price($listing['products_price'], osc_get_tax_rate($listing['products_tax_class_id'])) . '</del>&nbsp;&nbsp;<span class="productSpecialPrice">' . $currencies->display_price($listing['specials_new_products_price'], osc_get_tax_rate($listing['products_tax_class_id'])) . '</span></td>';
            } else {
              $prod_list_contents .= '<td class="text-right">' . $currencies->display_price($listing['products_price'], osc_get_tax_rate($listing['products_tax_class_id'])) . '</td>';
            }
            break;
          case 'PRODUCT_LIST_QUANTITY':
            $prod_list_contents .= '<td class="text-right">' . $listing['products_quantity'] . '</td>';
            break;
          case 'PRODUCT_LIST_WEIGHT':
            $prod_list_contents .= '<td class="text-right">' . $listing['products_weight'] . '</td>';
            break;
          case 'PRODUCT_LIST_IMAGE':
            if (isset($_GET['manufacturers_id'])  && osc_not_null($_GET['manufacturers_id'])) {
              $prod_list_contents .= '<td class="text-center"><a href="' . osc_href_link('products', 'manufacturers_id=' . $_GET['manufacturers_id'] . '&id=' . $listing['products_id']) . '">' . osc_image(DIR_WS_IMAGES . $listing['products_image'], $listing['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>';
            } else {
              $prod_list_contents .= '<td class="text-center"><a href="' . osc_href_link('products', ($cPath ? 'cPath=' . $cPath . '&' : '') . 'id=' . $listing['products_id']) . '">' . osc_image(DIR_WS_IMAGES . $listing['products_image'], $listing['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>';
            }
            break;
          case 'PRODUCT_LIST_BUY_NOW':
            $prod_list_contents .= '<td class="text-center">' . osc_draw_button(IMAGE_BUTTON_BUY_NOW, 'shopping-cart', osc_href_link('cart', 'add&id=' . $listing['products_id'] . '&formid=' . md5($_SESSION['sessiontoken'])), 'success') . '</td>';
            break;
        }
      }

      $prod_list_contents .= '</tr>' . "\n";
    }

    $prod_list_contents .= '          </table>' . "\n";

    echo $prod_list_contents;
  } else {
?>

          <p><?php echo TEXT_NO_PRODUCTS; ?></p>

<?php
  }

  if ( ($listing_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
?>

          <div class="row-fluid pagination pagination-right">
            <div style="float:left"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
            <?php echo $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, osc_get_all_get_params(array('page'))); ?>

          </div>

<?php
  }
?>

        </div>
