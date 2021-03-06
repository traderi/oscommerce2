<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class paypoint_secpay {
    var $code, $title, $description, $enabled;

// class constructor
    function paypoint_secpay() {
      global $order;

      $this->signature = 'paypoint|paypoint_secpay|1.0|2.3';

      $this->code = 'paypoint_secpay';
      $this->title = MODULE_PAYMENT_PAYPOINT_SECPAY_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYPOINT_SECPAY_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYPOINT_SECPAY_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_PAYPOINT_SECPAY_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_PAYPOINT_SECPAY_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_PAYPOINT_SECPAY_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PAYPOINT_SECPAY_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://www.secpay.com/java-bin/ValCard';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPOINT_SECPAY_ZONE > 0) ) {
        $check_flag = false;
        $check_query = osc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPOINT_SECPAY_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = osc_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      global $order, $currencies;

      switch (MODULE_PAYMENT_PAYPOINT_SECPAY_CURRENCY) {
        case 'Default Currency':
          $sec_currency = DEFAULT_CURRENCY;
          break;
        case 'Any Currency':
        default:
          $sec_currency = $_SESSION['currency'];
          break;
      }

      switch (MODULE_PAYMENT_PAYPOINT_SECPAY_TEST_STATUS) {
        case 'Always Fail':
          $test_status = 'false';
          break;
        case 'Production':
          $test_status = 'live';
          break;
        case 'Always Successful':
        default:
          $test_status = 'true';
          break;
      }

// Calculate the digest to send to SECPAY

      $digest_string = STORE_NAME . date('Ymdhis') . number_format($order->info['total'] * $currencies->get_value($sec_currency), $currencies->currencies[$sec_currency]['decimal_places'], '.', '') . MODULE_PAYMENT_PAYPOINT_SECPAY_REMOTE;

// There is a bug in the digest code, if there are any spaces in the trans id ( usually in the STORE_NAME
// SECPay will replace these with an _ and the hash is calculated of that so need to do a search and replace
// in the digest_string for spaces and replace with _
      $digest_string = str_replace(' ', '_', $digest_string);

      $digest = md5($digest_string);

// Incase this gets 'fixed' at the SECPay end do a search and replace on the trans_id too
      $trans_id_string = STORE_NAME . date('Ymdhis');
      $trans_id = str_replace(' ', '_', $trans_id_string);

      $process_button_string = osc_draw_hidden_field('merchant', MODULE_PAYMENT_PAYPOINT_SECPAY_MERCHANT_ID) .
                               osc_draw_hidden_field('trans_id', $trans_id) .
                               osc_draw_hidden_field('amount', number_format($order->info['total'] * $currencies->get_value($sec_currency), $currencies->currencies[$sec_currency]['decimal_places'], '.', '')) .
                               osc_draw_hidden_field('bill_name', $order->billing['firstname'] . ' ' . $order->billing['lastname']) .
                               osc_draw_hidden_field('bill_addr_1', $order->billing['street_address']) .
                               osc_draw_hidden_field('bill_addr_2', $order->billing['suburb']) .
                               osc_draw_hidden_field('bill_city', $order->billing['city']) .
                               osc_draw_hidden_field('bill_state', $order->billing['state']) .
                               osc_draw_hidden_field('bill_post_code', $order->billing['postcode']) .
                               osc_draw_hidden_field('bill_country', $order->billing['country']['title']) .
                               osc_draw_hidden_field('bill_tel', $order->customer['telephone']) .
                               osc_draw_hidden_field('bill_email', $order->customer['email_address']) .
                               osc_draw_hidden_field('ship_name', $order->delivery['firstname'] . ' ' . $order->delivery['lastname']) .
                               osc_draw_hidden_field('ship_addr_1', $order->delivery['street_address']) .
                               osc_draw_hidden_field('ship_addr_2', $order->delivery['suburb']) .
                               osc_draw_hidden_field('ship_city', $order->delivery['city']) .
                               osc_draw_hidden_field('ship_state', $order->delivery['state']) .
                               osc_draw_hidden_field('ship_post_code', $order->delivery['postcode']) .
                               osc_draw_hidden_field('ship_country', $order->delivery['country']['title']) .
                               osc_draw_hidden_field('currency', $sec_currency) .
                               osc_draw_hidden_field('callback', osc_href_link('checkout', 'process', 'SSL', false) . ';' . osc_href_link('checkout', 'payment&payment_error=' . $this->code, 'SSL', false)) .
                               osc_draw_hidden_field(session_name(), session_id()) .
                               osc_draw_hidden_field('options', 'test_status=' . $test_status . ',dups=false,cb_flds=' . session_name()) .
                               osc_draw_hidden_field('digest', $digest);

      return $process_button_string;
    }

    function before_process() {
      if ( ($_GET['valid'] == 'true') && ($_GET['code'] == 'A') && !empty($_GET['auth_code']) && empty($_GET['resp_code']) && !empty($_GET[session_name()]) ) {
        $DIGEST_PASSWORD = MODULE_PAYMENT_PAYPOINT_SECPAY_READERS_DIGEST;
        list($REQUEST_URI, $CHECK_SUM) = split('hash=', $_SERVER['REQUEST_URI']);

        if ($_GET['hash'] != md5($REQUEST_URI . $DIGEST_PASSWORD)) {
          osc_redirect(osc_href_link('checkout', 'payment&' . session_name() . '=' . $_GET[session_name()] . '&payment_error=' . $this->code ."&detail=hash", 'SSL', false, false));
        }
      } else {
        osc_redirect(osc_href_link('checkout', 'payment&' . session_name() . '=' . $_GET[session_name()] . '&payment_error=' . $this->code, 'SSL', false, false));
      }
    }

    function after_process() {
      return false;
    }

    function get_error() {
      if ($_GET['code'] == 'N') {
        $error = MODULE_PAYMENT_PAYPOINT_SECPAY_TEXT_ERROR_MESSAGE_N;
      } elseif ($_GET['code'] == 'C') {
        $error = MODULE_PAYMENT_PAYPOINT_SECPAY_TEXT_ERROR_MESSAGE_C;
      } else {
        $error = MODULE_PAYMENT_PAYPOINT_SECPAY_TEXT_ERROR_MESSAGE;
      }

      return array('title' => MODULE_PAYMENT_PAYPOINT_SECPAY_TEXT_ERROR,
                   'error' => $error);
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = osc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPOINT_SECPAY_STATUS'");
        $this->_check = osc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PayPoint.net SECPay Module', 'MODULE_PAYMENT_PAYPOINT_SECPAY_STATUS', 'False', 'Do you want to accept PayPoint.net SECPay payments?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID', 'MODULE_PAYMENT_PAYPOINT_SECPAY_MERCHANT_ID', 'secpay', 'Merchant ID to use for the SECPay service', '6', '2', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_PAYPOINT_SECPAY_CURRENCY', 'Any Currency', 'The currency to use for credit card transactions', '6', '3', 'osc_cfg_select_option(array(\'Any Currency\', \'Default Currency\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_PAYPOINT_SECPAY_TEST_STATUS', 'Always Successful', 'Transaction mode to use for the PayPoint.net SECPay service', '6', '4', 'osc_cfg_select_option(array(\'Always Successful\', \'Always Fail\', \'Production\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYPOINT_SECPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYPOINT_SECPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_get_zone_class_title', 'osc_cfg_pull_down_zone_classes(', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYPOINT_SECPAY_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_pull_down_order_statuses(', 'osc_get_order_status_name', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Remote Password', 'MODULE_PAYMENT_PAYPOINT_SECPAY_REMOTE', 'secpay', 'The Remote Password needs to be created in the PayPoint extranet.', '6', '0', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Digest Key', 'MODULE_PAYMENT_PAYPOINT_SECPAY_READERS_DIGEST', 'secpay', 'The Digest Key needs to be created in the PayPoint extranet.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_PAYPOINT_SECPAY_STATUS', 'MODULE_PAYMENT_PAYPOINT_SECPAY_MERCHANT_ID', 'MODULE_PAYMENT_PAYPOINT_SECPAY_REMOTE', 'MODULE_PAYMENT_PAYPOINT_SECPAY_READERS_DIGEST', 'MODULE_PAYMENT_PAYPOINT_SECPAY_CURRENCY', 'MODULE_PAYMENT_PAYPOINT_SECPAY_TEST_STATUS', 'MODULE_PAYMENT_PAYPOINT_SECPAY_ZONE', 'MODULE_PAYMENT_PAYPOINT_SECPAY_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYPOINT_SECPAY_SORT_ORDER');
    }
  }
?>
