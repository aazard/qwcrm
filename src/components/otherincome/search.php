<?php
/**
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
 */

defined('_QWEXEC') or die;

require(INCLUDES_DIR.'otherincome.php');
require(INCLUDES_DIR.'payment.php');

// Prevent undefined variable errors
\QFactory::$VAR['page_no'] = isset(\QFactory::$VAR['page_no']) ? \QFactory::$VAR['page_no'] : null;
\QFactory::$VAR['search_category'] = isset(\QFactory::$VAR['search_category']) ? \QFactory::$VAR['search_category'] : null;
\QFactory::$VAR['search_term'] = isset(\QFactory::$VAR['search_term']) ? \QFactory::$VAR['search_term'] : null;
\QFactory::$VAR['filter_type'] = isset(\QFactory::$VAR['filter_type']) ? \QFactory::$VAR['filter_type'] : null;
\QFactory::$VAR['filter_status'] = isset(\QFactory::$VAR['filter_status']) ? \QFactory::$VAR['filter_status'] : null;

// If a search is submitted
if(isset(\QFactory::$VAR['submit'])) {
    
    // Log activity
    $record = _gettext("A search of otherincomes has been performed with the search term").' `'.\QFactory::$VAR['search_term'].'` '.'in the category'.' `'.\QFactory::$VAR['search_category'].'`.';
    write_record_to_activity_log($record);
    
    // Redirect search so the variables are in the URL
    unset(\QFactory::$VAR['submit']);
    force_page('otherincome', 'search', \QFactory::$VAR, 'get');
    
}

// Build the page
$smarty->assign('search_category',          \QFactory::$VAR['search_category']                                                                                         );
$smarty->assign('search_term',              \QFactory::$VAR['search_term']                                                                                             );
$smarty->assign('filter_type',              \QFactory::$VAR['filter_type']                                                                                             );
$smarty->assign('filter_status',            \QFactory::$VAR['filter_status']                                                                                             );
$smarty->assign('otherincome_statuses',     get_otherincome_statuses()                                                                                      );
$smarty->assign('otherincome_types',        get_otherincome_types()                                                                                         );
$smarty->assign('display_otherincomes',     display_otherincomes('otherincome_id', 'DESC', true, '25', \QFactory::$VAR['page_no'], \QFactory::$VAR['search_category'], \QFactory::$VAR['search_term'], \QFactory::$VAR['filter_type'], \QFactory::$VAR['filter_status']));
\QFactory::$BuildPage .= $smarty->fetch('otherincome/search.tpl');