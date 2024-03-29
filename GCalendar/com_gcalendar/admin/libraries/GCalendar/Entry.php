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

class GCalendar_Entry extends Zend_Gdata_Calendar_EventEntry{

	const SINGLE_WHOLE_DAY    = 1;
	const SINGLE_PART_DAY     = 2;
	const MULTIPLE_WHOLE_DAY  = 3;
	const MULTIPLE_PART_DAY   = 4;

	private $dayType = null;
	private $startDate = null;
	private $endDate = null;
	private $modifiedDate = null;
	private $location = null;
	private $gcalId = null;
	private $rrule = null;
	private $originalGCEvent = null;
	private $params = array();

	public function setParam($key, $value){
		$this->params[$key] = $value;
	}

	public function getParam($key){
		return $this->params[$key];
	}

	public function getGCalId(){
		if($this->gcalId == null){
			$this->gcalId = substr($this->getId(), strrpos($this->getId(), '/')+1);
		}
		return $this->gcalId;
	}

	public function getDayType(){
		if($this->dayType == null){
			$SECSINDAY = 86400;

			$start = clone $this->getStartDate();
			$start->modify('+1 day');
			if($start->format('U') <= $this->getEndDate()->format('U')) {
				if($start->format('U') == $this->getEndDate()->format('U') && $this->getStartDate()->format('g:i a') == '12:00 am') {
					$this->dayType =  GCalendar_Entry::SINGLE_WHOLE_DAY;
				} else {
					if($this->getStartDate()->format('g:i a') == '12:00 am' && $this->getEndDate()->format('g:i a') == '12:00 am') {
						$this->dayType =  GCalendar_Entry::MULTIPLE_WHOLE_DAY;
					} else {
						$this->dayType =  GCalendar_Entry::MULTIPLE_PART_DAY;
					}
				}
			} else {
				$this->dayType = GCalendar_Entry::SINGLE_PART_DAY;
			}
		}
		return $this->dayType;
	}

	public function getStartDate(){
		if($this->startDate == null){
			$when = $this->getWhen();
			if(empty($when) && $this->getRecurrence() != null) {
				$parts = explode(PHP_EOL, $this->getRecurrence()->getText());
				foreach ($parts as $part) {
					if(strpos($part, 'DTSTART') === false) {
						continue;
					}
					if(strpos($part, 'DTSTART;VALUE=DATE:') !== false) {
						$this->startDate = JFactory::getDate(substr($part, 19).' 00:00:00');
					} else {
						$this->startDate = JFactory::getDate(substr($part, 8));
					}
				}
			} else if(is_array($when)){
				$when = reset($when);
				$this->startDate = JFactory::getDate($when->getStartTime());
			}
			if($this->startDate != null && ($this->getDayType() == GCalendar_Entry::SINGLE_PART_DAY || $this->getDayType() == GCalendar_Entry::MULTIPLE_PART_DAY)) {
				$this->startDate->setTimezone(new DateTimeZone(GCalendarUtil::getComponentParameter('timezone')));
			}
		}
		return $this->startDate;
	}

	public function getEndDate(){
		if($this->endDate == null){
			$when = $this->getWhen();
			if(empty($when) && $this->getRecurrence() != null) {
				$parts = explode(PHP_EOL, $this->getRecurrence()->getText());
				foreach ($parts as $part) {
					if(strpos($part, 'DTEND') === false) {
						continue;
					}
					if(strpos($part, 'DTEND;VALUE=DATE:') !== false) {
						$this->endDate = JFactory::getDate(substr($part, 17).' 00:00:00');
					} else {
						$this->endDate = JFactory::getDate(substr($part, 6));
					}
				}
			} else if(is_array($when)){
				$when = reset($when);
				$this->endDate = JFactory::getDate($when->getEndTime());
			}
			if($this->endDate != null && ($this->getDayType() == GCalendar_Entry::SINGLE_PART_DAY || $this->getDayType() == GCalendar_Entry::MULTIPLE_PART_DAY)) {
				$this->endDate->setTimezone(new DateTimeZone(GCalendarUtil::getComponentParameter('timezone')));
			}
		}
		return $this->endDate;
	}

	public function getModifiedDate(){
		if($this->modifiedDate == null){
			$this->modifiedDate = JFactory::getDate($this->getPublished());
			$this->modifiedDate->setTimezone(new DateTimeZone(GCalendarUtil::getComponentParameter('timezone')));
		}
		return $this->modifiedDate;
	}

	public function getLocation(){
		if($this->location == null && $this->getWhere() !=null){
			$where = $this->getWhere();
			if(is_array($where)){
				$where = reset($where);
			}
			$this->location = $where->getValueString();
		}
		return $this->location;
	}

	public function getRRule() {
		if($this->rrule == null) {
			$rec = $this->getRecurrence();
			if($rec == null) {
				$original = $this->getOriginalGCEvent();
				if($original != null) {
					$rec = $original->getRecurrence();
				}
			}
			if ($rec != null) {
				$parts = explode(PHP_EOL, $rec->getText());
				foreach ($parts as $part) {
					if(strpos($part, 'RRULE:') === false) {
						continue;
					}
					$this->rrule = substr($part, 6);
				}
			}
		}
		return $this->rrule;
	}

	public function isRepeating() {
		return strpos($this->getGCalId(), '_') !== false || $this->getRecurrence() != null;
	}

	public function getOriginalGCEvent() {
		if($this->originalGCEvent != null) {
			return $this->originalGCEvent;
		}
		if(!$this->isRepeating()) {
			return null;
		}
		if($this->getRecurrence() != null) {
			return $this;
		}
		$this->originalGCEvent = GCalendarZendHelper::getEvent(GCalendarDBUtil::getCalendar($this->getParam('gcid')), substr($this->getGCalId(), 0, strpos($this->getGCalId(), '_')));
		return $this->originalGCEvent;
	}

	/**
	 * Returns an integer less than, equal to, or greater than zero if
	 * the first argument is considered to be respectively less than,
	 * equal to, or greater than the second.
	 * This function can be used to sort an array of GCalendar_Entry
	 * items with usort.
	 *
	 * @see http://www.php.net/usort
	 * @param $event1
	 * @param $event2
	 * @return the comparison integer
	 */
	public static function compare(GCalendar_Entry $event1, GCalendar_Entry $event2){
		return $event1->getStartDate()->format('U') - $event2->getStartDate()->format('U');
	}

	/**
	 * Compares the events descending.
	 *
	 * @see GCalendar_Entry::compare()
	 * @param GCalendar_Entry $event1
	 * @param GCalendar_Entry $event2
	 * @return number
	 */
	public static function compareDesc(GCalendar_Entry $event1, GCalendar_Entry $event2){
		return $event2->getStartDate()->format('U') - $event1->getStartDate()->format('U');
	}
}