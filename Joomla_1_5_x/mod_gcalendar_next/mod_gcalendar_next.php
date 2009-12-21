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
 * This code was based on Allon Moritz's great work in the companion
 * upcoming module. 
 * 
 * @author Eric Horne
 * @copyright 2009 Eric Horne 
 * @version $Revision: 2.2.0 $
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once (dirname(__FILE__).DS.'helper.php');

$document =& JFactory::getDocument();
$gcalendar_item = ModGCalendarNextHelper::getCalendarItems($params);
require( JModuleHelper::getLayoutPath( 'mod_gcalendar_next') );
?>
