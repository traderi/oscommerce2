<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

    <h1><span class="pull-right"><?php echo $products_price; ?></span><?php echo $products_name; ?></h1>

    <div class="contentContainer">

<?php
  if (osc_not_null($Qreview->value('products_image'))) {
?>

      <div class="pull-right text-center">
        <?php echo '<a href="' . osc_href_link('products', 'id=' . $Qreview->valueInt('products_id')) . '">' . osc_image(DIR_WS_IMAGES . $Qreview->value('products_image'), $Qreview->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'style="padding:5px"') . '</a>'; ?>

        <p><?php echo osc_draw_button(IMAGE_BUTTON_IN_CART, 'shopping-cart', osc_href_link('cart', 'add&id=' . $_GET['id'] . '&formid=' . md5($_SESSION['sessiontoken'])), 'success'); ?></p>
      </div>

<?php
  }
?>

      <div>
        <span class="pull-right"><?php echo sprintf(TEXT_REVIEW_DATE_ADDED, osc_date_long($Qreview->value('date_added'))); ?></span>
        <h2><?php echo sprintf(TEXT_REVIEW_BY, osc_output_string_protected($Qreview->value('customers_name'))); ?></h2>
      </div>

      <div class="contentText">
        <?php echo osc_break_string(nl2br(osc_output_string_protected($Qreview->value('reviews_text'))), 60, '-<br />') . '<br /><br /><i>' . sprintf(TEXT_REVIEW_RATING, osc_image(DIR_WS_IMAGES . 'stars_' . $Qreview->valueInt('reviews_rating') . '.gif', sprintf(TEXT_OF_5_STARS, $Qreview->valueInt('reviews_rating'))), sprintf(TEXT_OF_5_STARS, $Qreview->valueInt('reviews_rating'))) . '</i>'; ?>
      </div>

      <div class="buttonSet">
        <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_WRITE_REVIEW, 'comment', osc_href_link('products', 'reviews&new&id=' . $_GET['id']), 'warning'); ?></span>
        <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'arrow-left', osc_href_link('products', 'reviews&id=' . $_GET['id'])); ?>

      </div>

    </div>
