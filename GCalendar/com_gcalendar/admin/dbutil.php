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
 * @copyright 2007-2011 Allon Moritz
 * @since 2.2.0
 */

defined('_JEXEC') or die();

JLoader::import('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_gcalendar/models', 'GCalendarModel');

class GCalendarDBUtil{

	public static function getCalendar($calendarID) {
		$model = JModelLegacy::getInstance('GCalendars', 'GCalendarModel', array('ignore_request' => true));
		$model->setState('ids',$calendarID);
		$items = $model->getItems();
		if(empty($items)){
			return null;
		}
		return $items[0];
	}

	public static function getCalendars($calendarIDs) {
		$model = JModelLegacy::getInstance('GCalendars', 'GCalendarModel', array('ignore_request' => true));
		$model->setState('ids', $calendarIDs);
		return $model->getItems();
	}

	public static function getAllCalendars() {
		$model = JModelLegacy::getInstance('GCalendars', 'GCalendarModel', array('ignore_request' => true));
		return $model->getItems();
	}
}