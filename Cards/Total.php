<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class Total extends Card
{
	public function render(array $options)
	{
		$options = array_merge([
			'text1' => null,
			'text2' => null,
			'where' => [],
		], $options);

		$options = $this->getBasicOptions($options);

		$tot = $this->model->_ORM->count($options['element'], $options['where'], [
			'table' => $options['table'],
		]);

		$this->renderTitle($options);
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
			$this->renderListLink($options);
			?>
		</div>
		<?php
	}
}
