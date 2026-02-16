const villeSearch = document.getElementById('villeSearch');
const regionFilter = document.getElementById('regionFilter');
const table = document.getElementById('villesTable');

const filterRows = () => {
    if (!table) return;
    const query = (villeSearch?.value || '').toLowerCase();
    const region = regionFilter?.value || '';
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach((row) => {
        const nom = (row.dataset.nom || '').toLowerCase();
        const rowRegion = row.dataset.region || '';
        const matchNom = !query || nom.includes(query);
        const matchRegion = !region || rowRegion === region;
        row.style.display = matchNom && matchRegion ? '' : 'none';
    });
};

if (villeSearch) {
    villeSearch.addEventListener('input', filterRows);
}
if (regionFilter) {
    regionFilter.addEventListener('change', filterRows);
}
