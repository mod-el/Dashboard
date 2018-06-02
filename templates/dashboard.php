<?php
$config = $this->model->_Dashboard->retrieveConfig();
?>
<style>
	.table tbody tr:hover td {
		background-color: #F9F9F9 !important;
		cursor: pointer;
	}
</style>

<div class="p-3 container-fluid">
	<h1 class="text-center"><?= entities($config['title']) ?></h1>
	<div class="py-2">
		<?php
		$this->model->_Dashboard->render($config['cards']);
		?>
	</div>
</div>