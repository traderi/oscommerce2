<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class sb_pinterest {
    var $code = 'sb_pinterest';
    var $title;
    var $description;
    var $sort_order;
    var $icon;
    var $enabled = false;

    function sb_pinterest() {
      $this->title = MODULE_SOCIAL_BOOKMARKS_PINTEREST_TITLE;
      $this->public_title = MODULE_SOCIAL_BOOKMARKS_PINTEREST_PUBLIC_TITLE;
      $this->description = MODULE_SOCIAL_BOOKMARKS_PINTEREST_DESCRIPTION;

      if ( defined('MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS == 'True');
      }
    }

    function getOutput() {
      global $OSCOM_Template;

// add the js in the footer
      $OSCOM_Template->addBlock('<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>', 'footer_scripts');

      $params = array();

// grab the product name (used for description)
      $params['description'] = osc_get_products_name($_GET['id']);

// and image (used for media)
      $image_query = osc_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . osc_get_prid($_GET['id']) . "'");
      $image = osc_db_fetch_array($image_query);

      if (osc_not_null($image['products_image'])) {
        $image_file = $image['products_image'];

        $pi_query = osc_db_query("select image from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . osc_get_prid($_GET['id']) . "' order by sort_order");

        if (osc_db_num_rows($pi_query) > 0) {
          while ($pi = osc_db_fetch_array($pi_query)) {
            if (osc_not_null($pi['image'])) {
              $image_file = $pi['image']; // overwrite image with first multiple product image
              break;
            }
          }
        }

        $params['media'] = osc_href_link(DIR_WS_IMAGES . $image_file, '', 'NONSSL', false);
      }

// url
      $params['url'] = osc_href_link('products', 'id=' . $_GET['id'], 'NONSSL', false);

      $output = '<a href="http://pinterest.com/pin/create/button/?';

      foreach ($params as $key => $value) {
        $output .= $key . '=' . urlencode($value) . '&amp;';
      }

      $output = substr($output, 0, -5); //remove last & from the url

      $output .= '" class="pin-it-button" data-count-layout="' . strtolower(MODULE_SOCIAL_BOOKMARKS_PINTEREST_BUTTON_COUNT_POSITION) . '"><img src="//assets.pinterest.com/images/PinExt.png" alt="" title="' . $this->public_title . '" /></a>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function getPublicTitle() {
      return $this->public_title;
    }

    function check() {
      return defined('MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Pinterest Module', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS', 'True', 'Do you want to allow Pinterest Button?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Layout Position', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_BUTTON_COUNT_POSITION', 'None', 'Horizontal or Vertical or None', '6', '2', 'osc_cfg_select_option(array(\'Horizontal\', \'Vertical\', \'None\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_BUTTON_COUNT_POSITION', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER');
    }
  }
?>
