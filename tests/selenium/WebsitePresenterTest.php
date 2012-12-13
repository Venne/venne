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
		$this->assertEquals("Blog", $this->getValue("id=frmwebsiteForm-name"));
		$this->assertEquals("%n %s %t", $this->getValue("id=frmwebsiteForm-title"));
		$this->assertEquals("|", $this->getValue("id=frmwebsiteForm-titleSeparator"));
		$this->assertEquals("", $this->getValue("id=frmwebsiteForm-keywords"));
		$this->assertEquals("", $this->getValue("id=frmwebsiteForm-description"));
		$this->assertEquals("", $this->getValue("id=frmwebsiteForm-author"));
		$this->assertEquals("@cms/bootstrap", $this->getValue("id=frmwebsiteForm-layout"));
		$this->assertEquals("", $this->getValue("id=frmwebsiteForm-cacheMode"));
		$this->assertEquals("", $this->getValue("id=frmwebsiteForm-routePrefix"));

		$this->type("id=frmwebsiteForm-name", "Blog0");
		$this->type("id=frmwebsiteForm-title", "%n %s %t1");
		$this->type("id=frmwebsiteForm-titleSeparator", "|2");
		$this->type("id=frmwebsiteForm-keywords", "3");
		$this->type("id=frmwebsiteForm-description", "4");
		$this->type("id=frmwebsiteForm-author", "5");
		$this->select("id=frmwebsiteForm-layout", "label=bootstrap (cms)");
		$this->select("id=frmwebsiteForm-cacheMode", "label=static");
		$this->type("id=frmwebsiteForm-routePrefix", "<lang>/");
		$this->click("id=frmwebsiteForm-_submit");

		$this->assertEquals("Blog0", $this->getValue("id=frmwebsiteForm-name"));
		$this->assertEquals("%n %s %t1", $this->getValue("id=frmwebsiteForm-title"));
		$this->assertEquals("|2", $this->getValue("id=frmwebsiteForm-titleSeparator"));
		$this->assertEquals("3", $this->getValue("id=frmwebsiteForm-keywords"));
		$this->assertEquals("4", $this->getValue("id=frmwebsiteForm-description"));
		$this->assertEquals("5", $this->getValue("id=frmwebsiteForm-author"));
		$this->assertEquals("@cms/bootstrap", $this->getValue("id=frmwebsiteForm-layout"));
		$this->assertEquals("static", $this->getValue("id=frmwebsiteForm-cacheMode"));
		$this->assertEquals("<lang>/", $this->getValue("id=frmwebsiteForm-routePrefix"));

		$this->type("id=frmwebsiteForm-name", "Blog");
		$this->type("id=frmwebsiteForm-title", "%n %s %t");
		$this->type("id=frmwebsiteForm-titleSeparator", "|");
		$this->type("id=frmwebsiteForm-keywords", "");
		$this->type("id=frmwebsiteForm-description", "");
		$this->type("id=frmwebsiteForm-author", "");
		$this->select("id=frmwebsiteForm-layout", "label=bootstrap (cms)");
		$this->select("id=frmwebsiteForm-cacheMode", "label=off");
		$this->type("id=frmwebsiteForm-routePrefix", "");
		$this->click("id=frmwebsiteForm-_submit");

		$this->logout();
	}
}

