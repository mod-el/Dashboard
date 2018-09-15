<?php namespace Model\Dashboard;

use Model\Core\Autoloader;
use Model\Core\Module;

class Dashboard extends Module
{
	public $cards = [];
	public $layout = [];

	public function init(array $options)
	{
		$config = $this->retrieveConfig();
		$this->cards = $config['cards'];

		if (!$this->model->_User_Admin->logged())
			return;

		$layoutFile = INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Dashboard' . DIRECTORY_SEPARATOR . $this->model->_User_Admin->logged() . '.json';
		if (file_exists($layoutFile)) {
			$this->layout = json_decode(file_get_contents($layoutFile), true);
		} else {
			$this->layout = $config['default'];
			file_put_contents($layoutFile, json_encode($this->layout));
		}

		$dependencies = [];

		foreach ($this->layout as $row) {
			foreach ($row as $col) {
				foreach ($col['cards'] as $cardName) {
					if (!isset($this->cards[$cardName]))
						$this->model->error('No card named "' . $cardName . '"');

					$card = $this->cards[$cardName];

					if (in_array($card['type'], ['LineChart', 'PieChart'])) {
						$chartingModule = $card['options']['chart-module'] ?? 'Highcharts';
						if (!in_array($chartingModule, $dependencies))
							$dependencies[] = $chartingModule;
					}
				}
			}
		}

		foreach ($dependencies as $module)
			$this->model->load($module);
	}

	public function render()
	{
		$totalCards = 0;
		foreach ($this->layout as $row) {
			?>
			<div class="row">
				<?php
				foreach ($row as $col) {
					if (!isset($col['class'], $col['cards']))
						$this->model->error('Invalid dashboard configuration ("class" or "cards" missing)');
					?>
					<div class="<?= entities($col['class']) ?>">
						<?php
						foreach ($col['cards'] as $idx => $cardName) {
							if (!isset($this->cards[$cardName]))
								$this->model->error('No card named "' . $cardName . '"');

							$cardOptions = $this->cards[$cardName];
							if (!isset($cardOptions['type'], $cardOptions['options']))
								$this->model->error('Invalid dashboard configuration ("type" or "options" missing)');
							?>
							<div class="card<?= $idx > 0 ? ' mt-3' : '' ?>">
								<?php
								$className = Autoloader::searchFile('Card', $cardOptions['type']);
								if (!$className)
									$this->model->error('No card type named "' . $cardOptions['type'] . '"');

								$card = new $className($this->model, $totalCards);
								$card->render($cardOptions['options']);

								$totalCards++;
								?>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
	}

	public function getListForCharting(array &$options): iterable
	{
		if (isset($options['fields']) and !is_string($options['fields']) and is_callable($options['fields']))
			$options['fields'] = call_user_func($options['fields']);

		if ($options['data']) {
			return is_callable($options['data']) ? $options['data']() : $options['data'];
		} else {
			if ($options['group_by']) {
				if (!isset($options['label']))
					$options['label'] = $options['group_by'];
				if (!$options['order_by'])
					$options['order_by'] = $options['group_by'];
			}

			$qryOptions = [
				'limit' => $options['limit'],
				'order_by' => $options['order_by'],
				'group_by' => $options['group_by'],
				'having' => $options['having'],
				'sum' => $options['sum'],
				'max' => $options['max'],
			];
			if ($options['element'] and $options['element'] !== 'Element')
				return $this->model->_ORM->all($options['element'], $options['where'], $qryOptions);
			else
				return $this->model->_Db->select_all($options['table'], $options['where'], $qryOptions);
		}
	}
}
