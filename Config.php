<?php namespace Model\Dashboard;

use Model\Core\Module_Config;

class Config extends Module_Config
{
//	public $configurable = true;

	/**
	 * @throws \Model\Core\Exception
	 */
	protected function assetsList()
	{
		$this->addAsset('config', 'config.php', function () {
			return '<?php
$config = [
	\'title\' => APP_NAME,
	\'cards\' => [],
];
';
		});
	}
}
