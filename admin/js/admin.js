document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';

            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

});