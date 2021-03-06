<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $htaccess_array = null;
  $htpasswd_array = null;
  $is_iis = stripos($_SERVER['SERVER_SOFTWARE'], 'iis');

  $authuserfile_array = array('##### OSCOMMERCE ADMIN PROTECTION - BEGIN #####',
                              'AuthType Basic',
                              'AuthName "osCommerce Online Merchant Administration Tool"',
                              'AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce',
                              'Require valid-user',
                              '##### OSCOMMERCE ADMIN PROTECTION - END #####');

  if (!$is_iis && file_exists(DIR_FS_ADMIN . '.htpasswd_oscommerce') && osc_is_writable(DIR_FS_ADMIN . '.htpasswd_oscommerce') && file_exists(DIR_FS_ADMIN . '.htaccess') && osc_is_writable(DIR_FS_ADMIN . '.htaccess')) {
    $htaccess_array = array();
    $htpasswd_array = array();

    if (filesize(DIR_FS_ADMIN . '.htaccess') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htaccess', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htaccess'));
      fclose($fg);

      $htaccess_array = explode("\n", $data);
    }

    if (filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce'));
      fclose($fg);

      $htpasswd_array = explode("\n", $data);
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (osc_not_null($action)) {
    switch ($action) {
      case 'insert':
        require('includes/functions/password_funcs.php');

        $username = osc_db_prepare_input($_POST['username']);
        $password = osc_db_prepare_input($_POST['password']);

        $check_query = osc_db_query("select id from " . TABLE_ADMINISTRATORS . " where user_name = '" . osc_db_input($username) . "' limit 1");

        if (osc_db_num_rows($check_query) < 1) {
          osc_db_query("insert into " . TABLE_ADMINISTRATORS . " (user_name, user_password) values ('" . osc_db_input($username) . "', '" . osc_db_input(osc_encrypt_password($password)) . "')");

          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }

            if (isset($_POST['htaccess']) && ($_POST['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . osc_crypt_apr_md5($password);
            }

            $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
            fwrite($fp, implode("\n", $htpasswd_array));
            fclose($fp);

            if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
              array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
            } elseif (empty($htpasswd_array)) {
              for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
                if (in_array($htaccess_array[$i], $authuserfile_array)) {
                  unset($htaccess_array[$i]);
                }
              }
            }

            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
        } else {
          $messageStack->add_session(ERROR_ADMINISTRATOR_EXISTS, 'error');
        }

        osc_redirect(osc_href_link(FILENAME_ADMINISTRATORS));
        break;
      case 'save':
        require('includes/functions/password_funcs.php');

        $username = osc_db_prepare_input($_POST['username']);
        $password = osc_db_prepare_input($_POST['password']);

        $check_query = osc_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$_GET['aID'] . "'");
        $check = osc_db_fetch_array($check_query);

// update username in current session if changed
        if ( ($check['id'] == $_SESSION['admin']['id']) && ($check['user_name'] != $_SESSION['admin']['username']) ) {
          $_SESSION['admin']['username'] = $username;
        }

// update username in htpasswd if changed
        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

            if ( ($check['user_name'] == $ht_username) && ($check['user_name'] != $username) ) {
              $htpasswd_array[$i] = $username . ':' . $ht_password;
            }
          }
        }

        osc_db_query("update " . TABLE_ADMINISTRATORS . " set user_name = '" . osc_db_input($username) . "' where id = '" . (int)$_GET['aID'] . "'");

        if (osc_not_null($password)) {
// update password in htpasswd
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }

            if (isset($_POST['htaccess']) && ($_POST['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . osc_crypt_apr_md5($password);
            }
          }

          osc_db_query("update " . TABLE_ADMINISTRATORS . " set user_password = '" . osc_db_input(osc_encrypt_password($password)) . "' where id = '" . (int)$_GET['aID'] . "'");
        } elseif (!isset($_POST['htaccess']) || ($_POST['htaccess'] != 'true')) {
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }
          }
        }

