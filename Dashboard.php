<?php namespace Model\Dashboard;

use Model\Core\Autoloader;
use Model\Core\Module;

class Dashboard extends Module
{
	public function init(array $options)
	{
		$config = $this->retrieveConfig();
		$dependencies = [];

		foreach ($config['cards'] as $row) {
			foreach ($row as $col) {
				foreach ($col['cards'] as $card) {
					if (in_array($card['type'], ['LineChart', 'PieChart'])) {
						$chartingModule = $card['chart-module'] ?? 'C3';
						if (!in_array($chartingModule, $dependencies))
							$dependencies[] = $chartingModule;
					}
				}
			}
		}

		foreach ($dependencies as $module)
			$this->model->load($module);
	}

	public function render(array $cards)
	{
		$totalCards = 0;
		foreach ($cards as $row) {
			?>
			<div class="row pb-3">
				<?php
				foreach ($row as $col) {
					if (!isset($col['class'], $col['cards']))
						$this->model->error('Invalid dashboard configuration ("class" or "cards" missing)');
					?>
					<div class="<?= entities($col['class']) ?>">
						<?php
						foreach ($col['cards'] as $idx => $cardOptions) {
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
