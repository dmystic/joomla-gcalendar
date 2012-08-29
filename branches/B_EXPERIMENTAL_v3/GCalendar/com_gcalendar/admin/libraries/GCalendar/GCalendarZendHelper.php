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
 * @copyright 2007-2012 Allon Moritz
 * @since 2.5.0
 */

defined('_JEXEC') or die();

if(!class_exists('apiClient')) {
	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'gapc'.DS.'apiClient.php');
}
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'gapc'.DS.'contrib'.DS.'apiCalendarService.php');

class GCalendarZendHelper{

	const SORT_ORDER_ASC = 'ascending';
	const SORT_ORDER_DESC = 'descending';

	const ORDER_BY_START_TIME = 'starttime';
	const ORDER_BY_LAST_MODIFIED = 'lastmodified';

	/**
	 * @param string $username
	 * @param string $password
	 *
	 * @return Zend_Gdata_Calendar_ListFeed|NULL
	 */
	public static function getCalendars($username, $password){
		try{
			$client = Zend_Gdata_ClientLogin::getHttpClient($username, $password, Zend_Gdata_Calendar::AUTH_SERVICE_NAME);

			$gdataCal = new Zend_Gdata_Calendar($client);
			return $gdataCal->getCalendarListFeed();
		} catch(Exception $e){
			JError::raiseWarning(200, $e->getMessage());
		}
		return null;
	}

	/**
	 * @param $calendar
	 * @param $startDate
	 * @param $endDate
	 * @param $max
	 * @param $filter
	 * @param $orderBy
	 * @param $pastEvents
	 * @param $sortOrder
	 *
	 * @return Zend_Gdata_App_Feed|NULL
	 */
	public static function getEvents($calendar, $startDate = null, $endDate = null, $max = 1000, $filter = null, $orderBy = GCalendarZendHelper::ORDER_BY_START_TIME, $pastEvents = false, $sortOrder = GCalendarZendHelper::SORT_ORDER_ASC, $startIndex = 1){
		// Implement View Level Access
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin') && !in_array($calendar->access, $user->getAuthorisedViewLevels())) {
			return array();
		}

		$cache = JFactory::getCache('com_gcalendar');
		$cache->setCaching(GCalendarUtil::getComponentParameter('gc_cache', 1) == '1');
		if(GCalendarUtil::getComponentParameter('gc_cache', 1) == 2){
			$conf = JFactory::getConfig();
			$cache->setCaching($conf->getValue( 'config.caching' ));
		}
		$cache->setLifeTime(GCalendarUtil::getComponentParameter('gc_cache_time', 900));
		$cache->setCaching(false);

		$events = $cache->call( array( 'GCalendarZendHelper', 'internalGetEvents' ), $calendar, $startDate, $endDate, $max, $filter, $orderBy, $pastEvents, $sortOrder, $startIndex);

