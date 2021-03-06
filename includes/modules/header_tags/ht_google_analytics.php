<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ht_google_analytics {
    var $code = 'ht_google_analytics';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_google_analytics() {
      $this->title = MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_TITLE;
      $this->description = MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS == 'True');
      }
    }

    function execute() {
      global $OSCOM_APP, $OSCOM_Customer, $OSCOM_Template;

      if (osc_not_null(MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID)) {
        if (MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_JS_PLACEMENT != 'Header') {
          $this->group = 'footer_scripts';
        }

        $header = '<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \'' . osc_output_string(MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID) . '\']);
  _gaq.push([\'_trackPageview\']);' . "\n";

        if ( (MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_EC_TRACKING == 'True') && ($OSCOM_APP->getCode() == 'checkout') && ($OSCOM_APP->getCurrentAction() == 'success') && $OSCOM_Customer->isLoggedOn() ) {
          $order_query = osc_db_query("select orders_id, billing_city, billing_state, billing_country from " . TABLE_ORDERS . " where customers_id = '" . (int)$OSCOM_Customer->getID() . "' order by date_purchased desc limit 1");

          if (osc_db_num_rows($order_query) == 1) {
            $order = osc_db_fetch_array($order_query);

            $totals = array();

            $order_totals_query = osc_db_query("select value, class from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order['orders_id'] . "'");
            while ($order_totals = osc_db_fetch_array($order_totals_query)) {
              $totals[$order_totals['class']] = $order_totals['value'];
            }

            $header .= '  _gaq.push([\'_addTrans\',
    \'' . (int)$order['orders_id'] . '\', // order ID - required
    \'' . osc_output_string(STORE_NAME) . '\', // store name
    \'' . (isset($totals['ot_total']) ? $this->format_raw($totals['ot_total'], DEFAULT_CURRENCY) : 0) . '\', // total - required
    \'' . (isset($totals['ot_tax']) ? $this->format_raw($totals['ot_tax'], DEFAULT_CURRENCY) : 0) . '\', // tax
    \'' . (isset($totals['ot_shipping']) ? $this->format_raw($totals['ot_shipping'], DEFAULT_CURRENCY) : 0) . '\', // shipping
    \'' . osc_output_string_protected($order['billing_city']) . '\', // city
    \'' . osc_output_string_protected($order['billing_state']) . '\', // state or province
    \'' . osc_output_string_protected($order['billing_country']) . '\' // country
  ]);' . "\n";

            $order_products_query = osc_db_query("select op.products_id, pd.products_name, op.final_price, op.products_quantity from " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_LANGUAGES . " l where op.orders_id = '" . (int)$order['orders_id'] . "' and op.products_id = pd.products_id and l.code = '" . osc_db_input(DEFAULT_LANGUAGE) . "' and l.languages_id = pd.language_id");
            while ($order_products = osc_db_fetch_array($order_products_query)) {
              $category_query = osc_db_query("select cd.categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_LANGUAGES . " l where p2c.products_id = '" . (int)$order_products['products_id'] . "' and p2c.categories_id = cd.categories_id and l.code = '" . osc_db_input(DEFAULT_LANGUAGE) . "' and l.languages_id = cd.language_id limit 1");
              $category = osc_db_fetch_array($category_query);

              $header .= '  _gaq.push([\'_addItem\',
    \'' . (int)$order['orders_id'] . '\', // order ID - required
    \'' . (int)$order_products['products_id'] . '\', // SKU/code - required
    \'' . osc_output_string($order_products['products_name']) . '\', // product name
    \'' . osc_output_string($category['categories_name']) . '\', // category
    \'' . $this->format_raw($order_products['final_price']) . '\', // unit price - required
    \'' . (int)$order_products['products_quantity'] . '\' // quantity - required
  ]);' . "\n";
            }

            $header .= '  _gaq.push([\'_trackTrans\']); //submits transaction to the Analytics servers' . "\n";
          }
        }

        $header .= '  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>' . "\n";

        $OSCOM_Template->addBlock($header, $this->group);
      }
    }

    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies;

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $_SESSION['currency'];
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(osc_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Google Analytics Module', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS', 'True', 'Do you want to add Google Analytics to your shop?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Google Analytics ID', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID', '', 'The Google Analytics profile ID to track.', '6', '0', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('E-Commerce Tracking', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_EC_TRACKING', 'True', 'Do you want to enable e-commerce tracking? (E-Commerce tracking must also be enabled in your Google Analytics profile settings)', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Javascript Placement', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_JS_PLACEMENT', 'Header', 'Should the Google Analytics javascript be loaded in the header or footer?', '6', '1', 'osc_cfg_select_option(array(\'Header\', \'Footer\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_EC_TRACKING', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_JS_PLACEMENT', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_SORT_ORDER');
    }
  }
?>
