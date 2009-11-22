<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Allon Moritz
 * @copyright 2007-2009 Allon Moritz
 * @version $Revision: 2.1.1 $
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');

$component	= &JComponentHelper::getComponent('com_gcalendar');
$menu = &JSite::getMenu();
$items		= $menu->getItems('componentid', $component->id);

$model = & $this->getModel();
if (is_array($items)){
	global $mainframe;
	$pathway	= &$mainframe->getPathway();
	foreach($items as $item) {
		$paramsItem	=& $menu->getParams($item->id);
		//if($paramsItem->get('calendars')===$this->params->get('calendars')){
		//	$pathway->addItem($this->params->get('name'), '');
		//}
	}
}
$params = $this->params;
?>

<div class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>"><?php
$variables = '';
$variables .= '?showTitle='.$params->get( 'title' );
$variables .= '&amp;showNav='.$params->get( 'navigation' );
$variables .= '&amp;showDate='.$params->get( 'date' );
$variables .= '&amp;showPrint='.$params->get( 'print' );
$variables .= '&amp;showTabs='.$params->get( 'tabs' );
$variables .= '&amp;showCalendars=0';
$variables .= '&amp;showTz='.$params->get( 'tz' );
$variables .= '&amp;mode='.$params->get( 'view' );
$variables .= '&amp;wkst='.$params->get( 'weekstart' );
$variables .= '&amp;bgcolor=%23'.$params->get( 'bgcolor' );
$variables .= '&hl='.GCalendarUtil::getFrLanguage();
$tz = $params->get('timezone');
if(!empty($tz))$tz='&ctz='.$tz;
$variables .= $tz;
$variables .= '&amp;height='.$params->get( 'height' );

$domain = 'http://www.google.com/calendar/embed';
$google_apps_domain = $params->get('google_apps_domain');
if(!empty($google_apps_domain)){
	$domain = 'http://www.google.com/calendar/hosted/'.$google_apps_domain.'/embed';
}

$calendar_list = '<div id="gc_google_view_list"><table>';
$calendarIDs = $params->get( 'calendarids' );
foreach($this->calendars as $calendar) {
	$value = '&amp;src='.$calendar->calendar_id;

	$html_color = '';
	if(!empty($calendar->color)){
		$color = $calendar->color;
		if(strpos($calendar->color, '#') === 0){
			$color = str_replace("#","%23",$calendar->color);
			$html_color = $calendar->color;
		}
		else if(!(strpos($calendar->color, '%23') === 0)){
			$color = '%23'.$calendar->color;
			$html_color = '#'.$calendar->color;
		}
		$value = $value.'&amp;color='.$color;
	}

	if(!empty($calendar->magic_cookie)){
		$value = $value.'&amp;pvttk='.$calendar->magic_cookie;
	}

	$checked = '';
	if(!empty($calendar->calendar_id)
	&& ((is_array( $calendarIDs ) && in_array($calendar->id, $calendarIDs))
	|| $calendar->id == $calendarIDs)){
		$variables .= $value;
		$checked = 'checked';
	}

	$calendar_list .="<tr>\n";
	$calendar_list .="<td><input type=\"checkbox\" name=\"".$calendar->calendar_id."\" value=\"".$value."\" ".$checked." onclick=\"updateGCalendarFrame(this)\"/></td>\n";
	$calendar_list .="<td><font color=\"".$html_color."\">".$calendar->name."</font></td></tr>\n";
}
$calendar_list .="</table></div>\n";
if($params->get('show_selection')==1){
	JHTML::_('behavior.mootools');
	$document = &JFactory::getDocument();
	$document->addScript( 'components/com_gcalendar/views/google/tmpl/gcalendar.js' );
	echo $calendar_list;
	echo "<div align=\"center\" style=\"text-align:center\">\n";
	echo "<a id=\"gc_google_view_toggle\" name=\"gc_google_view_toggle\" href=\"#\">\n";
	echo "<img id=\"gc_google_view_toggle_status\" name=\"gc_google_view_toggle_status\" src=\"".JURI::base()."components/com_gcalendar/views/google/tmpl/down.png\"/>\n";
	echo "</a></div>\n";
}
$calendar_url="";
if ($params->get('use_custom_css')) {
	$calendar_url= JURI::base().'administrator/components/com_gcalendar/libraries/mygooglecal/MyGoogleCal4.php'.$variables;
} else {
	$calendar_url=$domain.$variables;
}
echo $params->get( 'textbefore' );

?> <iframe id="gcalendar_frame" src="<?php echo $calendar_url; ?>"
	width="<?php echo $params->get( 'width' ); ?>"
	height="<?php echo $params->get( 'height' ); ?>" align="top"
	frameborder="0"
	class="gcalendar<?php echo $params->get( 'pageclass_sfx' ); ?>"> <?php echo JText::_( 'NO_IFRAMES' ); ?>
</iframe></div>

<?php
echo $params->get( 'textafter' );
?>