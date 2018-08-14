<?php namespace {namespace};

use Model\Dashboard\Card;

class {name} extends Card
{
	public function render(array $options)
	{
		$options = array_merge([
			/* Your options */
		], $options);
		$options = $this->getBasicOptions($options);
		?>
		<div class="card-body"></div>
		<?php
	}
}
