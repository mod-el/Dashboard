var dashboardLayout;

async function applyDashboardFilters() {
	let form = _('dashboard-filters-form');
	let filters = form ? JSON.stringify(await form.getValues()) : {};
	zkPopupClose();
	await loadAdminPage('', filters ? {filters} : {}, {}, false);
}

function enableDashboardEdit() {
	dashboardLayout = JSON.parse(_('original-dashboard-layout').value);

	for (let row of document.querySelectorAll('[data-dashboard-row]'))
		row.style.minHeight = '100px';

	for (let el of document.querySelectorAll('[data-dashboard-edit]')) {
		if (parseInt(el.getAttribute('data-dashboard-edit')) === 1)
			el.removeClass('d-none');
		else
			el.addClass('d-none');
	}
}

async function confirmDashboardEdit() {
	if (!confirm('Salvare le modifiche apportate al layout della dashboard?'))
		return;

	clearMainPage();
	showLoadingMask();

	let response = await ajax(adminPrefix + 'save-dashboard-layout', {}, {layout: JSON.stringify(dashboardLayout)}, {
		'headers': {
			'X-Access-Token': adminApiToken
		}
	});

	if (!response.success)
		alert(response);

	document.location.reload();
}

async function revertDashboardEdit() {
	if (confirm('Sicuro di voler annullare eventuali modifiche?'))
		return applyDashboardFilters(); // Reloads the dashboard
}

function dashboardRowDragged() {
	let oldDashboardLayout = JSON.parse(JSON.stringify(dashboardLayout));
	dashboardLayout = [];

	for (let row of document.querySelectorAll('.model-dashboard [data-dashboard-row]')) {
		let idx = parseInt(row.getAttribute('data-dashboard-row'));
		row.setAttribute('data-dashboard-row', dashboardLayout.length.toString());
		dashboardLayout.push(oldDashboardLayout[idx]);
	}
}

function dashboardColumnDragged(col) {
	let row = col.parentNode.parentNode;
	let rowIdx = parseInt(row.getAttribute('data-dashboard-row'));

	let oldDashboardLayout = JSON.parse(JSON.stringify(dashboardLayout));
	dashboardLayout[rowIdx] = [];

	for (let col of document.querySelectorAll('.model-dashboard [data-dashboard-row="' + rowIdx + '"] [data-dashboard-column]')) {
		let idx = parseInt(col.getAttribute('data-dashboard-column'));
		col.setAttribute('data-dashboard-column', dashboardLayout[rowIdx].length.toString());
		dashboardLayout[rowIdx].push(oldDashboardLayout[rowIdx][idx]);
	}
}

function dashboardDeleteCard(card) {
	if (!confirm('Sicuro di voler eliminare?'))
		return;

	let cardIdx = parseInt(card.getAttribute('data-dashboard-card'));
	let col = card.parentNode;
	let colIdx = parseInt(col.getAttribute('data-dashboard-column'));
	let row = col.parentNode.parentNode;
	let rowIdx = parseInt(row.getAttribute('data-dashboard-row'));

	dashboardLayout[rowIdx][colIdx].cards.splice(cardIdx, 1);
	card.remove();

	let newCardIdx = 0;
	for (let _card of col.querySelectorAll('[data-dashboard-card]'))
		_card.setAttribute('data-dashboard-card', (newCardIdx++).toString());
}

function dashboardDeleteColumn(col) {
	if (!confirm('Sicuro di voler eliminare?'))
		return;

	let colIdx = parseInt(col.getAttribute('data-dashboard-column'));
	let row = col.parentNode.parentNode;
	let rowIdx = parseInt(row.getAttribute('data-dashboard-row'));

	dashboardLayout[rowIdx].splice(colIdx, 1);
	col.remove();

	let newColIdx = 0;
	for (let _col of row.querySelectorAll('[data-dashboard-column]'))
		_col.setAttribute('data-dashboard-column', (newColIdx++).toString());
}

function dashboardDeleteRow(row) {
	if (!confirm('Sicuro di voler eliminare?'))
		return;

	let rowIdx = parseInt(row.getAttribute('data-dashboard-row'));

	dashboardLayout.splice(rowIdx, 1);
	row.remove();

	let newRowIdx = 0;
	for (let _row of document.querySelectorAll('[data-dashboard-row]'))
		_row.setAttribute('data-dashboard-row', (newRowIdx++).toString());
}

function dashboardAddRow() {
	let cont = _('dashboard-rows-cont');

	let newRow = document.createElement('div');
	newRow.className = 'relative';
	newRow.setAttribute('data-dashboard-row', dashboardLayout.length);
	newRow.style.minHeight = '100px';
	newRow.innerHTML = `<i class="fas fa-arrows-alt-v" data-dashboard-edit="1" data-draggable-grip title="Sposta riga"></i>
		<i class="fas fa-plus-circle" data-dashboard-edit="1" title="Aggiungi colonna"></i>
		<i class="fas fa-minus-circle" data-dashboard-edit="1" title="Elimina riga" onclick="dashboardDeleteRow(this.parentNode); return false"></i>
		<div class="row" data-draggable-cont data-draggable-callback="dashboardColumnDragged(this)"></div>`;

	cont.insertBefore(newRow, cont.lastElementChild);

	checkDraggables();

	dashboardLayout.push([]);
}

function dashboardAddColumn(row) {
	let rowIdx = parseInt(row.getAttribute('data-dashboard-row'));
	let colIdx = dashboardLayout[rowIdx].length;

	dashboardLayout[rowIdx].push({class: 'col p-2', cards: []});

	let newColumn = document.createElement('div');
	newColumn.className = 'col p-2 relative';
	newColumn.setAttribute('data-dashboard-column', colIdx);
	newColumn.innerHTML = `<i class="fas fa-arrows-alt-h" data-dashboard-edit="1" data-draggable-grip title="Sposta colonna"></i>
		<div class="dashboard-edit-links row no-gutters pt-2 pb-4" data-dashboard-edit="1">
			<div class="col-6 pr-2">
				<a href="#" onclick="return false">Aggiungi scheda</a>
			</div>
			<div class="col-6 pl-2">
				<a href="#" onclick="dashboardDeleteColumn(this.parentNode.parentNode.parentNode); return false" class="dashboard-delete-link">Elimina colonna</a>
			</div>
		</div>`;

	row.querySelector('.row').appendChild(newColumn);

	checkDraggables();
}
