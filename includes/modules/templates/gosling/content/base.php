<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  $OSCOM_Template->buildBlocks();

  if (!$OSCOM_Template->hasBlocks('boxes_column_left')) {
    $OSCOM_Template->setGridContentWidth($OSCOM_Template->getGridContentWidth() + $OSCOM_Template->getGridColumnWidth());
  }

  if (!$OSCOM_Template->hasBlocks('boxes_column_right')) {
    $OSCOM_Template->setGridContentWidth($OSCOM_Template->getGridContentWidth() + $OSCOM_Template->getGridColumnWidth());
  }
?>
<!doctype html>

<html <?php echo HTML_PARAMS; ?>>

<head>

<meta charset="<?php echo CHARSET; ?>" />

<title><?php echo osc_output_string_protected($OSCOM_Template->getTitle()); ?></title>

<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />

<link rel="icon" type="image/png" href="{publiclink}images/oscommerce_icon.png{publiclink}" />

<meta name="generator" content="osCommerce Online Merchant" />

<link rel="stylesheet" href="public/template/gosling/css/general.css" />

<script src="ext/jquery/jquery-1.9.1.min.js"></script>
<script src="ext/bootstrap/js/bootstrap.min.js"></script>

<?php
  if ( !empty($_GET) && osc_sanitize_string(basename(key(array_slice($_GET, 0, 1, true)))) == 'products') {
?>

<script src="ext/jquery/bxGallery/jquery.bxGallery.1.1.min.js"></script>
<link rel="stylesheet" href="ext/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="ext/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>

<?php
  }

  echo $OSCOM_Template->getBlocks('header_tags');
?>

</head>
<body>

<div id="bodyWrapper" class="container-fluid" style="overflow:hidden">

<?php
  if ( $OSCOM_MessageStack->exists('header') ) {
    echo '<div class="row-fluid">' . $OSCOM_MessageStack->get('header') . '</div>';
  }
?>

  <div id="header" class="row-fluid">
    <div id="storeLogo"><?php echo '<a href="' . osc_href_link() . '">' . osc_image(DIR_WS_IMAGES . 'store_logo.png', STORE_NAME) . '</a>'; ?></div>

    <div id="headerShortcuts" class="btn-group">
<?php
  echo osc_draw_button(HEADER_TITLE_CART_CONTENTS . ($_SESSION['cart']->count_contents() > 0 ? ' (' . $_SESSION['cart']->count_contents() . ')' : ''), 'shopping-cart', osc_href_link('cart'), ($OSCOM_APP->getCode() == 'cart' ? 'primary' : null)) .
       osc_draw_button(HEADER_TITLE_CHECKOUT, 'play', osc_href_link('checkout', '', 'SSL'), ($OSCOM_APP->getCode() == 'checkout' ? 'primary' : null)) .
       osc_draw_button(HEADER_TITLE_MY_ACCOUNT, 'user', osc_href_link('account', '', 'SSL'), ($OSCOM_APP->getCode() == 'account' ? 'primary' : null));

  if ( $OSCOM_Customer->isLoggedOn() ) {
    echo osc_draw_button(HEADER_TITLE_LOGOFF, null, osc_href_link('account', 'logoff', 'SSL'));
  }
?>

    </div>
  </div>

<script>
$('#headerShortcuts').buttonset();
</script>

  <div class="row-fluid">
    <?php echo $OSCOM_Breadcrumb->get(); ?>

  </div>

  <div class="row-fluid">
<?php
  if ( $OSCOM_Template->hasBlocks('boxes_column_left') ) {
?>

    <div id="columnLeft" class="span<?php echo $OSCOM_Template->getGridColumnWidth(); ?>">
<?php
    $leftBlocks = $OSCOM_Template->getBlocksArray('boxes_column_left');
    foreach ($leftBlocks as $leftBlock) {
?>

      <div class="well well-small">
        <ul class="nav nav-list"><?php echo $leftBlock; ?></ul>
      </div>
<?php
    }
?>

    </div>
<?php
  }
?>

    <div id="bodyContent" class="span<?php echo $OSCOM_Template->getGridContentWidth(); ?>">
<?php
  if (isset($_GET['error_message']) && osc_not_null($_GET['error_message'])) {
?>

      <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo htmlspecialchars(urldecode($_GET['error_message'])); ?>

      </div>
<?php
  }

  if (isset($_GET['info_message']) && osc_not_null($_GET['info_message'])) {
?>

      <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo htmlspecialchars(urldecode($_GET['info_message'])); ?>

      </div>
<?php
  }

  require($OSCOM_APP->getContentFile(true));
?>

    </div>
<?php
  if ( $OSCOM_Template->hasBlocks('boxes_column_right') ) {
?>

    <div id="columnRight" class="span<?php echo $OSCOM_Template->getGridColumnWidth(); ?>">
<?php
    $rightBlocks = $OSCOM_Template->getBlocksArray('boxes_column_right');
    foreach ($rightBlocks as $rightBlock) {
?>

      <div class="well well-small">
        <ul class="nav nav-list"><?php echo $rightBlock; ?></ul>
      </div>
<?php
    }
?>

    </div>
<?php
  }
?>

    <div class="footer span12 text-center">
      <p><?php echo FOOTER_TEXT_BODY; ?></p>
    </div>

<?php
  if ($banner = osc_banner_exists('dynamic', '468x50')) {
?>

    <div class="span12 text-center" style="padding-bottom: 20px;">
      <?php echo osc_display_banner('static', $banner); ?>

    </div>

<?php
  }
?>

  </div>
</div>

<script>
$('.productListTable tr:nth-child(even)').addClass('alt');
</script>

<?php
  echo $OSCOM_Template->getBlocks('footer_scripts');
?>

</body>
</html>
