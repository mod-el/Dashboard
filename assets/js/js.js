async function applyDashboardFilters() {
	let form = _('dashboard-filters-form');
	let filters = JSON.stringify(await form.getValues());
	zkPopupClose();
	await loadAdminPage('', {filters}, {}, false);
}
