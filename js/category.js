document.addEventListener('DOMContentLoaded', function () {

    const sortSelect = document.getElementById('sortSelect');

    if (!sortSelect) return;

    sortSelect.addEventListener('change', function () {

        const url = new URL(window.location.href);

        // update sort param
        if (this.value) {
            url.searchParams.set('sort', this.value);
        } else {
            url.searchParams.delete('sort');
        }

        // reload page with new URL
        window.location.href = url.toString();
    });

});