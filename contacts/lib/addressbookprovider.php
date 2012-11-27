<?php
/**
 * ownCloud - AddressbookProvider
 *
 * @author Thomas Tanghus
 * @copyright 2012 Thomas Tanghus (thomas@tanghus.net)
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Contacts;

/**
 * This class manages our addressbooks.
 */
class AddressbookProvider implements \OC\IAddressBook {

	const CONTACT_TABLE = '*PREFIX*contacts_cards';
	const PROPERTY_TABLE = '*PREFIX*contacts_cards_properties';

	/**
	 * Addressbook id
	 * @var integer
	 */
	public $id;
	
	/**
	 * Addressbook info array
	 * @var array
	 */
	public $addressbook;

	/**
	 * Constructor
	 * @param integer $id
	 */
	public function __construct($id) {
		$this->id = $id;
	}
	
	public function getAddressbook() {
		if(!$this->addressbook) {
			$this->addressbook = Addressbook::find($this->id);
		}
		return $this->addressbook;
	}
	
	/**
	* @return string defining the technical unique key
	*/
	public function getKey() {
		return $this->getAddressbook()['uri'];
	}

	/**
	* In comparison to getKey() this function returns a human readable (maybe translated) name
	* @return mixed
	*/
	public function getDisplayName() {
		return $this->getAddressbook()['displayname'];
	}

	/**
	* @return mixed
	*/
	public function getPermissions() {
		return $this->getAddressbook()['permissions'];
	}

	private function addProperty(&$results, $row) {
		if(!$row['name'] || !$row['value']) {
			return false;
		}

		$value = null;

		switch($row['name']) {
			case 'N':
			case 'ORG':
			case 'ADR':
			case 'CATEGORIES':
				$property = \Sabre\VObject\Property::create($row['name'], $row['value']);
				$value = $property->getParts();
				break;
			default:
				$value = $value = strtr($row['value'], array('\,' => ',', '\;' => ';'));
				break;
		}
		
		if(in_array($row['name'], App::$multi_properties)) {
			if(!isset($results[$row['contactid']])) {
				$results[$row['contactid']] = array('id' => $row['contactid'], $row['name'] => array($value));
			} elseif(!isset($results[$row['contactid']][$row['name']])) {
				$results[$row['contactid']][$row['name']] = array($value);
			} else {
				$results[$row['contactid']][$row['name']][] = $value;
			}
		} else {
			if(!isset($results[$row['contactid']])) {
				$results[$row['contactid']] = array('id' => $row['contactid'], $row['name'] => $value);
			} elseif(!isset($results[$row['contactid']][$row['name']])) {
				$results[$row['contactid']][$row['name']] = $value;
			}
		}
	}
	
	/**
	* @param $pattern
	* @param $searchProperties
	* @param $options
	* @return array|false
	*/
	public function search($pattern, $searchProperties, $options) {
		$ids = array();
		$results = array();
		$query = 'SELECT DISTINCT `contactid` FROM `' . self::PROPERTY_TABLE . '` WHERE 1 AND (';
		foreach($searchProperties as $property) {
			$query .= '(`name` = "' . $property . '" AND `value` LIKE "%' . $pattern . '%") OR ';
		}
		$query = substr($query, 0, strlen($query) - 4);
		$query .= ')';

		$stmt = \OCP\DB::prepare($query);
		$result = $stmt->execute();
		if (\OC_DB::isError($result)) {
			\OC_Log::write('contacts', __METHOD__ . 'DB error: ' . \OC_DB::getErrorMessage($result), 
				\OCP\Util::ERROR);
			return false;
		}
		while( $row = $result->fetchRow()) {
			$ids[] = $row['contactid'];
		}

		$query = 'SELECT `' . self::CONTACT_TABLE . '`.`addressbookid`, `' . self::PROPERTY_TABLE . '`.`contactid`, `' 
			. self::PROPERTY_TABLE . '`.`name`, `' . self::PROPERTY_TABLE . '`.`value` FROM `' 
			. self::PROPERTY_TABLE . '`,`' . self::CONTACT_TABLE . '` WHERE `'
			. self::CONTACT_TABLE . '`.`addressbookid` = \'' . $this->id . '\' AND `'
			. self::PROPERTY_TABLE . '`.`contactid` = `' . self::CONTACT_TABLE . '`.`id` AND `' 
			. self::PROPERTY_TABLE . '`.`contactid` IN (' . join(',', array_fill(0, count($ids), '?')) . ')';

		//\OC_Log::write('contacts', __METHOD__ . 'DB query: ' . $query, \OCP\Util::DEBUG);
		$stmt = \OCP\DB::prepare($query);
		$result = $stmt->execute($ids);
		while( $row = $result->fetchRow()) {
			$this->addProperty($results, $row);
		}
		
		return $results;
	}

	/**
	* @param $properties
	* @return mixed
	*/
	public function createOrUpdate($properties) {
		// dummy
		return array('id'    => 0, 'FN' => 'Thomas Müller', 'EMAIL' => 'a@b.c',
			'PHOTO' => 'VALUE=uri:http://www.abc.com/pub/photos/jqpublic.gif',
			'ADR'   => ';;123 Main Street;Any Town;CA;91921-1234'
		);
	}

	/**
	* @param $id
	* @return mixed
	*/
	public function delete($id) {
		try {
			$query = 'SELECT * FROM `*PREFIX*contacts_cards` WHERE `id` = ? AND `addressbookid` = ?';
			$stmt = \OCP\DB::prepare($query);
			$result = $stmt->execute(array($id, $this->id));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('contacts', __METHOD__ . 'DB error: ' . \OC_DB::getErrorMessage($result), 
					\OCP\Util::ERROR);
				return false;
			}
			if($result->numRows() === 0) {
				\OC_Log::write('contacts', __METHOD__ 
					. 'Contact with id ' . $id . 'doesn\'t belong to addressbook with id ' . $this->id, 
					\OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__ . ', exception: ' . $e->getMessage(), 
				\OCP\Util::ERROR);
			return false;
		}
		return VCard::delete($id);
	}
}