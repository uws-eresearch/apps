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

	/**
	* @param $pattern
	* @param $searchProperties
	* @param $options
	* @return mixed
	*/
	public function search($pattern, $searchProperties, $options) {
		// dummy results
		return array(
			array('id' => 0, 'FN' => 'Thomas Müller', 'EMAIL' => 'a@b.c', 'GEO' => '37.386013;-122.082932'),
			array('id' => 5, 'FN' => 'Thomas Tanghus', 'EMAIL' => array('d@e.f', 'g@h.i')),
		);
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
		return VCard::delete($id);
	}
}