<?php namespace Model\Dashboard;

use Model\Core\Autoloader;
use Model\Core\Module;
use Model\Db\Db;

class Dashboard extends Module
{
	public array $cards = [];
	public array $layout = [];

	public function init(array $options)
	{
		$config = $this->retrieveConfig();

		$layoutFile = $this->getLayoutFilePath();
		if ($layoutFile === null)
			return;

		$this->cards = $config['cards'] ?? [];

		if (($config['configurable'] ?? true) and file_exists($layoutFile)) {
			try {
				$this->layout = json_decode(file_get_contents($layoutFile), true, 512, JSON_THROW_ON_ERROR);
			} catch (\JsonException $e) {
				$this->layout = [];
				unlink($layoutFile);
			}
		} else {
			$this->layout = $config['default'] ?? [];
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

	private function getLayoutFilePath(): ?string
	{
		if (!$this->model->isLoaded('User', 'Admin') or !$this->model->_User_Admin->logged())
			return null;

		if (!is_dir(INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Dashboard'))
			mkdir(INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Dashboard', 0777, true);

		return INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Dashboard' . DIRECTORY_SEPARATOR . $this->model->_User_Admin->logged() . '.json';
	}

	public function saveNewLayout(array $layout)
	{
		$layoutFile = $this->getLayoutFilePath();
		if ($layoutFile === null)
			return;

		file_put_contents($layoutFile, json_encode($layout));
		$this->layout = $layout;
	}

	public function render(array $filters = [])
	{
		?>
		<div data-draggable-cont data-draggable-callback="dashboardRowDragged()" id="dashboard-rows-cont">
			<?php
			$cardIdx = 0;
			foreach ($this->layout as $rowIdx => $row) {
				?>
				<div class="relative" data-dashboard-row="<?= $rowIdx ?>">
					<i class="fas fa-arrows-alt-v d-none" data-dashboard-edit="1" data-draggable-grip title="Sposta riga"></i>
					<i class="fas fa-plus-circle d-none" data-dashboard-edit="1" title="Aggiungi colonna" onclick="dashboardAddColumn(this.parentNode); return false"></i>
					<i class="fas fa-minus-circle d-none" data-dashboard-edit="1" title="Elimina riga" onclick="dashboardDeleteRow(this.parentNode); return false"></i>

					<div class="row" data-draggable-cont data-draggable-callback="dashboardColumnDragged(this)">
						<?php
						foreach ($row as $colIdx => $col) {
							if (!isset($col['class'], $col['cards']))
								$this->model->error('Invalid dashboard configuration ("class" or "cards" missing)');
							?>
							<div class="<?= entities($col['class']) ?> p-2 relative" data-dashboard-column="<?= $colIdx ?>">
								<i class="fas fa-arrows-alt-h d-none" data-dashboard-edit="1" data-draggable-grip title="Sposta colonna"></i>
								<?php
								foreach ($col['cards'] as $idx => $cardConfig) {
									if (!isset($this->cards[$cardConfig['card']]))
										continue;

									$cardOptions = $this->cards[$cardConfig['card']];
									if (!isset($cardOptions['type'], $cardOptions['options']))
										$this->model->error('Invalid dashboard configuration ("type" or "options" missing)');
									?>
									<div class="card relative<?= $idx > 0 ? ' mt-3' : '' ?>" data-dashboard-card="<?= $idx ?>">
										<i class="fas fa-trash d-none" data-dashboard-edit="1" onclick="dashboardDeleteCard(this.parentNode)" title="Elimina scheda"></i>
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

								<div class="dashboard-edit-links row no-gutters pt-2 pb-4 d-none" data-dashboard-edit="1">
									<div class="col-6 pr-2">
										<a href="#" onclick="return false">Aggiungi scheda</a>
									</div>
									<div class="col-6 pl-2">
										<a href="#" onclick="dashboardDeleteColumn(this.parentNode.parentNode.parentNode); return false" class="dashboard-delete-link">Elimina colonna</a>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
			?>
			<div class="dashboard-edit-links pt-2 pb-4 d-none" data-dashboard-edit="1">
				<a href="#" onclick="dashboardAddRow(); return false">Aggiungi riga</a>
			</div>
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
				'joins' => $options['joins'],
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
				return Db::getConnection()->selectAll($options['table'], $options['where'], $qryOptions);
		}
	}
}
