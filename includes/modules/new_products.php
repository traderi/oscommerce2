<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if ( (!isset($new_products_category_id)) || ($new_products_category_id == '0') ) {
    $new_products_query = osc_db_query("select p.products_id, p.products_image, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by p.products_date_added desc limit " . MAX_DISPLAY_NEW_PRODUCTS);
  } else {
    $new_products_query = osc_db_query("select distinct p.products_id, p.products_image, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.parent_id = '" . (int)$new_products_category_id . "' and p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by p.products_date_added desc limit " . MAX_DISPLAY_NEW_PRODUCTS);
  }

  $num_new_products = osc_db_num_rows($new_products_query);

  if ($num_new_products > 0) {
    $counter = 0;
    $col = 0;

    $new_prods_content = '';
    while ($new_products = osc_db_fetch_array($new_products_query)) {
      $counter++;

      if ($col === 0) {
        $new_prods_content .= '        <div class="row-fluid text-center">' . "\n";
      }

      $new_prods_content .= '          <div class="span4"><a href="' . osc_href_link('products', 'id=' . $new_products['products_id']) . '">' . osc_image(DIR_WS_IMAGES . $new_products['products_image'], $new_products['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . osc_href_link('products', 'id=' . $new_products['products_id']) . '">' . $new_products['products_name'] . '</a><br />' . $currencies->display_price($new_products['products_price'], osc_get_tax_rate($new_products['products_tax_class_id'])) . '</div>' . "\n";

      $col ++;

      if (($col > 2) || ($counter == $num_new_products)) {
        $new_prods_content .= '        </div>' . "\n";

        $col = 0;
      }
    }
?>

        <h2><?php echo sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B')); ?></h2>

<?php
    echo $new_prods_content;

  }
?>
