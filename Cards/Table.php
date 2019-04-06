<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class Table extends Card
{
	public function render(array $options)
	{
		$options = array_merge([
			'where' => [],
			'columns' => [],
			'limit' => 5,
			'order_by' => 'id DESC',
			'group_by' => false,
			'having' => [],
			'sum' => [],
			'max' => [],
		], $options);

		$options = $this->getBasicOptions($options);

		$list = $this->model->_ORM->all($options['element'], $options['where'], [
			'table' => $options['table'],
			'limit' => $options['limit'],
			'order_by' => $options['order_by'],
			'group_by' => $options['group_by'],
			'having' => $options['having'],
			'sum' => $options['sum'],
			'max' => $options['max'],
		]);

		$this->renderTitle($options);
		?>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-sm table-striped">
					<thead>
						<tr>
							<?php
							foreach ($options['columns'] as $k => $c) {
								$label = (is_numeric($k) and is_string($c)) ? $this->getLabel($c) : $k;
								?>
								<th scope="col"><?= entities($label) ?></th>
								<?php
							}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($list as $element) {
							$form = $element->getForm();
							?>
							<tr<?php if ($options['rule']) { ?> onclick="loadElement('<?= $options['rule'] ?>', '<?= $element['id'] ?>'); return false"<?php } ?>>
								<?php
								foreach ($options['columns'] as $k => $c) {
									if (!is_string($c) and is_callable($c)) {
										$text = call_user_func($c, $element);
									} else {
										$text = isset($form[$c]) ? entities($form[$c]->getText()) : '';
									}
									?>
									<td><?= $text ?></td>
									<?php
								}
								?>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
			$this->renderListLink($options);
			?>
		</div>
		<?php
	}
}
