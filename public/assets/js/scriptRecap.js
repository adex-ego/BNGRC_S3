document.addEventListener('DOMContentLoaded', function() {
    actualiserRecap();
});

async function actualiserRecap() {
    try {
        const baseUrl = window.BASE_URL || '';
        const response = await fetch(baseUrl + '/api/recap');
        const data = await response.json();

        document.getElementById('stat-total').textContent = parseFloat(data.total_besoins).toFixed(2);
        document.getElementById('stat-satisfaits').textContent = parseFloat(data.besoins_satisfaits).toFixed(2);
        document.getElementById('stat-restants').textContent = parseFloat(data.besoins_restants).toFixed(2);
    } catch (error) {
        console.error('Erreur lors de l\'actualisation:', error);
    }
}