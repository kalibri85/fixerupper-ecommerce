document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        const response = await fetch('addToCart.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('cartCount').textContent = data.count;
            button.innerHTML = 'Added ✓';
        }
    });
});