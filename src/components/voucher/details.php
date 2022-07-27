<?php
/**
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
 */

defined('_QWEXEC') or die;

// Check if we have an voucher_id
if(!isset(\CMSApplication::$VAR['voucher_id']) || !\CMSApplication::$VAR['voucher_id']) {
    $this->app->system->variables->systemMessagesWrite('danger', _gettext("No Voucher ID supplied."));
    $this->app->system->page->forcePage('voucher', 'search');
}

$voucher_details = $this->app->components->voucher->getRecord(\CMSApplication::$VAR['voucher_id']);

// if the voucher is deleted return to the search page
if($voucher_details['status'] === 'deleted') {
    $this->app->system->variables->systemMessagesWrite('danger', _gettext("Voucher").' '.\CMSApplication::$VAR['voucher_id'].' '._gettext("has been deleted and can no longer be accessed."));
    $this->app->system->page->forcePage('voucher', 'search');
}

// Build the page
$this->app->smarty->assign('client_details',               $this->app->components->client->getRecord($voucher_details['client_id'])                          );
$this->app->smarty->assign('employee_display_name',        $this->app->components->user->getRecord($voucher_details['employee_id'], 'display_name')          );
$this->app->smarty->assign('voucher_statuses',            $this->app->components->voucher->getStatuses()                                                     );
$this->app->smarty->assign('voucher_types',               $this->app->components->voucher->getTypes()                                                     );
$this->app->smarty->assign('voucher_details',             $voucher_details                                                           );