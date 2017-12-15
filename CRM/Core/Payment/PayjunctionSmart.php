<?php

/*
 * Payment Processor class for PayjunctionSmart
 */

class CRM_Core_Payment_PayjunctionSmart extends CRM_Core_Payment {

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var object
   * @static
   */
  static private $_singleton = NULL;

  /**
   * Mode of operation: live or test.
   *
   * @var object
   */
  protected $_mode = NULL;

  /**
   * Constructor
   *
   * @param string $mode
   *   The mode of operation: live or test.
   *
   * @return void
   */
  public function __construct($mode, &$paymentProcessor) {
    $this->_mode = $mode;
    $this->_islive = ($mode == 'live' ? 1 : 0);
    $this->_paymentProcessor = $paymentProcessor;
    $this->_processorName = ts('Payjunction Smart');
  }

  /**
   * This function checks to see if we have the right config values.
   *
   * @return string
   *   The error message if any.
   *
   * @public
   */
  public function checkConfig() {
    $config = CRM_Core_Config::singleton();
    $error = array();

    if (empty($this->_paymentProcessor['user_name'])) {
      $error[] = ts('The "Secret Key" is not set in the PayjunctionSmart Payment Processor settings.');
    }

    if (empty($this->_paymentProcessor['password'])) {
      $error[] = ts('The "Publishable Key" is not set in the PayjunctionSmart Payment Processor settings.');
    }

    if (!empty($error)) {
      return implode('<p>', $error);
    }
    else {
      return NULL;
    }
  }


  /**
   * Implementation of hook_civicrm_buildForm().
   *
   * @param $form - reference to the form object
   */
  public function buildForm(&$form) {

    $payjunctionsmart_ppid = self::get_payjunctionsmart_ppid($form);
    $user_name = civicrm_api3('PaymentProcessor', 'getvalue', array(
      'return' => "user_name",
      'id' => $payjunctionsmart_ppid,
    ));
    $password= civicrm_api3('PaymentProcessor', 'getvalue', array(
      'return' => "password",
      'id' => $payjunctionsmart_ppid,
    ));
    $signature = civicrm_api3('PaymentProcessor', 'getvalue', array(
      'return' => "signature",
      'id' => $payjunctionsmart_ppid,
    ));
    $url_site = civicrm_api3('PaymentProcessor', 'getvalue', array(
      'return' => "url_site",
      'id' => $payjunctionsmart_ppid,
    ));

    $curl = curl_init();
    curl_setopt_array($curl, array(
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_URL => $url_site.'/smartterminals/',
          CURLOPT_USERPWD => $user_name.':'.$password,
          CURLOPT_HTTPHEADER => array(
            'X-PJ-Application-Key: '.$signature
            )
          ));
    $smart_terminals = curl_exec($curl);
    curl_close($curl);
    $smart_terminal_options = array();
    $option_tag = '';

    foreach(json_decode($smart_terminals)->results as $key=>$value){
      $option_tag .= '<option value="'.$value->smartTerminalId.'">'.$value->nickName.'</option>';
    }

    $form->addElement('hidden', 'payjunctionsmart_token', $payjunctionsmart_token, array('id' => 'payjunctionsmart-token'));
    $form->add('text', 'payjunction_smart_terminal', ts('Test field'));

       CRM_Core_Region::instance('billing-block')->add(array(
         'markup' => '<div class="crm-section">
                <div class="label"><label for="smart_terminal">Smart Terminal</label></div>
                <div class="content other_amount-content">
                <select id="payjunctionsmart_smart_terminal">'.$option_tag.'</select></div><div class="clear"></div>
                <script type="text/javascript">
                CRM.$(function($) {
                  $("#payjunctionsmart-smart-terminal").val($("#payjunctionsmart_smart_terminal").val());
                  $("#payjunctionsmart_smart_terminal").on("change", function() {
                    $("#payjunctionsmart-smart-terminal").val($("#payjunctionsmart_smart_terminal").val());
                  });
                });
                </script>
                '
      ));
    $form->addElement('hidden', 'payjunctionsmart_api_signature',$signature, array('id' => 'payjunctionsmart-api-signature'));
    $form->addElement('hidden', 'payjunctionsmart_api_login',$user_name, array('id' => 'payjunctionsmart-api-login'));
    $form->addElement('hidden', 'payjunctionsmart_api_password',$password, array('id' => 'payjunctionsmart-api-password'));
    $form->addElement('hidden', 'payjunctionsmart_api_url',$url_site, array('id' => 'payjunctionsmart-api-url'));
    $form->addElement('hidden', 'payjunctionsmart_smart_terminal','', array('id' => 'payjunctionsmart-smart-terminal'));

  }

