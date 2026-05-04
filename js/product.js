document.querySelectorAll('.variation-select').forEach(select => {
    select.addEventListener('change', updatePrice);
});

function updatePrice() {
    const priceEl = document.getElementById('productPrice');
    const basePrice = parseFloat(priceEl.dataset.basePrice);
    let selectedPrice = basePrice;

    document.querySelectorAll('.variation-select').forEach(select => {
        const option = select.selectedOptions[0];

        // data-price is set only when variation has a price override
        if (option && option.dataset.price !== '' && option.dataset.price !== undefined) {
            const override = parseFloat(option.dataset.price);
            if (!isNaN(override) && override > 0) {
                selectedPrice = override;
            }
        }
    });

    document.getElementById('productPrice').textContent =
        selectedPrice.toFixed(2);
}

document.querySelectorAll('.qty-plus').forEach(btn => {
  btn.onclick = () => {
    let input = btn.parentNode.querySelector('.qty-input');
    input.value = parseInt(input.value) + 1;
  };
});

document.querySelectorAll('.qty-minus').forEach(btn => {
  btn.onclick = () => {
    let input = btn.parentNode.querySelector('.qty-input');
    if (input.value > 1) input.value--;
  };
});