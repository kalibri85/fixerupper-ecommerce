document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            document.getElementById('edit-form-' + id).style.display = 'block';
        });
    });

    document.querySelectorAll(".cancel-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            document.getElementById('edit-form-' + id).style.display = 'none';
        });
    });

});
tinymce.init({
    selector: '#description',
    branding: false,
    promotion: false
});