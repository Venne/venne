<?php

/**
 * XDebug Helper
 * Simple extension for Nette Framework, which integrates into the Debug Bar and
 * enables you to easily start and stop a Xdebug session.
 *
 * @author Jan Smitka <jan@smitka.org>
 * @copyright Copyright (c) 2010 Jan Smitka <jan@smitka.org>
 */


namespace NetteExtras;
use Nette\Diagnostics\IBarPanel;


class XDebugHelper implements IBarPanel
{
	private $ideKey;


	public function __construct($ideKey = 'netbeans-xdebug')
	{
		$this->ideKey = $ideKey;
	}



	public function getId()
	{
		return 'PandaWeb-XdebugHelper';
	}

	public function getPanel()
	{
		return FALSE;
	}

	public function getTab()
	{
		$ideKey = $this->ideKey;
		ob_start();
		require dirname(__FILE__) . '/xdebug-helper.tab.phtml';
		return ob_get_clean();
	}

}
