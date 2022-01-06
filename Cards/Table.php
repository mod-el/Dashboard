<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class Table extends Card
{
	public function render(array $options, array $filters = [])
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

		$columns = $this->model->_Admin->elaborateColumns($options['columns'], $options['table'] ?: null, false);
		?>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-sm table-striped">
					<thead>
						<tr>
							<?php
							$totals = [];
							foreach ($columns as $column) {
								?>
								<th scope="col"><?= entities($column['label']) ?></th>
								<?php
								if ($column['total'] and $column['field'])
									$totals[$column['field']] = 0;
							}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($list as $element) {
							?>
							<tr<?php if ($options['rule']) { ?> onclick="loadAdminElement('<?= $element['id'] ?>', {}, '<?= $options['rule'] ?>'); return false"<?php } ?>>
								<?php
								foreach ($columns as $column) {
									?>
									<td>
										<?php
										$elaborated = $this->model->_Admin->getElementColumn($element, $column);
										if ($column['total'] and $column['field'])
											$totals[$column['field']] += $elaborated['value'];

										echo $column['raw'] ? $elaborated['text'] : entities($elaborated['text']);
										?>
									</td>
									<?php
								}
								?>
							</tr>
							<?php
						}
						?>
					</tbody>
					<?php
					if (!empty($totals)) {
						?>
						<tfoot>
							<tr>
								<?php
								$labelColumnShown = false;
								$labelColumnWidth = 0;
								foreach ($columns as $column) {
									if ($column['total'] and $column['field']) {
										if (!$labelColumnShown) {
											if ($labelColumnWidth > 0) {
												?>
												<th scope="col" colspan="<?= $labelColumnWidth ?>" class="text-right">
													Totali:
												</th>
												<?php
											}

											$labelColumnShown = true;
										}

										?>
										<th scope="col">
											<?php
											if ($column['price'])
												echo makePrice($totals[$column['field']]);
											else
												echo $totals[$column['field']];
											?>
										</th>
										<?php
									} else {
										$labelColumnWidth++;
									}
								}
								?>
							</tr>
						</tfoot>
						<?php
					}
					?>
				</table>
			</div>
			<?php
			$this->renderListLink($options);
			?>
		</div>
		<?php
	}
}
