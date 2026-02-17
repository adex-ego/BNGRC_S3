document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('id_besoin_type');
    const itemSelect = document.getElementById('id_besoin_item');

    const filterItems = () => {
        if (!itemSelect) {
            return;
        }
        const selectedType = typeSelect ? typeSelect.value : '';
        const options = Array.from(itemSelect.options);
        let hasVisible = false;

        options.forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }
            const typeId = option.getAttribute('data-type-id');
            const visible = !selectedType || typeId === selectedType;
            option.hidden = !visible;
            option.disabled = !visible;
            if (visible) {
                hasVisible = true;
            }
        });

        if (!hasVisible || (itemSelect.selectedOptions[0] && itemSelect.selectedOptions[0].hidden)) {
            itemSelect.value = '';
        }
    };

    if (typeSelect) {
        typeSelect.addEventListener('change', filterItems);
        filterItems();
    }
});