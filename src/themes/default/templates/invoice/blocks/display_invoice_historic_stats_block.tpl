<!-- display_invoice_historic_stats_block.tpl -->
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
        <td class="row2"><b>{t}Paid{/t}</b></td>
        <td class="row2"><b>{t}Refunded{/t}</b></td>
        <td class="row2"><b>{t}Cancelled{/t}</b></td>
        <td class="row2"><b>{t}Deleted{/t}</b></td>
    </tr>
    <tr class="olotd4">
        <td>{$invoice_stats.count_opened}</td>
        <td>{$invoice_stats.count_closed}</td>
        <td>{$invoice_stats.count_paid}</td>
        <td>{$invoice_stats.count_refunded}</td>
        <td>{$invoice_stats.count_cancelled}</td>
        <td>{$invoice_stats.count_deleted}</td>
    </tr>
</table>