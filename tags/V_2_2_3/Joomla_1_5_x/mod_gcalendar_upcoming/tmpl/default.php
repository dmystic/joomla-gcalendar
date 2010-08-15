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
 * @copyright 2007-2010 Allon Moritz
 * @since 2.2.0
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$event_display = $params->get('output', '');

$dateformat=$params->get('date_format', '%d.%m.%Y');
$timeformat=$params->get('time_format', '%H:%M');

echo $params->get( 'text_before' );
if(!empty($gcalendar_data)){
	foreach( $gcalendar_data as $item){
		// APRIL 2010 MOD - CALENDAR IMAGES by Tyson Moore
		if($params->get('images', 'no') != 'no') {
			$tmp = JFactory::getDate($item->get_start_date());
			$startDate = $tmp->toFormat($dateformat);
			$month = $tmp->toFormat('%m');
			$month_text = strtoupper($tmp->toFormat('%h'));
			$day = $tmp->toFormat('%e');
			$feed = $item->get_feed();
			$colorImageBackground = $params->get('images', 'yes') == 'custom' ? '#'.$params->get('calimage_background') : GCalendarUtil::getFadedColor($feed->get('gccolor'), 80);
			$colorMonth = $params->get('images', 'yes') == 'custom' ? $params->get('calimage_month') : 'FFFFFF';
			$colorDay = $params->get('images', 'yes') == 'custom' ? $params->get('calimage_day') : $feed->get('gccolor');
			echo '<div style="float: left; margin-right: 6px; width: 42px; height: 42px; background-image: url(\'modules/mod_gcalendar_upcoming/tmpl/images/calendar-icon.gif\');">';
			echo '<div style="background-color: ' . $colorImageBackground . '; width: 32px; height: 10px; margin-top: 6px; margin-left: 5px;"></div><div style="padding:2px; font-weight: bold; font-size: 10px; color: #' . $colorMonth . '; text-align: center; position: relative; margin-top: -15px; margin-bottom: -4px;">' . $month_text . '</div>';
			echo '<div style="font-weight: bold; font-size: 1.3em; color: #' . $colorDay . '; width: 42px; text-align: center;">' . $day . '</div>';
			echo '</div>';
		}
		//END MOD
		echo GCalendarUtil::renderEvent($item, $event_display, $dateformat, $timeformat);
		if($params->get('images', 'no') != 'no') {
			echo '<p style="clear: both;"/>';
		}
	}
}
echo $params->get( 'text_after' );
?>