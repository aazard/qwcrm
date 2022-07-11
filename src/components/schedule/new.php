<?php
/**
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
 */

defined('_QWEXEC') or die;

// Prevent undefined variable errors
//\CMSApplication::$VAR['qform']['note'] = \CMSApplication::$VAR['qform']['note'] ?? null; 

// Check if we have an employee_id
if(!isset(\CMSApplication::$VAR['employee_id']) || !\CMSApplication::$VAR['employee_id']) {
    $this->app->system->variables->systemMessagesWrite('danger', _gettext("No Employee ID supplied."));
    $this->app->system->page->forcePage('user', 'search');
}

// Check if we have a workorder_id
if(!isset(\CMSApplication::$VAR['workorder_id']) || !\CMSApplication::$VAR['workorder_id']) {
    $this->app->system->variables->systemMessagesWrite('danger', _gettext("No Workorder ID supplied."));
    $this->app->system->page->forcePage('workorder', 'search');
}

// Get client_id
\CMSApplication::$VAR['client_id'] =  $this->app->components->workorder->getRecord(\CMSApplication::$VAR['workorder_id'], 'client_id');

// If new schedule item submitted
if(isset(\CMSApplication::$VAR['submit'])) {    
    
    // Add missing Time variables to 'qform' (smarty workaround)
    \CMSApplication::$VAR['qform']['StartTime'] = \CMSApplication::$VAR['StartTime'];
    \CMSApplication::$VAR['qform']['EndTime'] = \CMSApplication::$VAR['EndTime'];
    
    // Add missing Time variables in DATETIME format
    \CMSApplication::$VAR['qform']['start_time'] = $this->app->system->general->smartytimeToOtherformat('datetime', \CMSApplication::$VAR['qform']['start_date'], \CMSApplication::$VAR['StartTime']['Time_Hour'], \CMSApplication::$VAR['StartTime']['Time_Minute'], '0', '24');
    \CMSApplication::$VAR['qform']['end_time']   = $this->app->system->general->smartytimeToOtherformat('datetime', \CMSApplication::$VAR['qform']['end_date'], \CMSApplication::$VAR['EndTime']['Time_Hour'], \CMSApplication::$VAR['EndTime']['Time_Minute'], '0', '24');
    
    /* This manually builds a 'Time' string
    \CMSApplication::$VAR['qform']['start_time'] = \CMSApplication::$VAR['StartTime']['Time_Hour'].":".\CMSApplication::$VAR['StartTime']['Time_Minute'];
    \CMSApplication::$VAR['qform']['end_time'] = \CMSApplication::$VAR['EndTime']['Time_Hour'].":".\CMSApplication::$VAR['EndTime']['Time_Minute'];*/
    
    // If insert fails send them an error and reload the page with submitted info or load the page with the schedule
    if (!$this->app->components->schedule->insertRecord(\CMSApplication::$VAR['qform'])) {        
                 
       $this->app->smarty->assign('schedule_details', \CMSApplication::$VAR['qform']);
            
    } else {       
            
        // Break up the date into segments in the correct format
        $start_year            = date('Y', $this->app->system->general->dateToTimestamp(\CMSApplication::$VAR['qform']['start_date'])  );
        $start_month           = date('m', $this->app->system->general->dateToTimestamp(\CMSApplication::$VAR['qform']['start_date'])  );
        $start_day             = date('d', $this->app->system->general->dateToTimestamp(\CMSApplication::$VAR['qform']['start_date'])  );
    
        // Load the schedule day with the newly submitted schedule item
        $this->app->system->variables->systemMessagesWrite('success', _gettext("Schedule Successfully Created"));
        $this->app->system->page->forcePage('schedule', 'day', 'start_year='.$start_year.'&start_month='.$start_month.'&start_day='.$start_day.'&employee_id='.\CMSApplication::$VAR['qform']['employee_id'].'&workorder_id='.\CMSApplication::$VAR['qform']['workorder_id']);
        
        // Load the updated schedule details page
        // $this->app->system->variables->systemMessagesWrite('success', gettext("Schedule Successfully Updated."));
        //$this->app->system->page->force_page('schedule', 'details&schedule_id='.\CMSApplication::$VAR['qform']['schedule_id']);
        
    }

// If new schedule form is intially loaded, load schedule item from the database and assign
} else {
    
    // Add 1 hour on to a start_time (if possible)
    $end_time = explode(':',\CMSApplication::$VAR['start_time']);
    $end_time[0] = ($end_time[0] + 1) > 23 ? 23 : ($end_time[0] + 1);
    $end_time = (string) $end_time[0].':'.(string)$end_time[1];
    
    // Build the page
    $schedule_details = array();
    $schedule_details['client_id']      = \CMSApplication::$VAR['client_id'];
    $schedule_details['employee_id']    = \CMSApplication::$VAR['employee_id'];
    $schedule_details['workorder_id']   = \CMSApplication::$VAR['workorder_id'];
    $schedule_details['start_date']     = $this->app->system->general->convertYearMonthDayToDate(\CMSApplication::$VAR['start_year'], \CMSApplication::$VAR['start_month'], \CMSApplication::$VAR['start_day']);
    $schedule_details['start_time']     = \CMSApplication::$VAR['start_time'];
    $schedule_details['end_date']       = $this->app->system->general->convertYearMonthDayToDate(\CMSApplication::$VAR['start_year'], \CMSApplication::$VAR['start_month'], \CMSApplication::$VAR['start_day']);
    $schedule_details['end_time']       = $end_time;
    $schedule_details['note']           = null;    
    $this->app->smarty->assign('schedule_details', $schedule_details);
    
}