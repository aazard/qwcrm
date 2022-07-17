<!-- display_workorder_historic_stats_block.tpl -->
{*
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
*}
<b>{$block_title}</b>
<br>
<table width="100%" cellpadding="4" cellspacing="0" border="0" class="olotable">
    <tr class="olotd4">
        <td class="row2"><b>{t}Opened{/t}</b></td>                                            
        <td class="row2"><b>{t}Closed{/t}</b></td>        
        <td class="row2"><b>{t}Closed without Invoice{/t}</b></td>
        <td class="row2"><b>{t}Closed with Invoice{/t}</b></td>
        <td class="row2"><b>{t}Deleted{/t}</b></td>
    </tr>
    <tr class="olotd4">
        <td>{$workorder_stats.count_opened}</td>
        <td>{$workorder_stats.count_closed}</td>
        <td>{$workorder_stats.count_closed_without_invoice}</td>
        <td>{$workorder_stats.count_closed_with_invoice}</td>        
        <td>{$workorder_stats.count_deleted}</td> 
    </tr>
</table>