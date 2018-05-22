<?php
/**
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
 */

defined('_QWEXEC') or die;

require(INCLUDES_DIR.'components/customer.php');
require(INCLUDES_DIR.'components/invoice.php'); // require to stop email sub-system error
require(INCLUDES_DIR.'components/user.php');
require(INCLUDES_DIR.'components/workorder.php');

// Delete any expired resets (CRON is better)
delete_expired_reset_codes($db);



// STAGE 1 -  Enter email
if(!isset($VAR['submit']) && !isset($VAR['email']) && !isset($VAR['token']) && !isset($VAR['password']) && !isset($VAR['confirmPassword'])) {
    
    // Load the enter_email page
    $stage = 'enter_email';
    


// STAGE 2 - Email submitted for account reset
} elseif(isset($VAR['submit']) && isset($VAR['email']) && $VAR['email'] != '') {
    
    // Prevent direct access to this page
    if(!check_page_accessed_via_qwcrm('user', 'reset')) {
        die(_gettext("No Direct Access Allowed."));
    }   

    // if recaptcha is disabled || recaptcha is enabled and passes authentication
    if( !$QConfig->recaptcha || ($QConfig->recaptcha && authenticate_recaptcha($QConfig->recaptcha_secret_key, $VAR['g-recaptcha-response']))) {
        
            /* Allowed to submit */
            
            // make sure user account exists and is not blocked
            if(!$VAR['user_id'] = validate_reset_email($db, $VAR['email'])) {
                
                // Display error message
                $smarty->assign('warning_msg', _gettext("You cannot reset the password on this account. It either does not exist or is blocked."));
                               
                // Reload the enter_email page
                $stage = 'enter_email';

            // The account is valid and allowed to be reset
            } else {
                
                // update reset count for the user
                update_user_reset_count($db, $VAR['user_id']);
                
                // build the email and send it
                send_reset_email($db, $VAR['user_id']);
                    
                // Load the enter_token page            
                $stage = 'enter_token';                
                
            }
            
    } else {
            
        /* Failed reCAPTCHA  */         

        // Load the enter_email page with reCAPTCHA warning message (already generated by authenticate_recaptcha())       
        $stage = 'enter_email';

    }


        
// STAGE 3 - Enter Token
} elseif(!isset($VAR['submit']) && isset($VAR['token']) && $VAR['token'] != '') {    
    
    // Load the enter_token page
    $smarty->assign('token', $VAR['token']);
    $stage = 'enter_token';

    
    
// STAGE 4 - Token has been submitted
} elseif(isset($VAR['submit']) && isset($VAR['token']) && $VAR['token'] != '') {
    
    // Prevent direct access to this page
    if(!check_page_accessed_via_qwcrm('user', 'reset')) {
        die(_gettext("No Direct Access Allowed."));
    }
    
    // if recaptcha is disabled || recaptcha is enabled and passes authentication
    if( !$QConfig->recaptcha || ($QConfig->recaptcha && authenticate_recaptcha($QConfig->recaptcha_secret_key, $VAR['g-recaptcha-response']))) {
        
        /* Allowed to submit */
        
        // Process the token and reset the password for the account - function sets response messages
        if(validate_reset_token($db, $VAR['token'])) {

            // Authorise the actual password change and return the secret code
            $reset_code = authorise_password_reset($db, $VAR['token']);

            // Load the enter_password page         
            $smarty->assign('reset_code', $reset_code);
            $stage = 'enter_password';

        } else {
            
            // Reload the enter_token page        
            $smarty->assign('token', $VAR['token']);
            $stage = 'enter_token';

        }

    } else {

        /* Failed reCAPTCHA  */

        // Load the enter_token page with reCAPTCHA warning message (already generated by authenticate_recaptcha())
        $stage = 'enter_token';

    }
    
    
    
// STAGE 5 - Password has been submitted
} elseif(isset($VAR['submit']) && isset($VAR['reset_code']) && $VAR['reset_code'] != '' && isset($VAR['password']) && $VAR['password'] != '') {
    
    // Prevent direct access to this page
    if(!check_page_accessed_via_qwcrm('user', 'reset')) {
        die(_gettext("No Direct Access Allowed."));
    }
    
    // validate the reset code
    if(!validate_reset_code($db, $VAR['reset_code'])) {
        
        // delete reset code for this user
        delete_user_reset_code($db, $VAR['user_id']);
        
        // Display an error message
        $smarty->assign('warning_msg', _gettext("The submitted reset code was invalid."));
        
        // Load the enter_email page
        $stage = 'enter_email';
        
    } else {
        
        // Get the user_id by the reset_code
        $VAR['user_id'] = get_user_id_by_reset_code($db, $VAR['reset_code']);

        // Delete reset_code for this user
        delete_user_reset_code($db, $VAR['user_id']);
        
        // Reset the password
        reset_user_password($db, $VAR['user_id'], $VAR['password']);

        // Logout the user out silently (if logged in)
        logout(true);

        // Redirect to login page with success or failed mess
        force_page('user', 'login', 'information_msg='._gettext("Password reset successfully."));
        exit;
        
    }
    
    
    
// Fallback    
} else {
    
    // do nothing
    
}



// set recaptcha values if enabled
$smarty->assign('recaptcha', $QConfig->recaptcha);
$smarty->assign('recaptcha_site_key', $QConfig->recaptcha_site_key);

// Select the correct stage of reset to load
$smarty->assign('stage', $stage);       

// Build the page
$BuildPage .= $smarty->fetch('user/reset.tpl');


   


