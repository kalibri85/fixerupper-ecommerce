document.addEventListener("DOMContentLoaded", () => {

    // =========================
    // TABS
    // =========================
    const tabs = document.querySelectorAll(".tab-link");
    const panes = document.querySelectorAll(".tab-pane-custom");

    tabs.forEach(tab => {
        tab.addEventListener("click", e => {
            e.preventDefault();

            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            panes.forEach(p => p.classList.add("d-none"));

            const target = document.getElementById(tab.dataset.tab);
            if (target) target.classList.remove("d-none");
        });
    });

    // Open general
    document.getElementById("general")?.classList.remove("d-none");
    document.getElementById("addAttributeBtn") ?.addEventListener("click", addAttribute);
    // Remove
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("remove-attr")) {
            e.target.closest("[data-attr]").remove();
        }
    });
    // =========================
    // CATEGORY → LOAD ATTRIBUTES
    // =========================
    const categorySelect = document.getElementById("categorySelect");

    if (categorySelect) {
        categorySelect.addEventListener("change", function () {

            fetch("getAttributes.php?category_id=" + this.value)
                .then(res => res.json())
                .then(data => {

                    let select = document.getElementById("attributeSelect");
                    if (!select) return;

                    select.innerHTML = '<option value="">Select attribute</option>';

                    data.forEach(attr => {
                        let opt = document.createElement("option");
                        opt.value = attr.id;
                        opt.textContent = attr.name;
                        select.appendChild(opt);
                    });

                });
        });
    }
    // =========================
    // VARIATION LOGIC
    // =========================
    const variationSelect = document.getElementById("variationSelect");

    if (variationSelect) {
        variationSelect.addEventListener("change", function () {

            if (!this.value) {
                alert("Select attribute first");
                return;
            }
            let attrID = this.value;
            let container = document.getElementById("variationValues");
            container.innerHTML = "";

            let attrBlock = document.querySelector(`[data-attr="${attrID}"]`);
            if (!attrBlock) return;

            let select = attrBlock.querySelector(".values");

            let selected = [...select.selectedOptions];

            selected.forEach(opt => {

                let div = document.createElement("div");
                div.className = "border p-2 mb-2";

                div.innerHTML = `
                    <strong>${opt.text}</strong>
                    <input type="hidden" name="variation_values[]" value="${opt.value}">
                    <input type="number" step="0.01" min="0" class="form-control mb-1" name="variation_price[]" placeholder="Price override">
                    <input type="text" class="form-control" name="variation_sku[]" placeholder="SKU">
                `;

                container.appendChild(div);

            });

        });
    }
variationSelect.addEventListener("change", function () {

    if (!this.value) return;

    let attrID = this.value;

    fetch("getVariationValues.php?attribute_id=" + attrID)
        .then(res => res.json())
        .then(data => {

            let container = document.getElementById("variationValues");
            container.innerHTML = "";

            data.forEach(v => {

                container.innerHTML += `
                    <div class="border p-2 mb-2">
                        <strong>${v.value}</strong>
                        <input type="hidden" name="variation_values[]" value="${v.id}">
                        <input type="number" name="variation_price[]" class="form-control mb-1">
                        <input type="text" name="variation_sku[]" class="form-control">
                    </div>
                `;
            });
        });
});
    // =========================
    // INIT TINYMCE
    // =========================
    initEditor();
});
// =========================
// ADD ATTRIBUTE
// =========================
function addAttribute() {

    let select = document.getElementById("attributeSelect");
    let id = select.value;
    let name = select.options[select.selectedIndex]?.text;

    if (!id) return;

    if (document.querySelector(`[data-attr="${id}"]`)) {
        alert("Attribute already added");
        return;
    }

    let container = document.getElementById("selectedAttributes");

    let block = document.createElement("div");
    block.className = "border p-2 mb-2";
    block.setAttribute("data-attr", id);

    block.innerHTML = `
        <div class="d-flex justify-content-between">
            <strong>${name}</strong>
            <button type="button" class="btn btn-sm btn-danger remove-attr">✕</button>
        </div>

        <select multiple name="attributes[${id}][values][]" class="form-control values mt-2"></select>
    `;

    container.appendChild(block);

    fetch("getAttributeValues.php?attribute_id=" + id)
        .then(res => res.json())
        .then(values => {

            let valuesSelect = block.querySelector(".values");

            values.forEach(v => {
                let opt = document.createElement("option");
                opt.value = v.id;
                opt.textContent = v.value;
                valuesSelect.appendChild(opt);
            });

        });

    let varSelect = document.getElementById("variationSelect");
    if (varSelect) {
        varSelect.innerHTML += `<option value="${id}">${name}</option>`;
    }

    block.querySelector(".remove-attr").addEventListener("click", () => {

        block.remove();

        if (varSelect) {
            [...varSelect.options].forEach(opt => {
                if (opt.value == id) opt.remove();
            });
        }

        document.getElementById("variationValues").innerHTML = "";
    });
}
// =========================
// TINYMCE INIT (CLEAN VERSION)
// =========================
function initEditor() {

    if (typeof tinymce === "undefined") return;

  
    if (tinymce.get("description")) return;

    tinymce.init({
        selector: "#description",
        license_key: 'gpl',

        height: 300,
        menubar: false,
        branding: false,
        promotion: false,

        plugins: "lists link image table code",
        toolbar: "undo redo | formatselect | bold italic underline | bullist numlist | link image | code",

        setup: function (editor) {

            editor.on("init", () => {
                console.log("TinyMCE ready");
            });
        }
    });
}