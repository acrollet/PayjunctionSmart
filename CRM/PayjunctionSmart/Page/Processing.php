<?php

require_once 'CRM/Core/Page.php';

class CRM_PayjunctionSmart_Page_Processing extends CRM_Core_Page {
    function run() {
      $invoice_id = $_GET['order_id'];
      civicrm_initialize();
      $manager = CRM_Core_Resources::singleton();
      $manager->addCoreResources();
      $payjunction_smart_url = CRM_Core_Resources::singleton()->getUrl('com.wannapixel.payjunctionsmart', 'js/civicrm_smart.js');
      $manager->addScriptUrl($payjunction_smart_url, 1, 'html-header');
      CRM_Core_Resources::singleton()->addVars('payjunctionsmart', array('invoice_id' => $invoice_id));

    parent::run();
  }

}
