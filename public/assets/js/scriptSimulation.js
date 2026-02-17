document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = window.BASE_URL || '';

    document.querySelectorAll('.btn-valider').forEach(btn => {
        btn.addEventListener('click', async function() {
            const idAchat = this.dataset.id;
            
            if (!confirm('Valider cet achat ?')) return;

            try {
                const response = await fetch(baseUrl + '/achats/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id_achat=${idAchat}`
                });

                const data = await response.json();

                if (data.error) {
                    alert('Erreur : ' + data.error);
                } else {
                    alert('Achat validé !');
                    location.reload();
                }
            } catch (error) {
                alert('Erreur : ' + error.message);
            }
        });
    });

    document.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', async function() {
            const idAchat = this.dataset.id;
            
            if (!confirm('Supprimer cette simulation ?')) return;

            try {
                const response = await fetch(baseUrl + '/achats/delete-simulation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id_achat=${idAchat}`
                });

                const data = await response.json();

                if (data.error) {
                    alert('Erreur : ' + data.error);
                } else {
                    alert('Simulation supprimée!');
                    location.reload();
                }
            } catch (error) {
                alert('Erreur : ' + error.message);
            }
        });
    });
});