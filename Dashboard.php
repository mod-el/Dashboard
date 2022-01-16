<?php namespace Model\Dashboard;

use Model\Core\Autoloader;
use Model\Core\Module;

class Dashboard extends Module
{
	public array $cards = [];
	public array $layout = [];

	public function init(array $options)
	{
		$config = $this->retrieveConfig();

		$this->cards = $config['cards'] ?? [];

		if (!$this->model->isLoaded('User', 'Admin') or !$this->model->_User_Admin->logged())
			return;

		if (!is_dir(INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Dashboard'))
			mkdir(INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Dashboard', 0777, true);

		$layoutFile = INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Dashboard' . DIRECTORY_SEPARATOR . $this->model->_User_Admin->logged() . '.json';
		if (file_exists($layoutFile)) {
			$this->layout = json_decode(file_get_contents($layoutFile), true);
			if ($this->layout === null) {
				$this->layout = [];
				unlink($layoutFile);
			}
		} else {
			$this->layout = $config['default'] ?? [];

			foreach ($this->layout as &$row) {
				foreach ($row as &$cell) {
					foreach ($cell['cards'] as &$card) {
						$card = [
							'card' => $card,
						];
					}
					unset($card);
				}
				unset($cell);
			}
			unset($row);

			file_put_contents($layoutFile, json_encode($this->layout));
		}

		$dependencies = [];
		foreach ($this->layout as $row) {
			foreach ($row as $col) {
				foreach ($col['cards'] as $cardConfig) {
					if (!isset($this->cards[$cardConfig['card']]))
						continue;

					$card = $this->cards[$cardConfig['card']];

					if (in_array($card['type'], ['LineChart', 'PieChart', 'AreaChart', 'StackedBarChart'])) {
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

	public function render(array $filters = [])
	{
		?>
		<div data-draggable-cont>
			<?php
			$cardIdx = 0;
			foreach ($this->layout as $row) {
				?>
				<div class="relative">
					<i class="fas fa-arrows-alt-v d-none" data-dashboard-edit="1" data-draggable-grip></i>

					<div class="row" data-draggable-cont>
						<?php
						foreach ($row as $col) {
							if (!isset($col['class'], $col['cards']))
								$this->model->error('Invalid dashboard configuration ("class" or "cards" missing)');
							?>
							<div class="<?= entities($col['class']) ?> relative">
								<i class="fas fa-arrows-alt-h d-none" data-dashboard-edit="1" data-draggable-grip></i>
								<?php
								foreach ($col['cards'] as $idx => $cardConfig) {
									if (!isset($this->cards[$cardConfig['card']]))
										continue;

									$cardOptions = $this->cards[$cardConfig['card']];
									if (!isset($cardOptions['type'], $cardOptions['options']))
										$this->model->error('Invalid dashboard configuration ("type" or "options" missing)');
									?>
									<div class="card<?= $idx > 0 ? ' mt-3' : '' ?>">
										<?php
										$className = Autoloader::searchFile('Card', $cardOptions['type']);
										if (!$className)
											$this->model->error('No card type named "' . $cardOptions['type'] . '"');

										$card = new $className($this->model, $cardIdx);
										$card->render($cardOptions['options'], $filters);

										$cardIdx++;
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
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	public function getListForCharting(array &$options, array $filters = []): iterable
	{
		if (isset($options['fields']) and !is_string($options['fields']) and is_callable($options['fields']))
			$options['fields'] = call_user_func($options['fields']);

		if ($options['data']) {
			return is_callable($options['data']) ? $options['data']($filters) : $options['data'];
		} else {
			if ($options['group_by']) {
				if (!isset($options['label']))
					$options['label'] = $options['group_by'];
				if (!$options['order_by'])
					$options['order_by'] = $options['group_by'];
			}

			if ($filters and !empty($options['elaborate_filters']) and is_callable($options['elaborate_filters']))
				$options['where'] = $options['elaborate_filters']($options['where'], $filters);

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
