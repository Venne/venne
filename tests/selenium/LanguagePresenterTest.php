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
class LanguagePresenterTest extends AdminPresenterTest
{

	public function testCreateEditDelete()
	{
		$this->login();

		$this->clickAndWait("link=Language settingsManage website languages, aliases,...");
		$this->clickAndWait("link=Create new");
		$this->type("id=frmcreateForm-name", "test1");
		$this->type("id=frmcreateForm-short", "test2");
		$this->type("id=frmcreateForm-alias", "test3");
		$this->clickAndWait("id=frmcreateForm-_submit");

		$this->assertEquals("test1", $this->getTable("id=snippet-table-table.2.1"));
		$this->assertEquals("test3", $this->getTable("id=snippet-table-table.2.2"));
		$this->assertEquals("test2", $this->getTable("id=snippet-table-table.2.3"));

		$this->clickAndWait("xpath=(//a[contains(text(),'Edit')])[4]");
		$this->type("id=frmeditForm-name", "test4");
		$this->type("id=frmeditForm-short", "test5");
		$this->type("id=frmeditForm-alias", "test6");
		$this->clickAndWait("id=frmeditForm-_submit");

		$this->assertEquals("test4", $this->getTable("id=snippet-table-table.2.1"));
		$this->assertEquals("test6", $this->getTable("id=snippet-table-table.2.2"));
		$this->assertEquals("test5", $this->getTable("id=snippet-table-table.2.3"));

		$this->click("xpath=(//a[contains(text(),'Delete')])[4]");

		$this->logout();
	}
}

