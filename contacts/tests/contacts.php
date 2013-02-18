<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts;

\OC_App::loadApp('contacts');

class Test_Contacts_App extends \PHPUnit_Framework_TestCase {
	function testAddressbook() {
		$uid=uniqid();
		\OC_User::setUserId($uid);
		$this->assertEquals(array(), Addressbook::all($uid, true));
		$aid1 = Addressbook::add($uid, 'test');
		$this->assertTrue(Addressbook::isActive($aid1));

		$all = Addressbook::all($uid);
		$this->assertEquals(1, count($all));

		$this->assertEquals($aid1, $all[0]['id']);
		$this->assertEquals('test', $all[0]['displayname']);
		$this->assertEquals('test', $all[0]['uri']);
		$this->assertEquals($uid, $all[0]['userid']);

		$aid2=Addressbook::add($uid, 'test');
		$this->assertNotEquals($aid1, $aid2);

		$all=Addressbook::all($uid);
		$this->assertEquals(2, count($all));

		$this->assertEquals($aid2, $all[1]['id']);
		$this->assertEquals('test', $all[1]['displayname']);
		$this->assertEquals('test1', $all[1]['uri']);

		$cal1=Addressbook::find($aid1);
		$this->assertEquals($cal1, $all[0]);

		Addressbook::delete($aid1);
		Addressbook::delete($aid2);

		// A default addressbook is created on delete if it is the last.
		$this->assertEquals(1, count(Addressbook::all($uid, true)));
	}
}
