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
<input type="hidden" id="original-dashboard-layout" value="<?= entities(json_encode($this->model->_Dashboard->layout)) ?>"/>

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
		if (($config['configurable'] ?? true) or $filtersFields) {
			?>
			<div class="model-dashboard-settings">
				<?php
				if ($config['configurable'] ?? true) {
					?>
					<a href="#" onclick="enableDashboardEdit(); return false" data-dashboard-edit="0"><i class="fas fa-cog"></i></a>
					<a href="#" onclick="revertDashboardEdit(); return false" data-dashboard-edit="1" class="d-none"><i class="fas fa-undo"></i></a>
					<a href="#" onclick="confirmDashboardEdit(); return false" data-dashboard-edit="1" class="d-none"><i class="fas fa-check-circle"></i></a>
					<?php
				}

				if ($filtersFields) {
					?>
					<a href="#" onclick="zkPopup('#filters-popup'); return false"><i class="fab fa-wpforms"></i></a>
					<?php
				}
				?>
			</div>
			<?php
		}

		if ($filtersFields) {
			?>
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
		?>
		<div style="height: 30px" class="d-none" data-dashboard-edit="1"></div>
		<?php
		$this->model->_Dashboard->render($filters);
		?>
	</div>
</div>
