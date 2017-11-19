<?php

/*
 * Form Class for Stripe
 */

class CRM_Core_Form_PayjunctionSmart extends CRM_Core_Form {

  /**
   * Function to access protected payProcessors array in event registraion forms
   */
  public static function get_ppids(&$form) {
    $payprocessorIds = $form->_paymentProcessors;
    return $payprocessorIds;
  }
}