 public static function get_payjunctionsmart_ppid($form) {
    if (empty($form->_paymentProcessor)) {
      return;
    }
    // Determine if we are dealing with a webform in CiviCRM 4.7.  Those don't have a
    //  _paymentProcessors array and only have one payprocesssor.
    if (in_array(get_class($form), array('CRM_Financial_Form_Payment', 'CRM_Contribute_Form_Contribution'))) {
      return $payjunctionsmart_ppid = $form->_paymentProcessor['id'];
    }
    else {
      // Find a PayjunctionSmart pay processor ascociated with this Civi form and find the ID.
   //   $payProcessors = $form->_paymentProcessors;
      $payProcessors = CRM_Core_Form_PayjunctionSmart::get_ppids($form);
      foreach ($payProcessors as $payProcessor) {
        if ($payProcessor['class_name'] == 'Payment_PayjunctionSmart') {
          return $payjunctionsmart_ppid = $payProcessor['id'];
          break;
        }
      }
    }
    // None of the payprocessors are PayjunctionSmart.
    if (empty($payjunctionsmart_ppid)) {
      return;
    }
  }

  /**
   * Given a payment processor id, return the pub key.
   */
  public function payjunctionsmart_get_key($payjunctionsmart_ppid) {
    try {
      $result = civicrm_api3('PaymentProcessor', 'getvalue', array(
        'return' => "password",
        'id' => $payjunctionsmart_ppid,
      ));
    }
    catch (CiviCRM_API3_Exception $e) {
      return NULL;
    }
    return $result;
  }


  /**
   * Return the CiviCRM version we're running.
   */
  public function get_civi_version() {
    $version = civicrm_api3('Domain', 'getvalue', array(
      'return' => "version",
      'current_domain' => true,
    ));
    return $version;
  }
  public function doDirectPayment(&$params) {
      $amount = $params['amount'];
      $api_login = $params['payjunctionsmart_api_login'];
      $api_password = $params['payjunctionsmart_api_password'];
      $api_url = $params['payjunctionsmart_api_url'];
      $api_terminal = $params['payjunctionsmart_smart_terminal'];
      $api_signature = $params['payjunctionsmart_api_signature'];
      $contribution_id = $params['contributionID'];
      $invoice_id = $params['invoiceID'];
      $amount = $params['amount'];
    	$curl = curl_init();
    	$fields = array(
    			'amountBase' => $amount,
    			'terminalId' => '11649',
    			'invoiceNumber' => $contribution_id
    		       );

    	curl_setopt_array($curl, array(
    				CURLOPT_RETURNTRANSFER => 1,
    				CURLOPT_URL => $api_url.'/smartterminals/'.$api_terminal.'/request-payment',
    				CURLOPT_USERPWD => $api_login.':'.$api_password,
    				CURLOPT_HTTPHEADER => array(
    					'X-PJ-Application-Key: '.$api_signature,
    					'Content-Type: application/x-www-form-urlencoded'
    					),
    				CURLOPT_POSTFIELDS => http_build_query($fields),
    				CURLOPT_POST => TRUE
    				));
    	$resp = curl_exec($curl);
    	$response_data = json_decode($resp,TRUE);
      $request_payment_id = $response_data['requestPaymentId'];
      curl_close($curl);

      require_once "CRM/Core/DAO.php";
      $query = "INSERT INTO olt_drp.order_smart_terminal_response_mapping values ('".$invoice_id."','".$request_payment_id."')";

      CRM_Core_DAO::executeQuery($query);
  }
}











