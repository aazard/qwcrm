<!-- display_suppliers_block.tpl -->
{*
 * @package   QWcrm
 * @author    Jon Brown https://quantumwarp.com/
 * @copyright Copyright (C) 2016 - 2017 Jon Brown, All rights reserved.
 * @license   GNU/GPLv3 or later; https://www.gnu.org/licenses/gpl.html
*}
<b>{$block_title}</b>
<table class="olotable" width="100%" border="0" cellpadding="5" cellspacing="0">                                                    
    <tr>
        <td class="olohead">{t}ID{/t}</td>
        <td class="olohead">{t}Name{/t}</td>                                                        
        <td class="olohead">{t}Type{/t}</td>
        <td class="olohead">{t}Zip{/t}</td> 
        <td class="olohead">{t}Status{/t}</td>
        <td class="olohead">{t}Note{/t}</td>
        <td class="olohead">{t}Description{/t}</td>
        <td class="olohead">{t}Action{/t}</td>
    </tr>                                                    
    {section name=s loop=$display_suppliers.records}
        <!-- This allows double clicking on a row and opens the corresponding supplier view details -->
        <tr class="row1" onmouseover="this.className='row2';" onmouseout="this.className='row1';" onDblClick="window.location='index.php?component=supplier&page_tpl=details&supplier_id={$display_suppliers.records[s].supplier_id}';">                                                           
            <td class="olotd4" nowrap><a href="index.php?component=supplier&page_tpl=details&supplier_id={$display_suppliers.records[s].supplier_id}">{$display_suppliers.records[s].supplier_id}</a></td>
            <td class="olotd4" nowrap><a href="index.php?component=supplier&page_tpl=details&supplier_id={$display_suppliers.records[s].supplier_id}">{$display_suppliers.records[s].display_name}</a></td>
            <td class="olotd4" nowrap>
                {section name=t loop=$supplier_types}    
                    {if $display_suppliers.records[s].type == $supplier_types[t].type_key}{t}{$supplier_types[t].display_name}{/t}{/if}        
                {/section}    
            </td>
            <td class="olotd4" nowrap>{$display_suppliers.records[s].zip}</td>
            <td class="olotd4" nowrap>
               {section name=r loop=$supplier_statuses}    
                   {if $display_suppliers.records[s].status == $supplier_statuses[r].status_key}{t}{$supplier_statuses[r].display_name}{/t}{/if}        
               {/section} 
            </td>
            <td class="olotd4" nowrap>{if $display_suppliers.records[s].note}
                <img src="{$theme_images_dir}icons/16x16/view.gif" border="0" alt="" onMouseOver="ddrivetip('<div><strong>{t}Note{/t}</strong></div><hr><div>{$display_suppliers.records[s].note|htmlentities|regex_replace:"/[\t\r\n']/":" "}</div>');" onMouseOut="hideddrivetip();">{/if}
            </td>                                                            
            <td class="olotd4" nowrap><img src="{$theme_images_dir}icons/16x16/view.gif" border="0" alt="" onMouseOver="ddrivetip('<div><strong>{t}Description{/t}</strong></div><hr><div>{$display_suppliers.records[s].description|htmlentities|regex_replace:"/[\t\r\n']/":" "}</div>');" onMouseOut="hideddrivetip();"></td>                                                            
            <td class="olotd4" nowrap>
                <a href="index.php?component=supplier&page_tpl=details&supplier_id={$display_suppliers.records[s].supplier_id}">
                    <img src="{$theme_images_dir}icons/16x16/viewmag.gif" alt="" border="0" onMouseOver="ddrivetip('<b>{t}View Supplier Details{/t}</b>');" onMouseOut="hideddrivetip();">
                </a>
                <a href="index.php?component=supplier&page_tpl=edit&supplier_id={$display_suppliers.records[s].supplier_id}">
                    <img src="{$theme_images_dir}icons/16x16/small_edit.gif" alt=""  border="0" onMouseOver="ddrivetip('<b>{t}Edit Supplier Details{/t}</b>');" onMouseOut="hideddrivetip();">
                </a>
                {*<a href="index.php?component=supplier&page_tpl=delete&supplier_id={$display_suppliers.records[s].supplier_id}" onclick="return confirm('{t}Are you Sure you want to delete this Supplier Record? This will permanently remove the record from the database.{/t}');">
                    <img src="{$theme_images_dir}icons/delete.gif" alt="" border="0" height="14" width="14" onMouseOver="ddrivetip('<b>{t}Delete Supplier Record{/t}</b>');" onMouseOut="hideddrivetip();">
                </a>*}
            </td>
        </tr>
    {/section}
    {if $display_suppliers.restricted_records}
        <tr>
            <td colspan="8">{t}Not all records are shown here.{/t} {t}Click{/t} <a href="index.php?component=supplier&page_tpl=search">{t}here{/t}</a> {t}to see all records.{/t}</td>
        </tr>
    {/if}
    {if !$display_suppliers.records}
        <tr>
            <td colspan="8" class="error">{t}There are no suppliers.{/t}</td>
        </tr>        
    {/if}  
</table>