// write new htpasswd file
        if (is_array($htpasswd_array)) {
          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);

          if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
            array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
          } elseif (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }
          }

          $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
          fwrite($fp, implode("\n", $htaccess_array));
          fclose($fp);
        }

        osc_redirect(osc_href_link(FILENAME_ADMINISTRATORS, 'aID=' . (int)$_GET['aID']));
        break;
      case 'deleteconfirm':
        $id = osc_db_prepare_input($_GET['aID']);

        $check_query = osc_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$id . "'");
        $check = osc_db_fetch_array($check_query);

        if ($_SESSION['admin']['id'] == $check['id']) {
          unset($_SESSION['admin']);
        }

        osc_db_query("delete from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$id . "'");

        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

            if ($ht_username == $check['user_name']) {
              unset($htpasswd_array[$i]);
            }
          }

          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);

          if (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }

            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
        }

        osc_redirect(osc_href_link(FILENAME_ADMINISTRATORS));
        break;
    }
  }

  $secMessageStack = new messageStack();

  if (is_array($htpasswd_array)) {
    if (empty($htpasswd_array)) {
      $secMessageStack->add(sprintf(HTPASSWD_INFO, implode('<br />', $authuserfile_array)), 'error');
    } else {
      $secMessageStack->add(HTPASSWD_SECURED, 'success');
    }
  } else if (!$is_iis) {
    $secMessageStack->add(HTPASSWD_PERMISSIONS, 'error');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo osc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>
<?php
  echo $secMessageStack->output();
?>
        </td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ADMINISTRATORS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_HTPASSWD; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $admins_query = osc_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " order by user_name");
  while ($admins = osc_db_fetch_array($admins_query)) {
    if ((!isset($_GET['aID']) || (isset($_GET['aID']) && ($_GET['aID'] == $admins['id']))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
      $aInfo = new objectInfo($admins);
    }


    $htpasswd_secured = osc_image(DIR_WS_IMAGES . 'icon_status_red.gif', 'Not Secured', 10, 10);

    if ($is_iis) {
      $htpasswd_secured = 'N/A';
    }

    if (is_array($htpasswd_array)) {
      for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
        list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

        if ($ht_username == $admins['user_name']) {
          $htpasswd_secured = osc_image(DIR_WS_IMAGES . 'icon_status_green.gif', 'Secured', 10, 10);
          break;
        }
      }
    }

    if ( (isset($aInfo) && is_object($aInfo)) && ($admins['id'] == $aInfo->id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . osc_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . osc_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $admins['id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $admins['user_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $htpasswd_secured; ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($aInfo) && is_object($aInfo)) && ($admins['id'] == $aInfo->id) ) { echo osc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . osc_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $admins['id']) . '">' . osc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="smallText" colspan="3" align="right"><?php echo osc_draw_button(IMAGE_INSERT, 'plus', osc_href_link(FILENAME_ADMINISTRATORS, 'action=new')); ?></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_ADMINISTRATOR . '</strong>');

      $contents = array('form' => osc_draw_form('administrator', FILENAME_ADMINISTRATORS, 'action=insert', 'post', 'autocomplete="off"'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_USERNAME . '<br />' . osc_draw_input_field('username'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_PASSWORD . '<br />' . osc_draw_password_field('password'));

      if (is_array($htpasswd_array)) {
        $contents[] = array('text' => '<br />' . osc_draw_checkbox_field('htaccess', 'true') . ' ' . TEXT_INFO_PROTECT_WITH_HTPASSWD);
      }

      $contents[] = array('align' => 'center', 'text' => '<br />' . osc_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . osc_draw_button(IMAGE_CANCEL, 'close', osc_href_link(FILENAME_ADMINISTRATORS)));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . $aInfo->user_name . '</strong>');

      $contents = array('form' => osc_draw_form('administrator', FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=save', 'post', 'autocomplete="off"'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_USERNAME . '<br />' . osc_draw_input_field('username', $aInfo->user_name));
      $contents[] = array('text' => '<br />' . TEXT_INFO_NEW_PASSWORD . '<br />' . osc_draw_password_field('password'));

      if (is_array($htpasswd_array)) {
        $default_flag = false;

        for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
          list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

          if ($ht_username == $aInfo->user_name) {
            $default_flag = true;
            break;
          }
        }

        $contents[] = array('text' => '<br />' . osc_draw_checkbox_field('htaccess', 'true', $default_flag) . ' ' . TEXT_INFO_PROTECT_WITH_HTPASSWD);
      }

      $contents[] = array('align' => 'center', 'text' => '<br />' . osc_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . osc_draw_button(IMAGE_CANCEL, 'close', osc_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . $aInfo->user_name . '</strong>');

      $contents = array('form' => osc_draw_form('administrator', FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $aInfo->user_name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . osc_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . osc_draw_button(IMAGE_CANCEL, 'close', osc_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id)));
      break;
    default:
      if (isset($aInfo) && is_object($aInfo)) {
        $heading[] = array('text' => '<strong>' . $aInfo->user_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => osc_draw_button(IMAGE_EDIT, 'document', osc_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=edit')) . osc_draw_button(IMAGE_DELETE, 'trash', osc_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=delete')));
      }
      break;
  }

  if ( (osc_not_null($heading)) && (osc_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
