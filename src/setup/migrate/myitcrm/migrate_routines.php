<?php

/*
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
 */

/** Migrate MyITCRM v2.9.3 **/

// This file contains all specific routines for the migrationmodified for the QWcrm v3.0.0 database

defined('_QWEXEC') or die;

class MigrateMyitcrm extends Setup {
    
    /** Mandatory Code **/

    /** Display Functions **/

    /** Insert Functions **/

    #####################################
    #    Insert new user                #
    #####################################

    public function insertUser($VAR) {

        $sql = "INSERT INTO ".PRFX."user SET
                customer_id         =". $this->app->db->qstr( $VAR['customer_id']                          ).", 
                username            =". $this->app->db->qstr( $VAR['username']                             ).",
                password            =". $this->app->db->qstr( \Joomla\CMS\User\UserHelper::hashPassword($VAR['password'])  ).",
                email               =". $this->app->db->qstr( $VAR['email']                                ).",
                usergroup           =". $this->app->db->qstr( $VAR['usergroup']                            ).",
                active              =". $this->app->db->qstr( $VAR['active']                               ).",
                register_date       =". $this->app->db->qstr( time()                                       ).",   
                require_reset       =". $this->app->db->qstr( $VAR['require_reset']                        ).",
                is_employee         =". $this->app->db->qstr( $VAR['is_employee']                          ).",              
                display_name        =". $this->app->db->qstr( $VAR['display_name']                         ).",
                first_name          =". $this->app->db->qstr( $VAR['first_name']                           ).",
                last_name           =". $this->app->db->qstr( $VAR['last_name']                            ).",
                work_primary_phone  =". $this->app->db->qstr( $VAR['work_primary_phone']                   ).",
                work_mobile_phone   =". $this->app->db->qstr( $VAR['work_mobile_phone']                    ).",
                work_fax            =". $this->app->db->qstr( $VAR['work_fax']                             ).",                    
                home_primary_phone  =". $this->app->db->qstr( $VAR['home_primary_phone']                   ).",
                home_mobile_phone   =". $this->app->db->qstr( $VAR['home_mobile_phone']                    ).",
                home_email          =". $this->app->db->qstr( $VAR['home_email']                           ).",
                home_address        =". $this->app->db->qstr( $VAR['home_address']                         ).",
                home_city           =". $this->app->db->qstr( $VAR['home_city']                            ).",  
                home_state          =". $this->app->db->qstr( $VAR['home_state']                           ).",
                home_zip            =". $this->app->db->qstr( $VAR['home_zip']                             ).",
                home_country        =". $this->app->db->qstr( $VAR['home_country']                         ).", 
                based               =". $this->app->db->qstr( $VAR['based']                                ).",  
                notes               =". $this->app->db->qstr( $VAR['notes']                                );                 

        if(!$this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        // Get user_id
        $user_id = $this->app->db->Insert_ID();

        // Log activity
        $record = _gettext("Administrator Account").' '.$user_id.' ('.$this->getUserDetails($user_id, 'username').') '._gettext("for").' '.$this->getUserDetails($user_id, 'display_name').' '._gettext("created").'.';
        $this->writeRecordToSetupLog('migrate', $record);

        return $user_id;

    }

    /** Get Functions **/

    ##########################
    #  Get Company details   #
    ##########################

    /*
     * This combined public static function allows you to pull any of the company information individually
     * or return them all as an array
     * supply the required field name for a single item or all for all items as an array.
     */

    public function getCompanyDetails($item = null) {

        $sql = "SELECT * FROM ".PRFX."company";

        if(!$rs = $this->app->db->execute($sql)) { 

            // If the company lookup fails
            die('
                    <div style="color: red;">'.
                    '<strong>'._gettext("NB: This is the MyITCRM migrate version of this function.").'</strong><br>'.
                    _gettext("Something went wrong executing an SQL query.").'<br>'.
                    _gettext("Check to see if your Prefix is correct, if not you might have a configuration.php file that should not be present or is corrupt.").'<br>'.
                    _gettext("Error occured at").' <strong>'.__FUNCTION__.'()</strong> '._gettext("when trying to get the variable").' <strong>date_format</strong>'.'<br>'.
                    '</div>'
               );        

            // Any other lookup error
            $this->app->system->page->forceErrorPage('system', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql, _gettext("This is the first function loaded for the variable date_format."));        

        } else {

            if($item === null) {

                return $rs->GetRowAssoc();            

            } else {

                return $rs->fields[$item];   

            } 

        }

    }

    ##################################
    #  Get MyITCRM company details   #
    ##################################

    public function getCompanyDetailsMyitcrm($item = null) {

        $sql = "SELECT * FROM ".$this->app->config->get('myitcrm_prefix')."TABLE_COMPANY";

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        if($item === null) {

            return $rs->GetRowAssoc();            

        } else {

            return $rs->fields[$item];   

        }  

    }

    ##############################################
    #  Merge QWcrm and MyITCRM company details   #
    ##############################################

    public function getCompanyDetailsMerged() {

        $qwcrm_company_details              = $this->getCompanyDetails();
        $myitcrm_company_details            = $this->getCompanyDetailsMyitcrm();

        $merged['display_name']             = $myitcrm_company_details['COMPANY_NAME'];
        $merged['logo']                     = 'logo.png';
        $merged['address']                  = $myitcrm_company_details['COMPANY_ADDRESS'];
        $merged['city']                     = $myitcrm_company_details['COMPANY_CITY'];
        $merged['state']                    = $myitcrm_company_details['COMPANY_STATE'];
        $merged['zip']                      = $myitcrm_company_details['COMPANY_ZIP'];
        $merged['country']                  = $myitcrm_company_details['COMPANY_COUNTRY'];
        $merged['primary_phone']            = $myitcrm_company_details['COMPANY_PHONE'];
        $merged['mobile_phone']             = $myitcrm_company_details['COMPANY_MOBILE'];
        $merged['fax']                      = $myitcrm_company_details['COMPANY_FAX'];
        $merged['email']                    = $myitcrm_company_details['COMPANY_EMAIL'];
        $merged['website']                  = '';
        $merged['company_number']           = $myitcrm_company_details['COMPANY_ABN'];    
        $merged['tax_type']                 = $qwcrm_company_details['tax_type'];
        $merged['tax_rate']                 = $qwcrm_company_details['tax_rate'];
        $merged['vat_number']               = '';
        $merged['year_start']               = time();
        $merged['year_end']                 = strtotime('+1 year');
        //$merged['welcome_msg']              = $qwcrm_company_details['welcome_msg'];
        $merged['currency_symbol']          = $myitcrm_company_details['COMPANY_CURRENCY_SYMBOL'];
        $merged['currency_code']            = $myitcrm_company_details['COMPANY_CURRENCY_CODE'];
        $merged['date_format']              = $myitcrm_company_details['COMPANY_DATE_FORMAT'];
        //$merged['email_signature']          = $qwcrm_company_details['email_signature'];
        //$merged['email_signature_active']   = $qwcrm_company_details['email_signature_active'];
        //$merged['email_msg_invoice']        = $qwcrm_company_details['email_msg_invoice'];
        //$merged['email_msg_workorder']      = $qwcrm_company_details['email_msg_workorder'];

        // NB: the remmed out items are not on the setup company_details page so are added in via myitcrrm/migrate_routines.php

        return $merged;

    }
    
    #####################################
    #     Get User Details              # 
    #####################################

    public function getUserDetails($user_id = null, $item = null) {

        // This allows for workorder:status to work
        if(!$user_id){
            return;        
        }

        $sql = "SELECT * FROM ".PRFX."user WHERE user_id =".$this->app->db->qstr($user_id);

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        if($item === null) {

            return $rs->GetRowAssoc();

        } else {

            if($item === null){

                return $rs->GetRowAssoc();

            } else {

                return $rs->fields[$item];   

            } 

        }    

    }

    /** Update Functions **/
    
    #############################
    #  Update Company details   #
    #############################

    public function updateCompanyDetails($VAR) {

        $sql = null;
        
        // Prevent undefined variable errors
        $VAR['delete_logo'] = isset($VAR['delete_logo']) ? $VAR['delete_logo'] : null;

        // Delete logo if selected and no new logo is presented
        if($VAR['delete_logo'] && !$_FILES['logo']['name']) {
            $this->deleteLogo();        
        }

        // A new logo is supplied, delete old and upload new
        if($_FILES['logo']['name']) {
            $this->deleteLogo();
            $new_logo_filepath = $this->uploadLogo();
        }
    
        $sql .= "UPDATE ".PRFX."company SET
                display_name            =". $this->app->db->qstr( $VAR['display_name']                     ).",";
                    
        if($VAR['delete_logo']) {
            $sql .="logo                =''                                                     ,";
        }

        if(!empty($_FILES['logo']['name'])) {
            $sql .="logo                =". $this->app->db->qstr( $new_logo_filepath                       ).",";
        }
        
        /*if(isset($VAR['logo']) && is_string($VAR['logo'])) {
            $sql .="logo                =". $this->app->db->qstr( $VAR['logo']                             ).",";
        }*/

        $sql .="address                 =". $this->app->db->qstr( $VAR['address']                          ).",
                city                    =". $this->app->db->qstr( $VAR['city']                             ).",
                state                   =". $this->app->db->qstr( $VAR['state']                            ).",
                zip                     =". $this->app->db->qstr( $VAR['zip']                              ).",
                country                 =". $this->app->db->qstr( $VAR['country']                          ).",
                primary_phone           =". $this->app->db->qstr( $VAR['primary_phone']                    ).",
                mobile_phone            =". $this->app->db->qstr( $VAR['mobile_phone']                     ).",
                fax                     =". $this->app->db->qstr( $VAR['fax']                              ).",
                email                   =". $this->app->db->qstr( $VAR['email']                            ).",    
                website                 =". $this->app->db->qstr( $this->app->system->general->processInputtedUrl($VAR['website'])    ).",
                company_number          =". $this->app->db->qstr( $VAR['company_number']                   ).",                                        
                tax_type                =". $this->app->db->qstr( $VAR['tax_type']                         ).",
                tax_rate                =". $this->app->db->qstr( $VAR['tax_rate']                         ).",
                vat_number              =". $this->app->db->qstr( $VAR['vat_number']                       ).",
                year_start              =". $this->app->db->qstr( $this->app->system->general->dateToTimestamp($VAR['year_start'])    ).",
                year_end                =". $this->app->db->qstr( $this->app->system->general->dateToTimestamp($VAR['year_end'])      ).",
                welcome_msg             =". $this->app->db->qstr( $VAR['welcome_msg']                      ).",
                currency_symbol         =". $this->app->db->qstr( htmlentities($VAR['currency_symbol'])    ).",
                currency_code           =". $this->app->db->qstr( $VAR['currency_code']                    ).",
                date_format             =". $this->app->db->qstr( $VAR['date_format']                      ).",
                email_signature         =". $this->app->db->qstr( $VAR['email_signature']                  ).",
                email_signature_active  =". $this->app->db->qstr( $VAR['email_signature_active']           ).",
                email_msg_invoice       =". $this->app->db->qstr( $VAR['email_msg_invoice']                ).",
                email_msg_workorder     =". $this->app->db->qstr( $VAR['email_msg_workorder']              );                          

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}     

        // Assign success message
        $this->app->system->variables->systemMessagesWrite('success', _gettext("Company details updated."));

        // Log activity            
        $this->writeRecordToSetupLog('migrate', _gettext("Company details updated."));

        return; 

    }

