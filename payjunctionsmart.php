<?php

require_once 'payjunctionsmart.civix.php';

/**
 * Implementation of hook_civicrm_config().
 */
function payjunctionsmart_civicrm_config(&$config) {
    _payjunctionsmart_civix_civicrm_config($config);
    $extRoot = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR;
    $include_path = $extRoot . PATH_SEPARATOR . get_include_path( );
    set_include_path( $include_path );
}

/**
 * Implementation of hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 */
function payjunctionsmart_civicrm_xmlMenu(&$files) {
    _payjunctionsmart_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install().
 */
function payjunctionsmart_civicrm_install() {
    // Create required tables for Stripe.
    require_once "CRM/Core/DAO.php";
    CRM_Core_DAO::executeQuery("
  CREATE TABLE IF NOT EXISTS `smart_junction_payment_tracking` (
    `contact_id` int(10) COLLATE utf8_unicode_ci DEFAULT NULL,
    `request_id` longtext COLLATE utf8_unicode_ci DEFAULT NULL,
    `order_id` int(10) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
  ");
  return _payjunctionsmart_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall().
 */
function payjunctionsmart_civicrm_uninstall() {

}


/**
 * Implementation of hook_civicrm_disable().
 */
function payjunctionsmart_civicrm_disable() {
  return _payjunctionsmart_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function payjunctionsmart_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _payjunctionsmart_civix_civicrm_upgrade($op, $queue);
}


/**
 * Implementation of hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function payjunctionsmart_civicrm_managed(&$entities) {
  $entities[] = array(
    'module' => 'com.wannapixel.payjunctionsmart',
    'name' => 'PayjunctionSmart',
    'entity' => 'PaymentProcessorType',
    'params' => array(
      'version' => 3,
      'name' => 'PayjunctionSmart',
      'title' => 'PayjunctionSmart',
      'description' => 'PayjunctionSmart Payment Processor',
      'class_name' => 'Payment_PayjunctionSmart',
      'billing_mode' => 'form',
      'user_name_label' => 'API Login',
      'password_label' => 'Password',
      'signature_label' => 'Transaction Key',
      'payment_type' => 1
    ),
  );
  return _payjunctionsmart_civix_civicrm_managed($entities);
}

  function payjunctionsmart_civicrm_enable(){
    $UF_webhook_paths = array(
      "Drupal"    => "/civicrm/stripe/webhook"
    );
    return _payjunctionsmart_civix_civicrm_enable();
  }

/**
   * Implementation of hook_civicrm_validateForm().
   *
   * Prevent server validation of cc fields
   *
   * @param $formName - the name of the form
   * @param $fields - Array of name value pairs for all 'POST'ed form values
   * @param $files - Array of file properties as sent by PHP POST protocol
   * @param $form - reference to the form object
   * @param $errors - Reference to the errors array.
   *
*/

 function payjunctionsmart_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {



 }
function payjunctionsmart_civicrm_buildForm($formName, &$form){

  if((($formName == 'CRM_Contribute_Form_Contribution') && ($form->isBackOffice == 1)) ||
     (($formName == 'CRM_Financial_Form_Payment') && ($form->_paymentProcessor['name'] == 'payjunction'))){
       $elements = & $form->getElement('credit_card_type');
       $options = & $elements->_options;
       $options[1]['attr']['selected'] = '';
       $defaults['cvv2'] = '123';
       $defaults['credit_card_exp_date'] = '2027-12';
       $defaults['credit_card_number'] = '4111111111111111';
       $form->setDefaults($defaults);
  }
}
