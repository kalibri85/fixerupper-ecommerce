document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.remove-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            const confirmed = confirm(
                'Are you sure you want to remove this item from your cart?'
            );

            if (!confirmed) {
                e.preventDefault();
            }
        });
    });
});