<!-- display_refunds_block.tpl -->
{*
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
*}
<b>{$block_title}</b>
<table class="olotable" width="100%" border="0" cellpadding="5" cellspacing="0">
    <tr>
        <td class="olohead">{t}Refund ID{/t}</td>        
        <td class="olohead">{t}Client{/t}</td>
        <td class="olohead">{t}INV ID{/t}</td>              
        <td class="olohead">{t}Date{/t}</td>
        <td class="olohead">{t}Type{/t}</td>        
        {if $qw_tax_system != 'no_tax'}
            <td class="olohead">{t}Net{/t}</td>   
            <td class="olohead">{if '/^vat_/'|preg_match:$qw_tax_system}{t}VAT{/t}{else}{t}Sales Tax{/t}{/if} {t}Rate{/t}</td>
            <td class="olohead">{if '/^vat_/'|preg_match:$qw_tax_system}{t}VAT{/t}{else}{t}Sales Tax{/t}{/if}</td>
        {/if}
        <td class="olohead">{t}Gross{/t}</td>
        <td class="olohead">{t}Balance{/t}</td>
        <td class="olohead">{t}Status{/t}</td>
        <td class="olohead">{t}Note{/t}</td>        
        <td class="olohead">{t}Action{/t}</td>
    </tr>
    {section name=r loop=$display_refunds.records}                                                            
        <!-- This allows double clicking on a row and opens the corresponding refund view details -->
        <tr class="row1" onmouseover="this.className='row2';" onmouseout="this.className='row1';"{if $display_refunds.records[r].status != 'deleted'} onDblClick="window.location='index.php?component=refund&page_tpl=details&refund_id={$display_refunds.records[r].refund_id}';"{/if}>
            <td class="olotd4" nowrap>{if $display_refunds.records[r].status != 'deleted'}<a href="index.php?component=refund&page_tpl=details&refund_id={$display_refunds.records[r].refund_id}">{$display_refunds.records[r].refund_id}</a>{else}{$display_refunds.records[r].refund_id}{/if}</td>
            <td class="olotd4" nowrap><a href="index.php?component=client&page_tpl=details&client_id={$display_refunds.records[r].client_id}">{$display_refunds.records[r].client_display_name}</a></td>
            <td class="olotd4" nowrap><a href="index.php?component=invoice&page_tpl=details&invoice_id={$display_refunds.records[r].invoice_id}">{$display_refunds.records[r].invoice_id}</a></td>            
            <td class="olotd4" nowrap>{$display_refunds.records[r].date|date_format:$date_format}</td>                                                                
            <td class="olotd4" nowrap>
                {section name=s loop=$refund_types}    
                    {if $display_refunds.records[r].type == $refund_types[s].type_key}{t}{$refund_types[s].display_name}{/t}{/if}        
                {/section}   
            </td>                                                                
            {if $qw_tax_system != 'no_tax'}
                <td class="olotd4" nowrap>{$currency_sym}{$display_refunds.records[r].unit_net|string_format:"%.2f"}</td>
                <td class="olotd4" nowrap>{$display_refunds.records[r].unit_tax_rate|string_format:"%.2f"}%</td>
                <td class="olotd4" nowrap>{$currency_sym}{$display_refunds.records[r].unit_tax|string_format:"%.2f"}</td>
            {/if}                                                           
            <td class="olotd4" nowrap>{$currency_sym}{$display_refunds.records[r].unit_gross|string_format:"%.2f"}</td> 
            <td class="olotd4" nowrap>{$currency_sym}{$display_refunds.records[r].balance|string_format:"%.2f"}</td>
            <td class="olotd4" nowrap>
               {section name=s loop=$refund_statuses}    
                   {if $display_refunds.records[r].status == $refund_statuses[s].status_key}{t}{$refund_statuses[s].display_name}{/t}{/if}        
               {/section} 
            </td> 
            <td class="olotd4" nowrap>{if $display_refunds.records[r].note}<img src="{$theme_images_dir}icons/16x16/view.gif" border="0" alt="" onMouseOver="ddrivetip('<div><strong>{t}Note{/t}</strong></div><hr><div>{$display_refunds.records[r].note|htmlentities|regex_replace:"/[\t\r\n']/":" "}</div>');" onMouseOut="hideddrivetip();">{/if}</td>            
            <td class="olotd4" nowrap>
                <a href="index.php?component=refund&page_tpl=details&refund_id={$display_refunds.records[r].refund_id}">
                    <img src="{$theme_images_dir}icons/16x16/viewmag.gif" alt="" border="0" onMouseOver="ddrivetip('<b>{t}View Refund Details{/t}');" onMouseOut="hideddrivetip();">
                </a>
                <a href="index.php?component=refund&page_tpl=edit&refund_id={$display_refunds.records[r].refund_id}">
                    <img src="{$theme_images_dir}icons/16x16/small_edit.gif" alt=""  border="0" onMouseOver="ddrivetip('<b>{t}Edit Refund Details{/t}</b>');" onMouseOut="hideddrivetip();">
                </a>
                {*<a href="index.php?component=refund&page_tpl=delete&refund_id={$display_refunds.records[r].refund_id}" onclick="return confirm('{t}Are you Sure you want to delete this Refund Record? This will permanently remove the record from the database.{/t}');">
                    <img src="{$theme_images_dir}icons/delete.gif" alt="" border="0" height="14" width="14" onMouseOver="ddrivetip('<b>{t}Delete Refund Record{/t}</b>');" onMouseOut="hideddrivetip();">
                </a>*}
            </td>
        </tr>
    {/section}
    {if $display_refunds.restricted_records}
        <tr>
            <td colspan="13">{t}Not all records are shown here.{/t} {t}Click{/t} <a href="index.php?component=refund&page_tpl=search">{t}here{/t}</a> {t}to see all records.{/t}</td>
        </tr>
    {/if}
    {if !$display_refunds.records}
        <tr>
            <td colspan="13" class="error">{t}There are no refunds.{/t}</td>
        </tr>        
    {/if}     
</table>