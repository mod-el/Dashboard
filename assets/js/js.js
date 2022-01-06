async function applyDashboardFilters() {
	let form = _('dashboard-filters-form');
	let filters = JSON.stringify(await form.getValues());
	await loadAdminPage('', {filters}, {}, false);
}
