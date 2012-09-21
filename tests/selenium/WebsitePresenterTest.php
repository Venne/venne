<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WebsitePresenterTest extends AdminPresenterTest
{

	public function testEdit()
	{
		$this->login();

		$this->clickAndWait("link=Basic meta informationsEdit base meta informations about this website");
		try {
			$this->assertEquals("Blog %s %t", $this->getValue("id=frmwebsiteForm-title"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertEquals("|", $this->getValue("id=frmwebsiteForm-titleSeparator"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertEquals("", $this->getValue("id=frmwebsiteForm-keywords"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertEquals("", $this->getValue("id=frmwebsiteForm-description"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertEquals("", $this->getValue("id=frmwebsiteForm-author"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->verifyTextPresent("-------bootstrap (cms)");
		$this->verifyTextPresent("offtimestatic");
		try {
			$this->assertEquals("", $this->getValue("id=frmwebsiteForm-routePrefix"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->type("id=frmwebsiteForm-title", "Blog %s %t1");
		$this->type("id=frmwebsiteForm-titleSeparator", "|2");
		$this->type("id=frmwebsiteForm-keywords", "3");
		$this->type("id=frmwebsiteForm-description", "4");
		$this->type("id=frmwebsiteForm-author", "5");
		$this->select("id=frmwebsiteForm-layout", "label=bootstrap (cms)");
		$this->click("css=option[value=\"@cms/bootstrap\"]");
		$this->select("id=frmwebsiteForm-cacheMode", "label=static");
		$this->click("css=option[value=\"static\"]");
		$this->select("css=#frmwebsiteForm-routePrefix-pair > div.controls > select", "label=<lang>/");
		$this->click("//div[@id='frmwebsiteForm-routePrefix-pair']/div/select/option[2]");
		$this->click("id=frmwebsiteForm-_submit");
		try {
			$this->assertEquals("Blog %s %t1", $this->getValue("id=frmwebsiteForm-title"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertEquals("|2", $this->getValue("id=frmwebsiteForm-titleSeparator"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertEquals("3", $this->getValue("id=frmwebsiteForm-keywords"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertEquals("4", $this->getValue("id=frmwebsiteForm-description"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		try {
			$this->assertEquals("5", $this->getValue("id=frmwebsiteForm-author"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->verifyTextPresent("-------bootstrap (cms)");
		$this->verifyTextPresent("offtimestatic");
		try {
			$this->assertEquals("<lang>/", $this->getValue("id=frmwebsiteForm-routePrefix"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->verifyTextPresent("× Website has been saved");
		$this->type("id=frmwebsiteForm-title", "Blog %s %t");
		$this->type("id=frmwebsiteForm-titleSeparator", "|");
		$this->type("id=frmwebsiteForm-keywords", "");
		$this->type("id=frmwebsiteForm-description", "");
		$this->type("id=frmwebsiteForm-author", "");
		$this->select("id=frmwebsiteForm-layout", "label=-------");
		$this->click("css=option");
		$this->select("id=frmwebsiteForm-cacheMode", "label=off");
		$this->click("css=#frmwebsiteForm-cacheMode > option");
		$this->click("id=frmwebsiteForm-routePrefix");
		$this->type("id=frmwebsiteForm-routePrefix", "");
		$this->click("id=frmwebsiteForm-_submit");

		$this->logout();
	}
}