    /** Close Functions **/

    /** Delete Functions **/

    /** Migration Routines **/
    
    ############################################
    #   Migrate myitcrm database               #
    ############################################

    public function migrateMyitcrmDatabase($qwcrm_prefix, $myitcrm_prefix) {

        /* Customer */

        // customer
        $column_mappings = array(
            'customer_id'       => 'CUSTOMER_ID',
            'display_name'      => 'CUSTOMER_DISPLAY_NAME',
            'first_name'        => 'CUSTOMER_FIRST_NAME',
            'last_name'         => 'CUSTOMER_LAST_NAME',
            'website'           => 'CUSTOMER_WWW',
            'email'             => 'CUSTOMER_EMAIL',
            'credit_terms'      => 'CREDIT_TERMS',
            'discount_rate'     => 'DISCOUNT',
            'type'              => 'CUSTOMER_TYPE',
            'active'            => '',
            'primary_phone'     => 'CUSTOMER_PHONE',
            'mobile_phone'      => 'CUSTOMER_MOBILE_PHONE',
            'fax'               => 'CUSTOMER_WORK_PHONE',
            'address'           => 'CUSTOMER_ADDRESS',
            'city'              => 'CUSTOMER_CITY',
            'state'             => 'CUSTOMER_STATE',
            'zip'               => 'CUSTOMER_ZIP',
            'country'           => '',
            'notes'             => 'CUSTOMER_NOTES',
            'create_date'       => 'CREATE_DATE',
            'last_active'       => 'LAST_ACTIVE'
            );
        $this->migrateTable($qwcrm_prefix.'customer', $myitcrm_prefix.'TABLE_CUSTOMER', $column_mappings);

        // update customer types
        $this->updateColumnValues($qwcrm_prefix.'customer', 'type', '1', 'residential');
        $this->updateColumnValues($qwcrm_prefix.'customer', 'type', '2', 'commercial');
        $this->updateColumnValues($qwcrm_prefix.'customer', 'type', '3', 'charity');
        $this->updateColumnValues($qwcrm_prefix.'customer', 'type', '4', 'educational');
        $this->updateColumnValues($qwcrm_prefix.'customer', 'type', '5', 'goverment');

        // update active status (all enabled)
        $this->updateColumnValues($qwcrm_prefix.'customer', 'active', '*', '1');

        // customer_notes
        $column_mappings = array(
            'customer_note_id'  => 'ID',
            'employee_id'       => '',
            'customer_id'       => 'CUSTOMER_ID',
            'date'              => 'DATE',
            'note'              => 'NOTE'
            );    
        $this->migrateTable($qwcrm_prefix.'customer_notes', $myitcrm_prefix.'CUSTOMER_NOTES', $column_mappings);    

        /* Expense */

        // expense
        $column_mappings = array(
            'expense_id'        => 'EXPENSE_ID',
            'invoice_id'        => '',
            'payee'             => 'EXPENSE_PAYEE',
            'date'              => 'EXPENSE_DATE',
            'type'              => 'EXPENSE_TYPE',
            'payment_method'    => 'EXPENSE_PAYMENT_METHOD',
            'net_amount'        => 'EXPENSE_NET_AMOUNT',
            'vat_rate'          => 'EXPENSE_TAX_RATE',
            'vat_amount'        => 'EXPENSE_TAX_AMOUNT',
            'gross_amount'      => 'EXPENSE_GROSS_AMOUNT',
            'items'             => 'EXPENSE_ITEMS',
            'notes'             => 'EXPENSE_NOTES'        
            );
        $this->migrateTable($qwcrm_prefix.'expense', $myitcrm_prefix.'TABLE_EXPENSE', $column_mappings);

        // update expense types
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '1', 'advertising');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '2', 'bank_charges');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '3', 'broadband');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '4', 'credit');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '5', 'customer_refund');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '6', 'customer_refund');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '7', 'equipment');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '8', 'gift_certificate');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '9', 'landline');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '10', 'mobile_phone');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '11', 'office_supplies');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '12', 'parts');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '13', 'fuel');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '14', 'postage');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '15', 'tax');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '16', 'rent');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '17', 'transport');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '18', 'utilities');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '19', 'voucher');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '20', 'wages');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'type', '21', 'other');

        // update expense payment method
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '1', 'bank_transfer');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '2', 'card');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '3', 'cash');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '4', 'cheque');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '5', 'credit');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '6', 'direct_debit');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '7', 'gift_certificate');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '8', 'google_checkout');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '9', 'paypal');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '10', 'voucher');
        $this->updateColumnValues($qwcrm_prefix.'expense', 'payment_method', '11', 'other');    

        /* Gifcert */

        // giftcert
        $column_mappings = array(
            'giftcert_id'       => 'GIFT_ID',
            'giftcert_code'     => 'GIFT_CODE',
            'employee_id'       => '',
            'customer_id'       => 'CUSTOMER_ID',
            'invoice_id'        => 'INVOICE_ID',
            'date_created'      => 'DATE_CREATE',
            'date_expires'      => 'EXPIRE',
            'date_redeemed'     => 'DATE_REDEMED',
            'is_redeemed'       => '',
            'amount'            => 'AMOUNT',
            'active'            => 'ACTIVE',
            'notes'             => 'MEMO'        
            );
        $this->migrateTable($qwcrm_prefix.'giftcert', $myitcrm_prefix.'GIFT_CERT', $column_mappings);

        // update date_redeemed to remove incoreect zero dates
        $this->updateColumnValues($qwcrm_prefix.'giftcert', 'date_redeemed', '0', '');

        /* Invoice */

        // invoice
        $column_mappings = array(
            'invoice_id'        => 'INVOICE_ID',
            'employee_id'       => 'EMPLOYEE_ID',
            'customer_id'       => 'CUSTOMER_ID',
            'workorder_id'      => 'WORKORDER_ID',
            'date'              => 'INVOICE_DATE',
            'due_date'          => 'INVOICE_DUE',
            'discount_rate'     => 'DISCOUNT_APPLIED',
            'tax_type'          => '',
            'tax_rate'          => 'TAX_RATE',
            'sub_total'         => 'SUB_TOTAL',
            'discount_amount'   => 'DISCOUNT',
            'net_amount'        => '',
            'tax_amount'        => 'TAX',
            'gross_amount'      => 'INVOICE_AMOUNT',
            'paid_amount'       => 'PAID_AMOUNT',
            'balance'           => 'BALANCE',
            'open_date'         => 'INVOICE_DATE',
            'close_date'        => 'PAID_DATE',
            'last_active'       => 'PAID_DATE',
            'status'            => '',
            'is_closed'         => 'INVOICE_PAID',
            'paid_date'         => 'PAID_DATE'     
            );
        $this->migrateTable($qwcrm_prefix.'invoice', $myitcrm_prefix.'TABLE_INVOICE', $column_mappings);

        // Change tax_type to selected Company Tax Type for all migrated invoices - This is an assumption
        $this->updateColumnValues($qwcrm_prefix.'invoice', 'tax_type', '', $this->getCompanyDetails('tax_type'));

        // change close dates from zero to ''
        $this->updateColumnValues($qwcrm_prefix.'invoice', 'close_date', '0', '');
        $this->updateColumnValues($qwcrm_prefix.'invoice', 'paid_date', '0', '');
        $this->updateColumnValues($qwcrm_prefix.'invoice', 'last_active', '0', '');

        // correct null workorders
        $this->updateColumnValues($qwcrm_prefix.'invoice', 'workorder_id', '0', '');

        // invoice_labour
        $column_mappings = array(
            'invoice_labour_id' => 'INVOICE_LABOR_ID',
            'invoice_id'        => 'INVOICE_ID',
            'description'       => 'INVOICE_LABOR_DESCRIPTION',
            'amount'            => 'INVOICE_LABOR_RATE',
            'qty'               => 'INVOICE_LABOR_UNIT',
            'sub_total'         => 'INVOICE_LABOR_SUBTOTAL'    
            );
        $this->migrateTable($qwcrm_prefix.'invoice_labour', $myitcrm_prefix.'TABLE_INVOICE_LABOR', $column_mappings);

        // invoice_parts
        $column_mappings = array(
            'invoice_parts_id'  => 'INVOICE_PARTS_ID',
            'invoice_id'        => 'INVOICE_ID',
            'description'       => 'INVOICE_PARTS_DESCRIPTION',
            'amount'            => 'INVOICE_PARTS_AMOUNT',
            'qty'               => 'INVOICE_PARTS_COUNT',
            'sub_total'         => 'INVOICE_PARTS_SUBTOTAL'    
            );
        $this->migrateTable($qwcrm_prefix.'invoice_parts', $myitcrm_prefix.'TABLE_INVOICE_PARTS', $column_mappings);        

        /* Payment / transactions */

        // payment_transactions
        $column_mappings = array(
            'transaction_id'    => 'TRANSACTION_ID',
            'employee_id'       => '',
            'customer_id'       => 'CUSTOMER_ID',
            'workorder_id'      => 'WORKORDER_ID',
            'invoice_id'        => 'INVOICE_ID',
            'date'              => 'DATE',
            'method'            => 'TYPE',
            'amount'            => 'AMOUNT',
            'note'              => 'MEMO'  
            );
        $this->migrateTable($qwcrm_prefix.'payment_transactions', $myitcrm_prefix.'TABLE_TRANSACTION', $column_mappings);

        // update payment types
        $this->updateColumnValues($qwcrm_prefix.'payment_transactions', 'method', '1', 'credit_card');
        $this->updateColumnValues($qwcrm_prefix.'payment_transactions', 'method', '2', 'cheque');
        $this->updateColumnValues($qwcrm_prefix.'payment_transactions', 'method', '3', 'cash');
        $this->updateColumnValues($qwcrm_prefix.'payment_transactions', 'method', '4', 'gift_certificate');
        $this->updateColumnValues($qwcrm_prefix.'payment_transactions', 'method', '5', 'paypal');    

        /* Refund */

        // refund
        $column_mappings = array(
            'refund_id'         => 'REFUND_ID',
            'payee'             => 'REFUND_PAYEE',
            'date'              => 'REFUND_DATE',
            'type'              => 'REFUND_TYPE',
            'payment_method'    => 'REFUND_PAYMENT_METHOD',
            'net_amount'        => 'REFUND_NET_AMOUNT',
            'vat_rate'          => 'REFUND_TAX_RATE',
            'vat_amount'        => 'REFUND_TAX_AMOUNT',
            'gross_amount'      => 'REFUND_GROSS_AMOUNT',
            'items'             => 'REFUND_ITEMS',
            'notes'             => 'REFUND_NOTES'        
            );
        $this->migrateTable($qwcrm_prefix.'refund', $myitcrm_prefix.'TABLE_REFUND', $column_mappings);

        // update refund types
        $this->updateColumnValues($qwcrm_prefix.'refund', 'type', '1', 'credit_note');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'type', '2', 'proxy_invoice');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'type', '3', 'returned_goods');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'type', '4', 'returned_services');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'type', '5', 'other');

        // update refund payment methods
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '1', 'bank_transfer');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '2', 'card');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '3', 'cash');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '4', 'cheque');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '5', 'credit');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '6', 'direct_debit');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '7', 'gift_certificate');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '8', 'google_checkout');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '9', 'paypal');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '10', 'voucher');
        $this->updateColumnValues($qwcrm_prefix.'refund', 'payment_method', '11', 'other');    

        /* Schedule */

        // schedule
        $column_mappings = array(
            'schedule_id'       => 'SCHEDULE_ID',
            'employee_id'       => 'EMPLOYEE_ID',
            'customer_id'       => '',
            'workorder_id'      => 'WORK_ORDER_ID',
            'start_time'        => 'SCHEDULE_START',
            'end_time'          => 'SCHEDULE_END',
            'notes'             => 'SCHEDULE_NOTES'    
            );
        $this->migrateTable($qwcrm_prefix.'schedule', $myitcrm_prefix.'TABLE_SCHEDULE', $column_mappings);

        /* Supplier */

        // supplier
        $column_mappings = array(
            'supplier_id'       => 'SUPPLIER_ID',
            'display_name'      => 'SUPPLIER_NAME',
            'first_name'        => '',
            'last_name'         => 'SUPPLIER_CONTACT',
            'website'           => 'SUPPLIER_WWW',
            'email'             => 'SUPPLIER_EMAIL',
            'type'              => 'SUPPLIER_TYPE',
            'primary_phone'     => 'SUPPLIER_PHONE',
            'mobile_phone'      => 'SUPPLIER_MOBILE',
            'fax'               => 'SUPPLIER_FAX',
            'address'           => 'SUPPLIER_ADDRESS',
            'city'              => 'SUPPLIER_CITY',
            'state'             => 'SUPPLIER_STATE',
            'zip'               => 'SUPPLIER_ZIP',
            'country'           => '',
            'description'       => 'SUPPLIER_DESCRIPTION',
            'notes'             => 'SUPPLIER_NOTES'           
            );
        $this->migrateTable($qwcrm_prefix.'supplier', $myitcrm_prefix.'TABLE_SUPPLIER', $column_mappings);

        // update supplier types
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '1', 'affiliate_marketing');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '2', 'advertising');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '3', 'drop_shipping');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '4', 'courier');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '5', 'general');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '6', 'parts');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '7', 'services');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '8', 'software');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '9', 'wholesale');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '10', 'online');
        $this->updateColumnValues($qwcrm_prefix.'supplier', 'type', '11', 'other');

        /* user / Employee */

        // user
        $column_mappings = array(
            'user_id'           => 'EMPLOYEE_ID',
            'customer_id'       => '',
            'username'          => 'EMPLOYEE_LOGIN',
            'password'          => 'EMPLOYEE_PASSWD',
            'email'             => 'EMPLOYEE_EMAIL',
            'usergroup'         => 'EMPLOYEE_TYPE',
            'active'            => 'EMPLOYEE_STATUS',
            'last_active'       => '',
            'register_date'     => '',
            'require_reset'     => '',
            'last_reset_time'   => '',
            'reset_count'       => '',
            'is_employee'       => '',
            'display_name'      => 'EMPLOYEE_DISPLAY_NAME',
            'first_name'        => 'EMPLOYEE_FIRST_NAME',
            'last_name'         => 'EMPLOYEE_LAST_NAME',
            'work_primary_phone'=> 'EMPLOYEE_WORK_PHONE',
            'work_mobile_phone' => 'EMPLOYEE_MOBILE_PHONE',
            'work_fax'          => '',
            'home_primary_phone'=> 'EMPLOYEE_HOME_PHONE',
            'home_mobile_phone' => '',
            'home_email'        => '',
            'home_address'      => 'EMPLOYEE_ADDRESS',
            'home_city'         => 'EMPLOYEE_CITY',
            'home_state'        => 'EMPLOYEE_STATE',
            'home_zip'          => 'EMPLOYEE_ZIP',
            'home_country'      => '',
            'based'             => 'EMPLOYEE_BASED',
            'notes'             => ''
            );
        $this->migrateTable($qwcrm_prefix.'user', $myitcrm_prefix.'TABLE_EMPLOYEE', $column_mappings);

        // Set all users to have create date of now 
        $this->updateColumnValues($qwcrm_prefix.'user', 'register_date', '*', time());

        // Set all users to employees
        $this->updateColumnValues($qwcrm_prefix.'user', 'is_employee', '*', '1');

        // Set all users to technicians
        $this->updateColumnValues($qwcrm_prefix.'user', 'usergroup', '*', '4');

        // Set password reset required for all users
        $this->updateColumnValues($qwcrm_prefix.'user', 'require_reset', '*', '1');

        // Reset all user passwords (passwords will all be random and unknown)
        $this->resetAllUserPasswords();

        /* Workorder */

        // workorder
        $column_mappings = array(
            'workorder_id'      => 'WORK_ORDER_ID',
            'employee_id'       => 'WORK_ORDER_ASSIGN_TO',
            'customer_id'       => 'CUSTOMER_ID',
            'invoice_id'        => '',
            'created_by'        => 'WORK_ORDER_CREATE_BY',
            'closed_by'         => 'WORK_ORDER_CLOSE_BY',
            'open_date'         => 'WORK_ORDER_OPEN_DATE',
            'close_date'        => 'WORK_ORDER_CLOSE_DATE',
            'last_active'       => 'LAST_ACTIVE',
            'status'            => '',
            'is_closed'         => '',
            'scope'             => 'WORK_ORDER_SCOPE',
            'description'       => 'WORK_ORDER_DESCRIPTION',
            'comments'          => 'WORK_ORDER_COMMENT',
            'resolution'        => 'WORK_ORDER_RESOLUTION'           
            );   // WORK_ORDER_CURRENT_STATUS - WORK_ORDER_STATUS    
        $this->migrateTable($qwcrm_prefix.'workorder', $myitcrm_prefix.'TABLE_WORK_ORDER', $column_mappings);

        // workorder_history
        $column_mappings = array(
            'history_id'        => 'WORK_ORDER_STATUS_ID',
            'employee_id'       => 'WORK_ORDER_STATUS_ENTER_BY',
            'workorder_id'      => 'WORK_ORDER_ID',
            'date'              => 'WORK_ORDER_STATUS_DATE',
            'note'              => 'WORK_ORDER_STATUS_NOTES'         
            ); 
        $this->migrateTable($qwcrm_prefix.'workorder_history', $myitcrm_prefix.'TABLE_WORK_ORDER_STATUS', $column_mappings);    

        // workorder_notes
        $column_mappings = array(
            'workorder_note_id' => 'WORK_ORDER_NOTES_ID',
            'employee_id'       => 'WORK_ORDER_NOTES_ENTER_BY',
            'workorder_id'      => 'WORK_ORDER_ID',
            'date'              => 'WORK_ORDER_NOTES_DATE',
            'description'       => 'WORK_ORDER_NOTES_DESCRIPTION'         
            ); 
        $this->migrateTable($qwcrm_prefix.'workorder_notes', $myitcrm_prefix.'TABLE_WORK_ORDER_NOTES', $column_mappings);

        /* Corrections */

        // Workorder
        $this->databaseCorrectionWorkorder($qwcrm_prefix, $myitcrm_prefix);

        // Invoice
        $this->databaseCorrectionInvoice($qwcrm_prefix);

        // Giftcert
        $this->databaseCorrectionGiftcert($qwcrm_prefix);

        // Schedule
        $this->databaseCorrectionSchedule($qwcrm_prefix, $myitcrm_prefix);

        // User
        $this->databaseCorrectionUser($qwcrm_prefix);

        /* Final stuff */

        // Final statement
        if(self::$setup_error_flag) {

            // Setup error flag uses in smarty templates
            $this->app->smarty->assign('setup_error_flag', true);

            // Log message
            $record = _gettext("The database migration process failed, check the logs.");

            // Output message via smarty
            self::$executed_sql_results .= '<div>&nbsp;</div>';
            self::$executed_sql_results .= '<div style="color: red;"><strong>'.$record.'</strong></div>';

            // Log message to setup log        
            $this->writeRecordToSetupLog('migrate', $record);

        } else {

            // Log message
            $record = _gettext("The database migration process was successful.");

            // Output message via smarty
            self::$executed_sql_results .= '<div>&nbsp;</div>';
            self::$executed_sql_results .= '<div style="color: green;"><strong>'.$record.'</strong></div>';

            // Log message to setup log        
            $this->writeRecordToSetupLog('migrate', $record);

        } 

        // return reflecting the installation status
        if(self::$setup_error_flag) {

            /* Migration Failed */

            // Set setup_error_flag used in smarty templates
            $this->app->smarty->assign('setup_error_flag', true);        

            return false;

        } else {

            /* migration Successful */

            return true;

        }

    }

    /** Database Corrections **/

    ############################################
    #   Correct migrated workorder data        #
    ############################################

    public function databaseCorrectionWorkorder($qwcrm_prefix, $myitcrm_prefix) {

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message
        $record = _gettext("Starting the correction of the migrated `workorder` data in QWcrm.");       

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        // old MyITCRM workorder status
        // 1 - created
        // 2 - assigned
        // 3 - waiting for parts
        // n/a
        // n/a
        // 6 - closed
        // 7 - awaiting payment
        // 8 - payment made
        // 9 - pending
        // 10 - open

        $sql =  "SELECT            
                ".$qwcrm_prefix."workorder.workorder_id AS qw_workorder_id,

                ".$myitcrm_prefix."TABLE_WORK_ORDER.WORK_ORDER_ID AS my_work_order_id,
                ".$myitcrm_prefix."TABLE_WORK_ORDER.WORK_ORDER_STATUS AS my_work_order_status,
                ".$myitcrm_prefix."TABLE_WORK_ORDER.WORK_ORDER_CURRENT_STATUS AS my_work_order_current_status,

                ".$myitcrm_prefix."TABLE_INVOICE.INVOICE_ID AS my_invoice_id            

                FROM ".$qwcrm_prefix."workorder
                LEFT JOIN ".$myitcrm_prefix."TABLE_WORK_ORDER ON ".$qwcrm_prefix."workorder.workorder_id = ".$myitcrm_prefix."TABLE_WORK_ORDER.WORK_ORDER_ID
                LEFT JOIN ".$myitcrm_prefix."TABLE_INVOICE ON ".$myitcrm_prefix."TABLE_WORK_ORDER.WORK_ORDER_ID = ".$myitcrm_prefix."TABLE_INVOICE.WORKORDER_ID";

        /* Processs the records */

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        while(!$rs->EOF) {            

            $myitcrm_record = $rs->GetRowAssoc(); 

            /* status and is_closed */

            // WORK_ORDER_STATUS = 6 (closed), WORK_ORDER_CURRENT_STATUS = 6 (closed)
            if($myitcrm_record['my_work_order_status'] == '6' && $myitcrm_record['my_work_order_current_status'] == '6') {                    
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'status', 'closed_without_invoice', 'workorder_id', $myitcrm_record['qw_workorder_id']);
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'is_closed', '1', 'workorder_id', $myitcrm_record['qw_workorder_id']);
            }

            // WORK_ORDER_STATUS = 6 (closed), WORK_ORDER_CURRENT_STATUS = 8 (payment made)
            elseif($myitcrm_record['my_work_order_status'] == '6' && $myitcrm_record['my_work_order_current_status'] == '8') {                    
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'status', 'closed_with_invoice', 'workorder_id', $myitcrm_record['qw_workorder_id']);
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'is_closed', '1', 'workorder_id', $myitcrm_record['qw_workorder_id']);
            }

            // WORK_ORDER_STATUS = 9 (pending), WORK_ORDER_CURRENT_STATUS = 7 (awaiting payment)
            elseif($myitcrm_record['my_work_order_status'] == '9' && $myitcrm_record['my_work_order_current_status'] == '7') {                    
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'status', 'closed_with_invoice', 'workorder_id', $myitcrm_record['qw_workorder_id']);
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'is_closed', '1', 'workorder_id', $myitcrm_record['qw_workorder_id']);
            }

            // WORK_ORDER_STATUS = 10 (open), WORK_ORDER_CURRENT_STATUS = 1 (created)
            elseif($myitcrm_record['my_work_order_status'] == '10' && $myitcrm_record['my_work_order_current_status'] == '1') {                    
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'status', 'unassigned', 'workorder_id', $myitcrm_record['qw_workorder_id']);
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'is_closed', '0', 'workorder_id', $myitcrm_record['qw_workorder_id']);
            }

            // WORK_ORDER_STATUS = 10 (open), WORK_ORDER_CURRENT_STATUS = 2 (assigned)
            elseif($myitcrm_record['my_work_order_status'] == '10' && $myitcrm_record['my_work_order_current_status'] == '2') {                    
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'status', 'assigned', 'workorder_id', $myitcrm_record['qw_workorder_id']);
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'is_closed', '0', 'workorder_id', $myitcrm_record['qw_workorder_id']);
            }

            // Uncaught records / default
            else {                    
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'status', 'failed_to_migrate', 'workorder_id', $myitcrm_record['qw_workorder_id']);
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'is_closed', '0', 'workorder_id', $myitcrm_record['qw_workorder_id']);
            }

            /* invoice_id */

            if($myitcrm_record['my_invoice_id'] != '') {
                $this->updateRecordValue($qwcrm_prefix.'workorder', 'invoice_id', $myitcrm_record['my_invoice_id'], 'workorder_id', $myitcrm_record['qw_workorder_id']);                
            }

            // Advance the INSERT loop to the next record
            $rs->MoveNext();           

        }//EOF While loop

        /* Final Stuff */

        // Log message
        $record = _gettext("Finished the correction of the migrated `workorder` data in QWcrm."); 

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        return;

    }

    ############################################
    #   Correct migrated invoice data          #
    ############################################

    public function databaseCorrectionInvoice($qwcrm_prefix) {

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message
        $record = _gettext("Starting the correction of the migrated `invoice` data in QWcrm.");       

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        $sql =  "SELECT * FROM ".$qwcrm_prefix."invoice";                       

        /* Processs the records */

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        while(!$rs->EOF) {            

            $qwcrm_record = $rs->GetRowAssoc();

            /* net_amount */
            $net_amount = $qwcrm_record['sub_total'] - $qwcrm_record['discount_amount'];
            $this->updateRecordValue($qwcrm_prefix.'invoice', 'net_amount', $net_amount, 'invoice_id', $qwcrm_record['invoice_id']);            

            /* status and is_closed*/

            // no amount on invoice
            if($qwcrm_record['gross_amount'] == '0') {                    
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'status', 'pending', 'invoice_id', $qwcrm_record['invoice_id']);
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'is_closed', '0', 'invoice_id', $qwcrm_record['invoice_id']); 
            }

            // if unpaid
            elseif($qwcrm_record['paid_amount'] == '0') {                    
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'status', 'unpaid', 'invoice_id', $qwcrm_record['invoice_id']);
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'is_closed', '0', 'invoice_id', $qwcrm_record['invoice_id']);
            }

            // if there are partial payments
            elseif($qwcrm_record['paid_amount'] < $qwcrm_record['gross_amount'] && $qwcrm_record['paid_amount'] != '0') {                    
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'status', 'partially_paid', 'invoice_id', $qwcrm_record['invoice_id']);
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'is_closed', '0', 'invoice_id', $qwcrm_record['invoice_id']);
            }

            // if fully paid
            elseif($qwcrm_record['paid_amount'] == $qwcrm_record['gross_amount']) {                    
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'status', 'paid', 'invoice_id', $qwcrm_record['invoice_id']);
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'is_closed', '1', 'invoice_id', $qwcrm_record['invoice_id']);
            }            

            // Uncaught records / default
            else {                    
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'status', 'failed_to_migrate', 'invoice_id', $qwcrm_record['invoice_id']);
                $this->updateRecordValue($qwcrm_prefix.'invoice', 'is_closed', '0', 'invoice_id', $qwcrm_record['invoice_id']);
            }

            // Advance the INSERT loop to the next record
            $rs->MoveNext();           

        }//EOF While loop

        /* Final Stuff */

        // Log message
        $record = _gettext("Finished the correction of the migrated `invoice` data in QWcrm."); 

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        return;

    }

    ############################################
    #   Correct migrated giftcert data         #
    ############################################

    public function databaseCorrectionGiftcert($qwcrm_prefix) {

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message
        $record = _gettext("Starting the correction of the migrated `giftcert` data in QWcrm.");       

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        $sql =  "SELECT * FROM ".$qwcrm_prefix."giftcert";                       

        /* Processs the records */

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        while(!$rs->EOF) {            

            $qwcrm_record = $rs->GetRowAssoc();

            /* is_redeemed */

            // no amount on invoice
            if($qwcrm_record['date_redeemed'] == '') {                    
                $this->updateRecordValue($qwcrm_prefix.'giftcert', 'is_redeemed', '0', 'giftcert_id', $qwcrm_record['giftcert_id']);                               
            } else {
                $this->updateRecordValue($qwcrm_prefix.'giftcert', 'is_redeemed', '1', 'giftcert_id', $qwcrm_record['giftcert_id']);
            }

            // Advance the INSERT loop to the next record
            $rs->MoveNext();           

        }//EOF While loop

        /* Final Stuff */

        // Log message
        $record = _gettext("Finished the correction of the migrated `giftcert` data in QWcrm."); 

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        return;

    }

    ############################################
    #   Correct migrated schedule data         #
    ############################################

    public function databaseCorrectionSchedule($qwcrm_prefix, $myitcrm_prefix) {

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message
        $record = _gettext("Starting the correction of the migrated `schedule` data in QWcrm.");       

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        $sql =  "SELECT            
                ".$qwcrm_prefix."schedule.schedule_id AS qw_schedule_id,

                ".$myitcrm_prefix."TABLE_SCHEDULE.SCHEDULE_ID AS my_schedule_id,
                ".$myitcrm_prefix."TABLE_SCHEDULE.WORK_ORDER_ID AS my_work_order_id,

                ".$myitcrm_prefix."TABLE_WORK_ORDER.CUSTOMER_ID AS my_customer_id

                FROM ".$qwcrm_prefix."schedule
                LEFT JOIN ".$myitcrm_prefix."TABLE_SCHEDULE ON ".$qwcrm_prefix."schedule.schedule_id = ".$myitcrm_prefix."TABLE_SCHEDULE.SCHEDULE_ID  
                LEFT JOIN ".$myitcrm_prefix."TABLE_WORK_ORDER ON ".$myitcrm_prefix."TABLE_SCHEDULE.WORK_ORDER_ID = ".$myitcrm_prefix."TABLE_WORK_ORDER.WORK_ORDER_ID";

        /* Processs the records */

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        while(!$rs->EOF) {            

            $myitcrm_record = $rs->GetRowAssoc(); 

            /* customer_id */
            $this->updateRecordValue($qwcrm_prefix.'schedule', 'customer_id', $myitcrm_record['my_customer_id'], 'schedule_id', $myitcrm_record['qw_schedule_id']);

            // Advance the INSERT loop to the next record
            $rs->MoveNext();           

        }//EOF While loop

        /* Final Stuff */

        // Log message
        $record = _gettext("Finished the correction of the migrated `schedule` data in QWcrm."); 

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        return;

    }

    ############################################
    #   Correct migrated user data             #
    ############################################

    public function databaseCorrectionUser($qwcrm_prefix) {

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message
        $record = _gettext("Starting the correction of the migrated `user` data in QWcrm.");       

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        $sql = "SELECT * FROM ".$qwcrm_prefix."user";

        /* Processs the records */

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        while(!$rs->EOF) {            

            $qwcrm_record = $rs->GetRowAssoc(); 

            // Sanitise user's usernames - remove all spaces
            $this->updateRecordValue($qwcrm_prefix.'user', 'username', str_replace(' ', '.', $qwcrm_record['username']), 'user_id', $qwcrm_record['user_id']);            

            // Advance the INSERT loop to the next record
            $rs->MoveNext();           

        }//EOF While loop

        /* Final Stuff */

        // Log message
        $record = _gettext("Finished the correction of the migrated `user` data in QWcrm."); 

        // Result message
        self::$executed_sql_results .= '<div><strong><span style="color: green">'.$record.'</span></strong></div>';

        // Add division to seperate table migration function results
        self::$executed_sql_results .= '<div>&nbsp;</div>';

        // Log message to setup log                
        $this->writeRecordToSetupLog('migrate', $record);

        return;

    }
    

    /** Other Functions **/
    
    #########################################################
    #   check myitcrm database is accessible and is 2.9.3   #
    #########################################################

    public function checkMyitcrmDatabaseConnection($myitcrm_prefix) {

        $sql = "SELECT VERSION_ID FROM ".$myitcrm_prefix."VERSION WHERE VERSION_ID = '293'";

        if(!$rs = $this->app->db->execute($sql)) {        

            // output message failed to connect to the myitcrm database
            return false;

        } else {

            if($rs->RecordCount() != 1) {

                // output error message - database is not 293
                return false;

            } else {

                // myitcrm database is sutiable for migration
                return true;

            }

        }

    }
    
    #####################################
    #    Reset all user's passwords     #   // database structure is different in 3.0.1
    #####################################

    public function resetAllUserPasswords() { 

        $sql = "SELECT user_id FROM ".PRFX."user";

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        // Loop through all users
        while(!$rs->EOF) { 

            // Reset User's password
            $this->resetUserPassword($rs->fields['user_id']);

            // Advance the INSERT loop to the next record            
            $rs->MoveNext();            

        }

        // Log activity        
        $this->writeRecordToSetupLog('migrate', _gettext("All User Account passwords have been reset."));            

        return;
        
    }

    #####################################
    #    Reset a user's password        #    
    #####################################

    public function resetUserPassword($user_id, $password = null) { 

        // if no password supplied generate a random one
        if($password == null) { $password = \Joomla\CMS\User\UserHelper::genRandomPassword(16); }

        $sql = "UPDATE ".PRFX."user SET
                password        =". $this->app->db->qstr( \Joomla\CMS\User\UserHelper::hashPassword($password) ).",
                require_reset   =". $this->app->db->qstr( 0                                    ).",   
                last_reset_time =". $this->app->db->qstr( time()                               ).",
                reset_count     =". $this->app->db->qstr( 0                                    )."
                WHERE user_id   =". $this->app->db->qstr( $user_id                             );

        if(!$this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        // Log activity 
        // n/a

        return;  

    }

    #################################################
    #    Check if username already exists           #
    #################################################

    public function checkUsernameExists($username, $current_username = null) {

        // This prevents self-checking of the current username of the record being edited
        if ($current_username != null && $username === $current_username) {return false;}

        $sql = "SELECT username FROM ".PRFX."user WHERE username =". $this->app->db->qstr($username);

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        $result_count = $rs->RecordCount();

        if($result_count >= 1) {

            $this->app->smarty->assign('msg_danger', _gettext("The Username")." `".$username."` "._gettext("already exists! Please use a different one."));

            return true;

        } else {

            return false;

        }        
        
    } 
    
    ######################################################
    #  Check if an email address has already been used   #
    ######################################################

    public function checkUserEmailExists($email, $current_email = null) {

        // This prevents self-checking of the current username of the record being edited
        if ($current_email != null && $email === $current_email) {return false;}

        $sql = "SELECT email FROM ".PRFX."user WHERE email =". $this->app->db->qstr($email);

        if(!$rs = $this->app->db->execute($sql)) {$this->app->system->page->forceErrorPage('database', __FILE__, __FUNCTION__, $this->app->db->ErrorMsg(), $sql);}

        $result_count = $rs->RecordCount();

        if($result_count >= 1) {

            $this->app->smarty->assign('msg_danger', _gettext("The email address has already been used. Please use a different one."));

            return true;

        } else {

            return false;

        }        

    }

    ##########################
    #  Delete Company Logo   #
    ##########################

    public function deleteLogo() {

        // Only delete a logo if there is one set
        if($this->getCompanyDetails('logo')) {

            // Build the full logo file path
            $logo_file = parse_url(MEDIA_DIR . $this->getCompanyDetails('logo'), PHP_URL_PATH);

            // Perform the deletion
            unlink($logo_file);

        }

    }

    ##########################
    #  Upload Company Logo   #
    ##########################

    public function uploadLogo() {

        // Logo - Only process if there is an image uploaded
        if($_FILES['logo']['size'] > 0) {

            // Allowed extensions
            $allowedExts = array('png', 'jpg', 'jpeg', 'gif');

            // Get file extension
            $filename_info = pathinfo($_FILES['logo']['name']);
            $extension = $filename_info['extension'];

            // Rename Logo Filename to logo.xxx (keeps original image extension)
            $new_logo_filename = 'logo.' . $extension;       

            // Validate the uploaded file is allowed (extension, mime type, 0 - 2mb)
            if ((($_FILES['logo']['type'] == 'image/gif')
                    || ($_FILES['logo']['type'] == 'image/jpeg')
                    || ($_FILES['logo']['type'] == 'image/jpg')
                    || ($_FILES['logo']['type'] == 'image/pjpeg')
                    || ($_FILES['logo']['type'] == 'image/x-png')
                    || ($_FILES['logo']['type'] == 'image/png'))
                    && ($_FILES['logo']['size'] < 2048000)
                    && in_array($extension, $allowedExts)) {

                // Check for file submission errors and echo them
                if ($_FILES['logo']['error'] > 0 ) {
                    echo _gettext("Return Code").': ' . $_FILES['logo']['error'] . '<br />';                

                // If no errors then move the file from the PHP temporary storage to the logo location
                } else {
                    move_uploaded_file($_FILES['logo']['tmp_name'], MEDIA_DIR . $new_logo_filename);              
                }

                // return the filename with a random query to allow for caching issues
                return $new_logo_filename . '?' . strtolower(\Joomla\CMS\User\UserHelper::genRandomPassword(3));

            // If file is invalid then load the error page  
            } else {

                /*
                echo "Upload: "    . $_FILES['company_logo']['name']           . '<br />';
                echo "Type: "      . $_FILES['company_logo']['type']           . '<br />';
                echo "Size: "      . ($_FILES['company_logo']['size'] / 1024)  . ' Kb<br />';
                echo "Temp file: " . $_FILES['company_logo']['tmp_name']       . '<br />';
                echo "Stored in: " . MEDIA_DIR . $_FILES['file']['name']       ;
                 */   

                $this->app->system->page->forceErrorPage('file', __FILE__, __FUNCTION__, '', '', _gettext("Failed to update logo because the submitted file was invalid."));

            }

        }

    }   

}
