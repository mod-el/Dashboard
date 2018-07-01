<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class PieChart extends Card
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

			'chart-module' => 'Highcharts',
			'field' => null,
			'label' => null,
			'label-type' => null, // supported at the moment: datetime
			'values-type' => null, // supported at the moment: price
		], $options);

		$options = $this->getBasicOptions($options);

		$list = $this->model->_Dashboard->getListForCharting($options);

		$this->renderTitle($options);

		$chartModule = $this->model->getModule($options['chart-module']);
		?>
		<div class="card-body">
			<?php
			$options['id'] = 'dashboard-chart-' . $this->idx;
			$chartModule->pieChart($list, $options);
			$this->renderListLink($options);
			?>
		</div>
		<?php
	}
}
