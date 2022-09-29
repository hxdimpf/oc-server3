{***************************************************************************
 * You can find the license in the docs directory
 ***************************************************************************}
 {* OCSTYLE *}
{strip}
    {foreach name=topmenu from=$items item=menuitem}
        <li><a href="{$menuitem.href}" {$menuitem.target|default} {if $menuitem.selected} class="selected bg-green06"{/if}>{$menuitem.menustring|escape}</a></li>
    {/foreach}
{/strip}
