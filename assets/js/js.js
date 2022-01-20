var dashboardLayout;

async function applyDashboardFilters() {
	let form = _('dashboard-filters-form');
	let filters = form ? JSON.stringify(await form.getValues()) : {};
	zkPopupClose();
	await loadAdminPage('', filters ? {filters} : {}, {}, false);
}

function enableDashboardEdit() {
	dashboardLayout = JSON.parse(_('original-dashboard-layout').value);

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
		_col.setAttribute('data-dashboard-col', (newColIdx++).toString());
}
