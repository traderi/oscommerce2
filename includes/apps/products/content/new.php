<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

      <h1><?php echo HEADING_TITLE_NEW; ?></h1>

      <div class="contentContainer">
        <div class="contentText">

<?php
  $products_new_array = array();

  $products_new_query_raw = "select p.products_id, pd.products_name, p.products_image, p.products_price, p.products_tax_class_id, p.products_date_added, m.manufacturers_name from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on (p.manufacturers_id = m.manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by p.products_date_added DESC, pd.products_name";
  $products_new_split = new splitPageResults($products_new_query_raw, MAX_DISPLAY_PRODUCTS_NEW);

  if (($products_new_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>

          <div class="row-fluid pagination pagination-right">
            <div class="pull-left"><?php echo $products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW); ?></div>
            <?php echo $products_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, 'products&new'); ?>

          </div>
<?php
  }

  if ($products_new_split->number_of_rows > 0) {
?>

          <table class="table table-striped">
            <tbody>
<?php
    $products_new_query = osc_db_query($products_new_split->sql_query);
    while ($products_new = osc_db_fetch_array($products_new_query)) {
      if ($new_price = osc_get_products_special_price($products_new['products_id'])) {
        $products_price = '<del>' . $currencies->display_price($products_new['products_price'], osc_get_tax_rate($products_new['products_tax_class_id'])) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($new_price, osc_get_tax_rate($products_new['products_tax_class_id'])) . '</span>';
      } else {
        $products_price = $currencies->display_price($products_new['products_price'], osc_get_tax_rate($products_new['products_tax_class_id']));
      }
?>

            <tr>
              <td style="width:<?php echo SMALL_IMAGE_WIDTH; ?>px"><?php echo '<a href="' . osc_href_link('products', 'id=' . $products_new['products_id']) . '">' . osc_image(DIR_WS_IMAGES . $products_new['products_image'], $products_new['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>'; ?></td>

              <td>
                <span class="pull-right"><?php echo osc_draw_button(IMAGE_BUTTON_IN_CART, 'shopping-cart', osc_href_link('cart', 'add&id=' . $products_new['products_id'] . '&formid=' . md5($_SESSION['sessiontoken'])), 'success'); ?></span>

                <?php echo '<a href="' . osc_href_link('products', 'id=' . $products_new['products_id']) . '"><strong><u>' . $products_new['products_name'] . '</u></strong></a><br />' . TEXT_DATE_ADDED_NEW . ' ' . osc_date_long($products_new['products_date_added']) . '<br />' . TEXT_MANUFACTURER_NEW . ' ' . $products_new['manufacturers_name'] . '<br /><br />' . TEXT_PRICE_NEW . ' ' . $products_price; ?>

              </td>
            </tr>
<?php
    }
?>

            </tbody>
          </table>

<?php
  } else {
?>

    <div>
      <?php echo TEXT_NO_NEW_PRODUCTS; ?>
    </div>

<?php
  }

  if (($products_new_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>

          <div class="row-fluid pagination pagination-right">
            <div class="pull-left"><?php echo $products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW); ?></div>
            <?php echo $products_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, 'products&new'); ?>

          </div>

<?php
  }
?>

        </div>
      </div>
