<?php
require_once 'CRM/Core/Page.php';
class CRM_PayjunctionSmart_Page_Webhook extends CRM_Core_Page {
    function run() {
      $order_id = $_GET['order_id'];
      for($i=0;$i<10;$i++){
        $result = civicrm_api3('Contribution', 'get', array(
          'sequential' => 1,
          'invoice_id' => $order_id,
        ));
        $status = $result['values'][0]['contribution_status'];
        $contribution_id = $result['id'];
        $contact_id = $result['values'][0]['contact_id'];
        if($status == 'Pending'){
          sleep(2);
        }else{
          $finalURL = '/civicrm/contact/view/contribution?reset=1&action=view&id='.$contribution_id.'&cid='.$contact_id.'&context=dashboard';
          CRM_Utils_System::redirect( $finalURL );
        }
      }
      $finalURL = '/civicrm/contact/view/contribution?reset=1&action=view&id='.$contribution_id.'&cid='.$contact_id.'&context=dashboard';
      CRM_Utils_System::redirect( $finalURL );
    parent::run();
  }
}
