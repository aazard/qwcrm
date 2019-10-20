<?php
/**
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
 */

defined('_QWEXEC') or die;

require(INCLUDES_DIR.'company.php');

// Prevent undefined variable errors
\QFactory::$VAR['delete_logo'] = isset(\QFactory::$VAR['delete_logo']) ? \QFactory::$VAR['delete_logo'] : null;

// Update Company details
if(isset(\QFactory::$VAR['submit'])) {

    // Submit data to the database
    update_company_details(\QFactory::$VAR);    
    
    // Reload Company options and display a success message
    force_page('company', 'edit', 'information_msg='._gettext("Company details updated."));
    
}

// Build the page
$smarty->assign('date_formats', get_date_formats());
$smarty->assign('tax_systems', get_tax_systems() );
$smarty->assign('vat_tax_codes', get_vat_tax_codes(null, true) );
$smarty->assign('company_details', get_company_details() );

\QFactory::$BuildPage .= $smarty->fetch('company/edit.tpl');
