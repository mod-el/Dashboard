async function applyDashboardFilters() {
	let form = _('dashboard-filters-form');
	let filters = form ? JSON.stringify(await form.getValues()) : {};
	zkPopupClose();
	await loadAdminPage('', filters ? {filters} : {}, {}, false);
}

function enableDashboardEdit() {
	for (let el of document.querySelectorAll('[data-dashboard-edit]')) {
		if (parseInt(el.getAttribute('data-dashboard-edit')) === 1)
			el.removeClass('d-none');
		else
			el.addClass('d-none');
	}
}

async function confirmDashboardEdit() {
	// TODO: fare vero meccanismo di salvataggio
	for (let el of document.querySelectorAll('[data-dashboard-edit]')) {
		if (parseInt(el.getAttribute('data-dashboard-edit')) === 0)
			el.removeClass('d-none');
		else
			el.addClass('d-none');
	}
}

async function revertDashboardEdit() {
	if (confirm('Sicuro di voler annullare eventuali modifiche?'))
		return applyDashboardFilters(); // Reloads the dashboard
}
