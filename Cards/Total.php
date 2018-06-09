<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class Total extends Card
{
	public function render(array $options)
	{
		$options = array_merge([
			'title' => null,
			'text1' => null,
			'text2' => null,
			'where' => [],
		], $options);

		$options = $this->getBasicOptions($options);

		$tot = $this->model->_ORM->count($options['element'], $options['where'], [
			'table' => $options['table'],
		]);

		if ($options['title']) {
			?>
			<div class="card-header text-center" style="font-size: 1.5rem"><?= entities($options['title']) ?></div>
			<?php
		}
		?>
		<div class="card-body text-center">
			<?php
			if (isset($options['text1'])) {
				?>
				<h5 class="card-title"><?= entities($options['text1']) ?></h5>
				<?php
			}
			?>
			<h1 class="card-title"><?= $tot ?></h1>
			<?php
			if (isset($options['text2'])) {
				?>
				<h5 class="card-title"><?= entities($options['text2']) ?></h5>
				<?php
			}
			if ($options['rule']) {
				?>
				<a href="<?= $this->model->_AdminFront->getUrlPrefix() . $options['rule'] ?>" onclick="loadAdminPage(['<?= $options['rule'] ?>']); return false" class="card-link">Vai alla lista</a>
				<?php
			}
			?>
		</div>
		<?php
	}
}
