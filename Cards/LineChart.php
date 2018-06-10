<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class LineChart extends Card
{
	public function render(array $options)
	{
		$options = array_merge([
			'title' => null,
			'data' => null,
			'where' => [],
			'limit' => null,
			'order_by' => null,
			'group_by' => false,
			'having' => [],
			'sum' => [],
			'max' => [],
		], $options);

		$options = $this->getBasicOptions($options);

		if ($options['data']) {
			$list = is_callable($options['data']) ? $options['data']() : $options['data'];
		} else {
			if ($options['group_by']) {
				if (!isset($options['label']))
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
		}

		$this->renderTitle($options);
		?>
		<div class="card-body">
			<?php
			$options['id'] = 'dashboard-chart-' . $this->idx;
			$this->model->_C3->lineChart($list, $options);
			$this->renderListLink($options);
			?>
		</div>
		<?php
	}
}
