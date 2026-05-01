document.querySelectorAll('.variation-select').forEach(select => {
    select.addEventListener('change', updatePrice);
});

function updatePrice() {
    let basePrice = parseFloat(window.basePrice);

    let selectedPrice = basePrice;

    document.querySelectorAll('.variation-select').forEach(select => {
        let option = select.selectedOptions[0];

        if (option && option.dataset.price) {
            let override = parseFloat(option.dataset.price);
            if (!isNaN(override)) {
                selectedPrice = override;
            }
        }
    });

    document.getElementById('productPrice').innerText =
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