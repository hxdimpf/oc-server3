<?php
/****************************************************************************
 * ./lang/de/ocstyle/editcache.inc.php
 * -------------------
 * begin                : Mon July 6 2004
 *
 * For license information see LICENSE.md
 ****************************************************************************/

/****************************************************************************
 *
 *
 * language vars
 ****************************************************************************/

$submit = t('Save');
$remove = t('Delete');
$edit = t('Edit');

$error_wrong_node = t('This cache has been created on another Opencaching website. The cache can only be edited there.');
$savealert = t('Please save the changed geocache properties first.');

$all_countries_submit = '<input type="submit" name="show_all_countries_submit" id="showallcountries" value="' . t('Show all') . '" class="formbutton" onclick="submitbutton(\'showallcountries\')" />';
$error_general = "<tr><td class='error' colspan='2'><b>" . t('Some errors occurred, please check the marked fields.') . "</b></td></tr>";
$name_message = '&nbsp;<span class="errormsg">' . t('Cachename is invalid') . '</span>';
$date_message = '<span class="errormsg">' . t('date is invalid') . '</span>';
$hide_after_publish_message = '<span class="errormsg">' . t('The cache must be hidden before publish.') . '</span>';
$event_before_publish_message = '<span class="errormsg">' . t('The event must happen after publish.') . '</span>';
$coords_message = '<span class="errormsg">' . t('The used coordinates are invalid.') . '</span>';
$time_not_ok_message = '<span class="errormsg">' . t('The entered time is invalid.') . '</span>';
$way_length_not_ok_message = '<span class="errormsg">' . t('The distance you have entered is invalid. Format aa.aaa') . '</span>';
$sizemismatch_message = '&nbsp;<span class="errormsg">' . t('For virtual and webcam caches, the cache size has to be -no container-!') . '</span>';
$status_message = '&nbsp;<span class="errormsg">' . t('The cache-status does not fit to your publishing options') . '</span>';
$status_change = '<br /><div style="margin-top:6px"><img src="lang/de/ocstyle/images/misc/hint.gif" border="0" width="15" height="11" >' . t('To change the state, you need to {a}log</a> the new state.') . '</div>';
$status_change_a = '<a href="log.php?cacheid=%1" onclick="testListingModified(this)">';
$diff_not_ok_message = '&nbsp;<span class="errormsg">' . t('Choose both valuations!') . '</span>';
$safari_not_allowed_message = '<span class="errormsg">' . t('Only virtual caches can be safari caches.') . '</span>';
$bad_wpgc_message = '<span class="errormsg">' . t('GC waypoint is invalid, must be GCxxxxx') . '</span>';

$nopictures = '<tr><td colspan="2">' . t('No pictures available') . '</td></tr><tr><td colspan="2">&nbsp;</td></tr>';
$pic_editdel = '<a href="{link}" target="_blank">{title}</a> [<a onclick="testListingModified(this)" href="picture.php?action=edit&uuid={uuid}">' . t('edit') . '</a>] [<a onclick="testListingModified(this);" href="javascript:if(confirm(\'' . t('Do you really want to delete the picture \\\'{title}\\\'?') . '\')) location.href=\'picture.php?action=delete&uuid={uuid}\'">' . t('delete') . '</a>]';
$pictureline0 = '<tr><td colspan="2">' . $pic_editdel . '</td></tr>';
$pictureline = '<tr><td colspan="2">' . $pic_editdel . ' [<a href="picture.php?action=up&uuid={uuid}" onclick="testListingModified(this)">' . t('move up') . '</a>]</td></tr>';
// Ocprop: <a href=\"http://.*?\.opencaching\.de/images/uploads/.*?\">(.*?)<\/a>.*?\[<a href=\"picture\.php\?action=[a-z]*?\&uuid=(.*?)\">
$picturelines = '{lines}<tr><td colspan="2">&nbsp;</td></tr>';
$gc_com_msg2 = t('To prevent any problems with geocaching.com we want to please you to upload all linked pictures to %1 as well and link the pictures in the HTML code to these on %1');

$nowaypoints = '<tr><td colspan="2">' . t('No waypoints available') . '</td></tr>';
$waypointline = '<tr bgcolor="#ffffff"><td><table class="narrowtable" cellspacing="0" cellpadding="0"><tr><td><img src="{wp_image}" /></td><td>{wp_type}</td></tr></table></td><td><table class="narrowtable"><tr><td style="white-space:nowrap">{wp_coordinate}</td></tr></table></tp><td>{wp_show_description}</td><td>[<a href="childwp.php?cacheid={cacheid}&childid={childid}" onclick="testListingModified(this)">' . t('Edit') . '</a>] [<a href="childwp.php?cacheid={cacheid}&deleteid={childid}" onclick="testListingModified(this)">' . t('Delete') . '</a>]</td></tr>';
$waypointlines = '<tr><td colspan="2"><table class="edit_wptable">{lines}</table></td></tr><tr><td colspan="2">&nbsp;</td></tr>';

$cache_attrib_js = "[{id}, {selected}, '{img_undef}', '{img_large}', [{conflicting_attribs}]]";
$cache_attrib_pic = '<img id="attr{attrib_id}" src="{attrib_pic}" border="0" onmousedown="toggleAttr({attrib_id})" onmouseover="Tip(\'{html_desc}\', TITLE, \'{name}\', TITLEBGCOLOR, \'{color}\', TITLEFONTCOLOR, \'#000000\', BGCOLOR, \'#FFFFFF\', BORDERCOLOR, \'{color}\', CLICKCLOSE, true, DELAY, 0, FADEIN, false, FADEOUT, false, FONTCOLOR, \'#000080\', WIDTH, 500)" onmouseout="UnTip()" />&nbsp;';

$cache_attrib_group =
    '<div class="attribgroup"><table cellspacing="0" style="display:inline;border-spacing:0px;">
         <tr><td bgcolor="{color}" style="line-height:9px;padding-top:2px;margin:0 0 0 0;border-left:1px solid gray;border-right:1px solid gray;border-top:1px solid gray;"><font size="1">{name}</font></td></tr>
         <tr><td bgcolor="#F8F8F8" style="margin:0 0 0 0;border-left:1px solid gray;border-right:1px solid gray;border-bottom:1px solid gray;">{attribs}</td></tr>
       </table></div>';

$default_lang = t('EN');

$activation_form = '
    <tr><td class="spacer" colspan="2"></td></tr>
    <tr>
        <td class="strong">' . t('Publication:') . '</td>
        <td class="aligned space">
            <input type="radio" class="radio" name="publish" id="publish_now" value="now" {publish_now_checked} />&nbsp;<label for="publish_now">' . t('Publish now') . '</label><br />
            <input type="radio" class="radio" name="publish" id="publish_later" value="later" {publish_later_checked} />&nbsp;<label for="publish_later">' . t('Publish on') . '</label>
            <input class="input20" type="text" name="activate_day" maxlength="2" value="{activate_day}"/>.
            <input class="input20" type="text" name="activate_month" maxlength="2" value="{activate_month}"/>.
            <input class="input40" type="text" name="activate_year" maxlength="4" value="{activate_year}"/>&nbsp;
            <select name="activate_hour" class="input60">
                {activation_hours}
            </select>&nbsp;' . t('#time_suffix_label#') . '&nbsp;{activate_on_message}<br />
            <input type="radio" class="radio" name="publish" id="publish_notnow" value="notnow" {publish_notnow_checked} />&nbsp;<label for="publish_notnow">' . t('Do not publish now.') . '</label>
        </td>
    </tr>
    ';
