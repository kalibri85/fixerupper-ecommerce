document.addEventListener('DOMContentLoaded', () => {

    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    document.querySelectorAll('.delete-btn').forEach(btn => {

        btn.addEventListener('click', () => {

            if (!confirm('Are you sure you want to delete this category?')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'categories.php';

            [['delete', '1'], ['id', btn.dataset.id], ['csrf_token', csrfToken]].forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });

    });

});