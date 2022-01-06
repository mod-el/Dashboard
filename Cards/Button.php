<?php namespace Model\Dashboard\Cards;

use Model\Dashboard\Card;

class Button extends Card
{
	public function render(array $options, array $filters = [])
	{
		$options = array_merge([
			'text' => null,
			'icon' => null,
			'fa-icon' => null,
			'url' => null,
			'onclick' => null,
			'blank' => false,
			'get' => null,
			'action' => null,
		], $options);

		$options = $this->getBasicOptions($options);

		$url = '#';
		$onclick = 'return false';

		if ($options['onclick']) {
			$onclick = trim($options['onclick']);
			if (substr($onclick, -1) !== ';')
				$onclick .= ';';
			$onclick .= ' return false';
		} elseif ($options['url']) {
			$url = $options['url'];
			$onclick = null;
		} elseif ($options['rule']) {
			$url = $this->model->_AdminFront->getUrlPrefix() . $options['rule'];
			$request = [$options['rule']];
			if ($options['action']) {
				if (!is_array($options['action']))
					$this->model->error('"action" must be an array');
				$url .= '/' . implode('/', $options['action']);
				$request = array_merge($request, $options['action']);
			}

			$get = '';
			if ($options['get']) {
				$url .= '?' . $options['get'];
				$get = $options['get'];
			}

			$onclick = 'loadAdminPage(' . entities(json_encode(implode('/', $request))) . ', ' . entities(json_encode($get)) . '); return false';
		}
		?>
		<a href="<?= $url ?>"<?= $onclick ? ' onclick="' . $onclick . '"' : '' ?><?= $options['blank'] ? ' target="_blank"' : '' ?> class="card-body text-center">
			<?php
			if ($options['icon']) {
				?>
				<div class="py-2"><img src="<?= $options['icon'] ?>" alt=""/></div>
				<?php
			}
			if ($options['fa-icon']) {
				?>
				<div class="py-2"><i class="<?= $options['fa-icon'] ?>" aria-hidden="true" style="font-size: 3rem"></i>
				</div>
				<?php
			}
			if ($options['text']) {
				?>
				<h5 class="card-title"><?= entities($options['text']) ?></h5>
				<?php
			}
			?>
		</a>
		<?php
	}
}
