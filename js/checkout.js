// Form validation
document.addEventListener('DOMContentLoaded', function() {

    const form = document.querySelector('.needs-validation');

    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Delivery price update
    const subtotal     = parseFloat(document.getElementById('checkoutData').dataset.subtotal);
    const deliveryCost = document.getElementById('deliveryCost');
    const grandTotal   = document.getElementById('grandTotal');

    document.querySelectorAll('.delivery-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            const price = parseFloat(radio.dataset.price);
            const total = subtotal + price;
            deliveryCost.textContent = price > 0 ? '£' + price.toFixed(2) : 'Free';
            grandTotal.textContent   = total.toFixed(2);
        });
    });

    // Show / hide new address fields
    const newAddressFields = document.getElementById('newAddressFields');
    const requiredFields   = ['new_fullname', 'new_address', 'new_city', 'new_postcode', 'new_country'];

    document.querySelectorAll('[name="address_choice"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const isNew = radio.value === 'new';
            newAddressFields.classList.toggle('d-none', !isNew);

            newAddressFields.querySelectorAll('input').forEach(input => {
                if (requiredFields.includes(input.name)) {
                    input.required = isNew;
                }
            });
        });
    });

});
