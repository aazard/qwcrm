<?php
/**
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
 */

defined('_QWEXEC') or die;

class PaymentTypeRefund {
    
    private $app = null;
    private $VAR = null;    
    private $refund_details = null;
    
    public function __construct() {
                
        // Set class variables
        $this->app = \Factory::getApplication();
        $this->VAR = &\CMSApplication::$VAR;          
        $this->refund_details = $this->app->components->refund->getRecord($this->VAR['qpayment']['refund_id']);
        
        // Set intial record balance
        Payment::$record_balance = (float) $this->refund_details['balance'];
        
        // Assign Type specific template variables
        $this->app->smarty->assign('client_details', $this->app->components->client->getRecord($this->refund_details['client_id']));
        $this->app->smarty->assign('payment_active_methods', $this->app->components->payment->getMethods('send', true, array()));
        $this->app->smarty->assign('refund_details', $this->refund_details);
        $this->app->smarty->assign('refund_statuses', $this->app->components->refund->getStatuses());
        $this->app->smarty->assign('name_on_card', $this->app->components->company->getRecord('company_name'));
        
    }
    
    // Pre-Processing
    public function preProcess() {
        
        // Add required variables
        $this->VAR['qpayment']['client_id'] = $this->refund_details['client_id'];
        $this->VAR['qpayment']['workorder_id'] = $this->refund_details['workorder_id'];
        
        // Validate payment_amount (New Payments)
        if(Payment::$action === 'new') {
            Payment::$record_balance = (float) $this->refund_details['balance'];
            if(!$this->app->components->payment->checkAmountValid(Payment::$record_balance, $this->VAR['qpayment']['amount'])) {
                Payment::$payment_valid = false;
            }
        }
        
        // Validate payment_amount (Payment Update)
        if(Payment::$action === 'update') {
            Payment::$record_balance = ((float) $this->refund_details['balance'] + (float) Payment::$payment_details['amount']);
            if(!$this->app->components->payment->checkAmountValid(Payment::$record_balance, Payment::$payment_details['amount'])) {
                Payment::$payment_valid = false;
            }
        }
        
        return;

    }

    // Processing
    public function process() {
        
        // Recalculate record totals
        $this->app->components->refund->recalculateTotals($this->VAR['qpayment']['refund_id']);
        
        // Refresh the record data        
        $this->refund_details = $this->app->components->refund->getRecord($this->VAR['qpayment']['refund_id']);
        $this->app->smarty->assign('refund_details', $this->refund_details);
        Payment::$record_balance = (float) $this->refund_details['balance'];
        
        return;
        
    }
    
    // Post-Processing 
    public function postProcess() {
        
        // If the balance has been cleared, redirect to the record details page
        if($this->refund_details['balance'] == 0) {
            $this->app->system->variables->systemMessagesWrite('success', _gettext("The balance has been cleared."));
            $this->app->system->page->forcePage('refund', 'details&refund_id='.$this->VAR['refund_id']);
        }
        
        return;
       
    }
    
    // Build Buttons
    public function buildButtons() {
        
        // Submit
        if($this->refund_details['balance'] > 0) {
            Payment::$buttons['submit']['allowed'] = true;
            Payment::$buttons['submit']['url'] = null;
            Payment::$buttons['submit']['title'] = _gettext("Submit Payment");
        }        
        
        // Cancel
        if(!$this->refund_details['balance'] == 0) {            
            if($this->app->system->security->checkPageAccessedViaQwcrm('refund', 'new') || $this->app->system->security->checkPageAccessedViaQwcrm('refund', 'details')) {
                Payment::$buttons['cancel']['allowed'] = true;
                Payment::$buttons['cancel']['url'] = 'index.php?component=refund&page_tpl=details&refund_id='.$this->VAR['qpayment']['refund_id'];
                Payment::$buttons['cancel']['title'] = _gettext("Cancel");
            }            
        }
        
        // Return To Record
        if($this->app->system->security->checkPageAccessedViaQwcrm('payment', 'new')) {
            Payment::$buttons['returnToRecord']['allowed'] = true;
            Payment::$buttons['returnToRecord']['url'] = 'index.php?component=refund&page_tpl=details&refund_id='.$this->VAR['qpayment']['refund_id'];
            Payment::$buttons['returnToRecord']['title'] = _gettext("Return to Record");
        }
        
        // Add New Record
        Payment::$buttons['addNewRecord']['allowed'] = false;
        Payment::$buttons['addNewRecord']['url'] = null;        
        Payment::$buttons['addNewRecord']['title'] = null;
        
    } 
    
    // Update Payment
    public function update() {
        
        // update the payment
        $this->app->components->payment->updateRecord($this->VAR['qpayment']);
                
        // Recalculate record totals
        $this->app->components->refund->recalculateTotals($this->VAR['qpayment']['refund_id']);
        
        // Refresh the record data        
        //$this->refund_details = $this->app->components->refund->get_refund_details($this->VAR['qpayment']['refund_id']);        
        
        // Load the relevant record details page
        $this->app->system->variables->systemMessagesWrite('success', _gettext("Payment updated successfully and Refund").' '.$this->VAR['qpayment']['refund_id'].' '._gettext("has been updated to reflect this change."));
        $this->app->system->page->forcePage('refund', 'details&refund_id='.$this->VAR['qpayment']['refund_id']);
                
        return;        
        
    }
    
    // Cancel Payment
    public function cancel() {
        
        // Cancel the payment
        $this->app->components->payment->cancelRecord($this->VAR['qpayment']['payment_id']);
                
        // Recalculate record totals
        $this->app->components->refund->recalculateTotals($this->VAR['qpayment']['refund_id']);
        
        // Refresh the record data        
        //$this->refund_details = $this->app->components->refund->get_refund_details($this->VAR['qpayment']['refund_id']);        
        
        // Load the relevant record details page
        $this->app->system->variables->systemMessagesWrite('success', _gettext("Payment cancelled successfully and Refund").' '.$this->VAR['qpayment']['refund_id'].' '._gettext("has been updated to reflect this change."));
        $this->app->system->page->forcePage('refund', 'details&refund_id='.$this->VAR['qpayment']['refund_id']);
                
        return;        
        
    }
    
    // Delete Payment
    public function delete() {
        
        // Delete the payment
        $this->app->components->payment->deleteRecord($this->VAR['qpayment']['payment_id']);
                
        // Recalculate record totals
        $this->app->components->refund->recalculateTotals($this->VAR['qpayment']['refund_id']);
        
        // Refresh the record data        
        //$this->refund_details = $this->app->components->refund->get_refund_details($this->VAR['qpayment']['refund_id']);        
        
        // Load the relevant record details page
        $this->app->system->variables->systemMessagesWrite('success', _gettext("Payment deleted successfully and Refund").' '.$this->VAR['qpayment']['refund_id'].' '._gettext("has been updated to reflect this change."));
        $this->app->system->page->forcePage('refund', 'details&refund_id='.$this->VAR['qpayment']['refund_id']);
                
        return;        
        
    }
    
    // Check Payment is allowed
    public function checkPaymentAllowed() {
        
        $state_flag = true;
        
        /* Is on a different tax system
        if($this->refund_details['tax_system'] != QW_TAX_SYSTEM) {
            $this->app->system->variables->systemMessagesWrite('danger', _gettext("The refund cannot receive a payment because it is on a different tax system."));
            $this->app->system->page->forcePage('refund', 'details&refund_id='.$this->VAR['qpayment']['refund_id']);
            //$state_flag = false;
        }*/

        return $state_flag;
       
    }

}