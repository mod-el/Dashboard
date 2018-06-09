<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class LineChart extends Card
{
	public function render(array $options)
	{
		$options = array_merge([
			'title' => null,
			'where' => [],
			'limit' => null,
			'order_by' => null,
			'group_by' => false,
			'having' => [],
			'sum' => [],
			'max' => [],
			'fields' => [],
			'label' => null,
			'label-type' => null, // supported at the moment: timeseries
			'values-type' => null, // supported at the moment: price
		], $options);

		$options = $this->getBasicOptions($options);

		if ($options['group_by']) {
			if (!$options['label'])
				$options['label'] = $options['group_by'];
			if (!$options['order_by'])
				$options['order_by'] = $options['group_by'];
		}

		$list = $this->model->_Db->select_all($options['table'], $options['where'], [
			'limit' => $options['limit'],
			'order_by' => $options['order_by'],
			'group_by' => $options['group_by'],
			'having' => $options['having'],
			'sum' => $options['sum'],
			'max' => $options['max'],
		]);

		$chartColumns = [
			['x'],
		];
		foreach ($options['fields'] as $idx => $f) {
			$chartColumns[$idx + 1] = [
				$this->getLabel($f),
			];
		}

		foreach ($list as $elIdx => $el) {
			$chartColumns[0][] = $options['label'] ? $el[$options['label']] : $elIdx;
			foreach ($options['fields'] as $idx => $f)
				$chartColumns[$idx + 1][] = $el[$f];
		}

		$this->renderTitle($options);
		?>
		<div class="card-body">
			<div id="dashboard-chart-<?= $this->idx ?>"></div>
			<?php
			$this->renderListLink($options);
			?>
		</div>
		<script>
			<?php
			$chartOptions = [
				'bindto' => '#dashboard-chart-' . $this->idx,
				'data' => [
					'x' => 'x',
					'columns' => $chartColumns,
				],
			];

			switch ($options['label-type']) {
				case 'timeseries':
					if (!isset($options['label-format']))
						$options['label-format'] = '%d/%m/%Y';

					$chartOptions['axis'] = [
						'x' => [
							'type' => $options['label-type'],
							'tick' => [
								'format' => $options['label-format'],
							],
						],
					];
					break;
			}
			?>
			var chartOptions = <?=json_encode($chartOptions, JSON_PRETTY_PRINT)?>;
			<?php
			switch ($options['values-type']) {
			case 'price':
			?>
			chartOptions['tooltip'] = {
				'format': {
					'value': value => {
						return makePrice(value);
					},
				}
			};
			<?php
			break;
			}
			?>
			var chart<?= $this->idx ?> = c3.generate(chartOptions);
		</script>
		<?php
	}

	/**
	 * Converts a field name in a human-readable label
	 *
	 * @param string $k
	 * @return string
	 */
	public function getLabel(string $k): string
	{
		return ucwords(str_replace(array('-', '_'), ' ', $k));
	}
}
