<?php namespace Model\Dashboard;

use Model\Core\Module_Config;

class Config extends Module_Config
{
//	public bool $configurable = true;

	/**
	 * @throws \Model\Core\Exception
	 */
	protected function assetsList(): void
	{
		$this->addAsset('config', 'config.php', function () {
			return '<?php
$config = [
	\'title\' => APP_NAME,
	\'configurable\' => true,
	\'cards\' => [],
	\'default\' => [],
	\'filters\' => [],
];
';
		});
		$this->addAsset('app-data');
	}

	public function getConfigData(): ?array
	{
		return [];
	}
}
