<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_CONTACT; ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('contact') ) {
    echo $OSCOM_MessageStack->get('contact');
  }
?>

<?php echo osc_draw_form('contact_us', osc_href_link('info', 'contact&process'), 'post', 'class="form-horizontal"', true); ?>

  <div class="control-group">
    <label class="control-label" for="name"><?php echo ENTRY_NAME; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('name', '', 'id="name" placeholder="' . ENTRY_NAME . '"'); ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="email"><?php echo ENTRY_EMAIL; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('email', '', 'id="email" placeholder="' . ENTRY_EMAIL . '"'); ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="enquiry"><?php echo ENTRY_ENQUIRY; ?></label>
    <div class="controls">
      <?php echo osc_draw_textarea_field('enquiry', 'soft', 50, 15, '', 'id="enquiry" placeholder="' . ENTRY_ENQUIRY . '"'); ?>
    </div>
  </div>

  <div class="control-group">
    <div class="controls">
      <?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?>
    </div>
  </div>

</form>
