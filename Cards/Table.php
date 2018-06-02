<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class Table extends Card
{
	public function render(array $options)
	{
		$options = array_merge([
			'title' => null,
			'admin-page' => null,
			'table' => null,
			'element' => null,
			'where' => [],
			'columns' => [],
			'limit' => 5,
			'order_by' => 'id DESC',
		], $options);

		$rule = null;
		if ($options['admin-page']) {
			$options = $this->useAdminPageOptions($options);
			$rule = $this->model->_AdminFront->getRuleForPage($options['admin-page']);
		}

		if (!$options['element'])
			$options['element'] = 'Element';

		$list = $this->model->_ORM->all($options['element'], $options['where'], [
			'table' => $options['table'],
			'limit' => $options['limit'],
			'order_by' => $options['order_by'],
		]);

		if ($options['title']) {
			?>
			<div class="card-header text-center" style="font-size: 1.5rem"><?= entities($options['title']) ?></div>
			<?php
		}
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
							<tr<?php if ($rule) { ?> onclick="loadAdminPage(['<?= $rule ?>', 'edit', '<?= $element['id'] ?>']); return false"<?php } ?>>
								<?php
								foreach ($options['columns'] as $k => $c) {
									if (!is_string($c) and is_callable($c)) {
										$text = call_user_func($c, $element);
									} else {
										$text = isset($form[$c]) ? $form[$c]->getText() : '';
									}
									?>
									<td><?= entities($text) ?></td>
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
			if ($rule) {
				?>
				<div class="text-center">
					<a href="<?= $this->model->_AdminFront->getUrlPrefix() . $rule ?>" onclick="loadAdminPage(['<?= $rule ?>']); return false" class="card-link">Vai alla lista</a>
				</div>
				<?php
			}
			?>
		</div>
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
