document.addEventListener('DOMContentLoaded', () => {

    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    document.querySelectorAll('.delete-btn').forEach(btn => {

        btn.addEventListener('click', () => {

            if (!confirm('Are you sure you want to delete this category?')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'categories.php';

            form.innerHTML = `
                <input type="hidden" name="delete" value="1">
                <input type="hidden" name="id" value="${btn.dataset.id}">
                <input type="hidden" name="csrf_token" value="${csrfToken}">
            `;

            document.body.appendChild(form);
            form.submit();
        });

    });

});