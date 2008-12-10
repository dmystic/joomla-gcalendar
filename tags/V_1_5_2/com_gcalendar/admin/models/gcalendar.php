<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.2 $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * GCalendar Model
 *
 */
class GCalendarsModelGCalendar extends JModel
{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the calendar identifier
	 *
	 * @access	public
	 * @param	int Calendar identifier
	 * @return	void
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}


	/**
	 * Method to get a calendar
	 * @return object with data
	 */
	function &getData()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = ' SELECT * FROM #__gcalendar '.
					'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
			$this->_data->calendar = null;
		}
		return $this->_data;
	}

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
		$row =& $this->getTable();

		$data = JRequest::get( 'post' );

		// Bind the form fields to the calendar table
		if (!$row->bind($data)) {
			JError::raiseWarning( 500, $row->getError() );
			return false;
		}

		// Make sure the calendar record is valid
		if (!$row->check()) {
			JError::raiseWarning( 500, $row->getError() );
			return false;
		}
		
		// Store the calendar table to the database
		if (!$row->store()) {
			JError::raiseWarning( 500, $row->getError() );
			return false;
		}

		return true;
	}

	/**
	 * Method to delete record(s)
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function delete()
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$row =& $this->getTable();

		if (count( $cids ))
			foreach($cids as $cid) {
				if (!$row->delete( $cid )) {
					JError::raiseWarning( 500, $row->getError() );
					return false;
				}
			}						
		}
		return true;
	}
			

}
?>