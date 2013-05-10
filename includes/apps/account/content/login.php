<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_LOGIN; ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('login') ) {
    echo $OSCOM_MessageStack->get('login');
  }
?>

<div class="contentContainer row-fluid">
  <div class="contentText span5">
    <h2><?php echo HEADING_NEW_CUSTOMER; ?></h2>

    <p><?php echo TEXT_NEW_CUSTOMER; ?></p>
    <p><?php echo TEXT_NEW_CUSTOMER_INTRODUCTION; ?></p>

    <p class="text-right"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', osc_href_link('account', 'create', 'SSL'), 'success'); ?></p>
  </div>

  <div class="contentText span6 offset1">
    <h2><?php echo HEADING_RETURNING_CUSTOMER; ?></h2>

    <p><?php echo TEXT_RETURNING_CUSTOMER; ?></p>

    <?php echo osc_draw_form('login', osc_href_link('account', 'login&process', 'SSL'), 'post', 'class="form-horizontal"', true); ?>

      <div class="control-group">
        <label class="control-label" for="email_address"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
        <div class="controls">
          <?php echo osc_draw_input_field('email_address', '', 'style="width:auto" id="email_address" placeholder="' . ENTRY_EMAIL_ADDRESS . '"'); ?>

        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="password"><?php echo ENTRY_PASSWORD; ?></label>
        <div class="controls">
          <?php echo osc_draw_password_field('password', '', 'style="width:auto" id="password" placeholder="' . ENTRY_PASSWORD . '"'); ?>

        </div>
      </div>

      <div class="control-group">
        <div class="controls">
          <p><?php echo '<a href="' . osc_href_link('account', 'password&forgotten', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></p>
          <p><?php echo osc_draw_button(IMAGE_BUTTON_LOGIN, 'user', null, 'success'); ?></p>
        </div>
      </div>

    </form>
  </div>
</div>
