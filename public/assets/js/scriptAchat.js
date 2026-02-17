document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = window.BASE_URL || '';
    let selectedBesoin = null;

    const villeSelect = document.getElementById('id_ville');
    if (villeSelect) {
        villeSelect.addEventListener('change', function(e) {
            const idVille = e.target.value;
            const rows = document.querySelectorAll('.besoin-row');
            
            rows.forEach(row => {
                if (!idVille || row.dataset.idVille == idVille) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    document.querySelectorAll('.btn-acheter').forEach(btn => {
        btn.addEventListener('click', function() {
            selectedBesoin = {
                id: this.dataset.id,
                prix: parseFloat(this.dataset.prix)
            };
            document.getElementById('previewAchat').style.display = 'block';
            document.getElementById('quantite_achetee').focus();
            document.getElementById('quantite_achetee').value = '';
        });
    });

    const quantiteInput = document.getElementById('quantite_achetee');
    if (quantiteInput) {
        quantiteInput.addEventListener('input', function() {
            if (!selectedBesoin) return;
            
            const quantite = parseFloat(this.value) || 0;
            const montantHT = quantite * selectedBesoin.prix;
            const frais = montantHT * 0.10;
            const total = montantHT + frais;

            document.getElementById('montantHT').textContent = montantHT.toFixed(2);
            document.getElementById('montantFrais').textContent = frais.toFixed(2);
            document.getElementById('montantTotal').textContent = total.toFixed(2);
        });
    }

    const formAchat = document.getElementById('formAchat');
    if (formAchat) {
        formAchat.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!selectedBesoin) {
                alert('Sélectionnez un besoin');
                return;
            }

            const quantite = parseFloat(document.getElementById('quantite_achetee').value);
            if (!quantite || quantite <= 0) {
                alert('Quantité invalide');
                return;
            }

            try {
                const response = await fetch(baseUrl + '/achats/simulate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id_besoin_ville=${selectedBesoin.id}&quantite=${quantite}`
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                if (data.error) {
                    alert('Erreur : ' + data.error);
                } else {
                    alert('Simulation créée ! ID : ' + data.id_achat);
                    window.location.href = baseUrl + '/simulation';
                }
            } catch (error) {
                alert('Erreur : ' + error.message);
            }
        });
    }
});