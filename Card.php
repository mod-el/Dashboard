<?php namespace Model\Dashboard;

use Model\Admin\AdminPage;
use Model\Core\Autoloader;
use Model\Core\Core;

abstract class Card
{
	/** @var Core */
	protected $model;

	/**
	 * Card constructor.
	 * @param Core $model
	 */
	public function __construct(Core $model)
	{
		$this->model = $model;
	}

	abstract public function render(array $options);

	protected function getBasicOptions(array $options): array
	{
		$options = array_merge([
			'admin-page' => null,
			'rule' => null,
			'table' => null,
			'element' => null,
		], $options);

		if ($options['admin-page']) {
			$options = $this->useAdminPageOptions($options);
			$options['rule'] = $this->model->_AdminFront->getRuleForPage($options['admin-page']);
		}

		if (!$options['element'])
			$options['element'] = 'Element';

		return $options;
	}

	protected function getAdminPage(string $page): AdminPage
	{
		$className = Autoloader::searchFile('AdminPage', $page);
		return new $className($this->model);
	}

	protected function getAdminPageOptions(string $page): array
	{
		$adminPage = $this->getAdminPage($page);

		$options = array_merge([
			'table' => null,
			'element' => null,
			'where' => [],
		], $adminPage->options());

		if ($options['element'] and !$options['table'])
			$options['table'] = $this->model->_ORM->getTableFor($options['element']);

		return $options;
	}

	protected function useAdminPageOptions(array $options): array
	{
		$adminPageOptions = $this->getAdminPageOptions($options['admin-page']);

		if (!$options['element'] and $adminPageOptions['element'])
			$options['element'] = $adminPageOptions['element'];
		if (!$options['table'] and $adminPageOptions['table'])
			$options['table'] = $adminPageOptions['table'];
		if (!$options['where'] and $adminPageOptions['where'])
			$options['where'] = $adminPageOptions['where'];

		return $options;
	}

	protected function renderTitle(array $options)
	{
		if ($options['title'] ?? null) {
			?>
			<div class="card-header text-center" style="font-size: 1.5rem"><?= entities($options['title']) ?></div>
			<?php
		}
	}

	protected function renderListLink(array $options)
	{
		if ($options['rule'] ?? null) {
			?>
			<div class="text-center">
				<a href="<?= $this->model->_AdminFront->getUrlPrefix() . $options['rule'] ?>" onclick="loadAdminPage(['<?= $options['rule'] ?>']); return false" class="card-link">Vai alla lista</a>
			</div>
			<?php
		}
	}

	/**
	 * Converts a field name in a human-readable label
	 *
	 * @param string $k
	 * @return string
	 */
	protected function getLabel(string $k): string
	{
		return ucwords(str_replace(array('-', '_'), ' ', $k));
	}
}
