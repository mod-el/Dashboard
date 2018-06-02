<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class Total extends Card
{
	public function render(array $options)
	{
		$options = array_merge([
			'title' => null,
			'admin-page' => null,
			'table' => null,
			'element' => null,
			'where' => [],
		], $options);

		$rule = null;
		if ($options['admin-page']) {
			$options = $this->useAdminPageOptions($options);
			$rule = $this->model->_AdminFront->getRuleForPage($options['admin-page']);
		}

		if (!$options['element'])
			$options['element'] = 'Element';

		$tot = $this->model->_ORM->count($options['element'], $options['where'], [
			'table' => $options['table'],
		])
		?>
		<div class="card-body text-center">
			<?php
			if (isset($options['title'])) {
				?>
				<h5 class="card-title"><?= entities($options['title']) ?></h5>
				<?php
			}
			?>
			<h1 class="card-title"><?= $tot ?></h1>
			<?php
			if ($rule) {
				?>
				<a href="<?= $this->model->_AdminFront->getUrlPrefix() . $rule ?>" onclick="loadAdminPage(['<?= $rule ?>']); return false" class="card-link">Vai alla lista</a>
				<?php
			}
			?>
		</div>
		<?php
	}
}
