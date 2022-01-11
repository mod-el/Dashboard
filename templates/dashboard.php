<?php
$config = $this->model->_Dashboard->retrieveConfig();

$filters = [];
$filtersFields = $config['filters'] ?? [];
if ($filtersFields) {
	$filtersForm = new \Model\Form\Form([
		'model' => $this->model,
		'bootstrap' => true,
	]);

	foreach ($filtersFields as $filter_k => $filter)
		$filtersForm->add($filter_k, $filter);

	$filters = json_decode($_GET['filters'] ?? '[]', true);
	if ($filters)
		$filtersForm->setValues($filters);
	else
		$filters = $filtersForm->getValues();
}
?>
<div class="p-3 container-fluid model-dashboard">
	<?php
	if ($config['title']) {
		?>
		<h1 class="text-center"><?= entities($config['title']) ?></h1>
		<?php
	}
	?>
	<div class="py-2 relative">
		<?php
		if ($filtersFields) {
			?>
			<div class="model-dashboard-settings">
				<a href="#" onclick="zkPopup('#filters-popup'); return false"><i class="fab fa-wpforms"></i></a>
			</div>

			<div class="d-none" id="filters-popup">
				<div class="p-2">
					<form action="" method="post" onsubmit="applyDashboardFilters(); return false" class="flex-fields-wrap p-2" id="dashboard-filters-form">
						<?php
						foreach ($filtersFields as $filter_k => $filter) {
							?>
							<div>
								<?= entities($filtersForm[$filter_k]->getLabel()) ?>
								<br/>
								<?php $filtersForm[$filter_k]->render(); ?>
							</div>
							<?php
						}
						?>
						<div>
							&nbsp;<br/>
							<input type="submit" class="btn btn-primary" value="Applica"/>
						</div>
					</form>
				</div>
			</div>
			<?php
		}

		$this->model->_Dashboard->render($filters);
		?>
	</div>
</div>