		// Implement View Level Access
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin') && !in_array($calendar->access_content, $user->getAuthorisedViewLevels())) {
			foreach ($events as $event) {
				$event->setTitle(JText::_('COM_GCALENDAR_EVENT_BUSY_LABEL'));
				$event->setContent(null);
				$event->setWhere(null);
				$event->setWho(array());
			}
		}
		return $events;
	}

	/**
	 * @param $calendar
	 * @param $eventId
	 *
	 * @return Zend_Gdata_App_Entry|NULL
	 */
	public static function getEvent($calendar, $eventId){
		// Implement View Level Access
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin') && !in_array($calendar->access, $user->getAuthorisedViewLevels())) {
			return array();
		}

		$cache = JFactory::getCache('com_gcalendar');
		$cache->setCaching(GCalendarUtil::getComponentParameter('gc_cache', 1) == '1');
		if(GCalendarUtil::getComponentParameter('gc_cache', 1) == 2){
			$conf = JFactory::getConfig();
			$cache->setCaching($conf->getValue('config.caching'));
		}
		$cache->setLifeTime(GCalendarUtil::getComponentParameter('gc_cache_time', 900));

		$event =  $cache->call( array( 'GCalendarZendHelper', 'internalGetEvent' ), $calendar, $eventId);

		// Implement View Level Access
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin') && !in_array($calendar->access_content, $user->getAuthorisedViewLevels())) {
			$event->setTitle(JText::_('COM_GCALENDAR_EVENT_BUSY_LABEL'));
			$event->setContent(null);
			$event->setWhere(null);
			$event->setWho(array());
		}
		return $event;
	}

	/**
	 * @return Zend_Gdata_App_Feed|NULL
	 */
	public static function internalGetEvents($calendar, $startDate = null, $endDate = null, $max = 1000, $filter = null, $orderBy = GCalendarZendHelper::ORDER_BY_START_TIME, $startIndex = 1){
		if(empty($calendar->token)) {
			JError::raiseError(0, 'Calendar has no token!! Please authorize the calendar in the backend!');
			return null;
		}
		try {
			$client = self::getClient();
			$client->refreshToken($calendar->token);

			$service = new apiCalendarService($client);

			$options = array();
			$options['maxResults'] = $max;
			$options['orderBy'] = $orderBy;
			$options['orderBy'] = $orderBy;
			$options['singleEvents'] = true;
			if(!empty($filter)){
				$options['q'] = $filter;
			}
			if($startDate != null){
				$options['timeMin'] = JFactory::getDate($startDate)->format('Y-m-d\TH:i:s');
			}
			if($endDate != null){
				$options['timeMax'] = JFactory::getDate($endDate)->format('Y-m-d\TH:i:s');
			}
			$options['timeZone'] = 'Etc/GMT';
			return $service->events->listEvents(urldecode($calendar->calendar_id));






			$client = new Zend_Http_Client();

			if(!empty($calendar->username) && !empty($calendar->password)){
				$client = Zend_Gdata_ClientLogin::getHttpClient($calendar->username, $calendar->password, Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
			}

			$service = new Zend_Gdata_Calendar($client);

			$query = $service->newEventQuery();
			$query->setUser($calendar->calendar_id);
			if($calendar->magic_cookie != null){
				$query->setVisibility('private-'.$calendar->magic_cookie);
			}
			$query->setProjection('full');
			$query->setOrderBy($orderBy);
			$query->setSortOrder($sortOrder);
			$query->setSingleEvents('true');
			if(!empty($filter)){
				$query->setQuery($filter);
			}
			if($startDate != null){
				$query->setStartMin(strftime('%Y-%m-%dT%H:%M:%S', $startDate));
			}
			if($endDate != null){
				$query->setStartMax(strftime('%Y-%m-%dT%H:%M:%S',$endDate));
			}
			if($startDate == null && $endDate == null){
				$query->setFutureEvents($pastEvents ? 'false': 'true');
			}

			$query->setMaxResults($max);
			$query->setStartIndex($startIndex);
			$query->setParam('ctz', 'Etc/GMT');
			$query->setParam('hl', GCalendarUtil::getFrLanguage());

			$feed = $service->getFeed($query, 'GCalendar_Feed');
			foreach ($feed as $event) {
				$event->setParam('gcid', $calendar->id);
				$event->setParam('gccolor', $calendar->color);
				$event->setParam('gcname', $calendar->name);
			}
			return $feed;
		} catch (Exception $e) {
			echo $e;die;
			JError::raiseWarning(200, $e->getMessage());
			return null;
		}
	}

	/**
	 * @return Zend_Gdata_App_Entry|NULL
	 */
	public static function internalGetEvent($calendar, $eventId){
		try {
			$client = new Zend_Http_Client();

			if(!empty($calendar->username) && !empty($calendar->password)){
				$client = Zend_Gdata_ClientLogin::getHttpClient($calendar->username, $calendar->password, Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
			}

			$service = new Zend_Gdata_Calendar($client);

			$query = $service->newEventQuery();
			$query->setUser($calendar->calendar_id);
			if($calendar->magic_cookie != null){
				$query->setVisibility('private-'.$calendar->magic_cookie);
			}
			$query->setProjection('full');
			$query->setEvent($eventId);

			$query->setParam('ctz', 'Etc/GMT');
			$query->setParam('hl', GCalendarUtil::getFrLanguage());

			$event = $service->getEntry($query, 'GCalendar_Entry');
			$event->setParam('gcid', $calendar->id);
			$event->setParam('gccolor', $calendar->color);
			$event->setParam('gcname', $calendar->name);
			return $event;
		} catch (Zend_Gdata_App_Exception $e) {
			JError::raiseWarning(200, $e->getMessage());
			return null;
		}
	}

	public static function loadZendClasses() {
		static $zendLoaded;
		if($zendLoaded == null){
			ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . JPATH_ADMINISTRATOR . DS . 'components'. DS .'com_gcalendar' . DS . 'libraries');
			if(!class_exists('Zend_Loader')){
				require_once 'Zend/Loader.php';
			}

			Zend_Loader::loadClass('Zend_Gdata_AuthSub');
			Zend_Loader::loadClass('Zend_Gdata_HttpClient');
			Zend_Loader::loadClass('Zend_Gdata_Calendar');
			Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
			Zend_Loader::loadClass('GCalendar_Feed');
			Zend_Loader::loadClass('GCalendar_Entry');
			$zendLoaded = true;
		}
	}

	public static function getClient($includeRedirect = false) {
		$client = new apiClient();
		$client->setApplicationName('GCalendar joomla extension');
		$client->setClientId(GCalendarUtil::getComponentParameter('client-id'));
		$client->setClientSecret(GCalendarUtil::getComponentParameter('client-secret'));
		$uri = JFactory::getURI();
		if(filter_var($uri->getHost(), FILTER_VALIDATE_IP)) {
			$uri->setHost('localhost');
		}
		$client->setRedirectUri($uri->toString(array('scheme', 'host', 'port', 'path')).'?option=com_gcalendar&task=auth.store');
		$client->setUseObjects(true);

		$service = new apiAnalyticsService($client);

		return $client;
	}
}
GCalendarZendHelper::loadZendClasses();