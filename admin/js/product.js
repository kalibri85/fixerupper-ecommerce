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

    document.getElementById("general")?.classList.remove("d-none");
    document.getElementById("addAttributeBtn")?.addEventListener("click", addAttribute);

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

                    // Build with createElement
                    select.replaceChildren();
                    const defaultOpt = document.createElement("option");
                    defaultOpt.value = "";
                    defaultOpt.textContent = "Select attribute";
                    select.appendChild(defaultOpt);

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
            container.replaceChildren();

            let attrBlock = document.querySelector(`[data-attr="${attrID}"]`);
            if (!attrBlock) return;

            let select = attrBlock.querySelector(".values");
            let selected = [...select.selectedOptions];

            selected.forEach(opt => {
                let div = document.createElement("div");
                div.className = "border p-2 mb-2";

                // Use createElement instead of innerHTML with user data
                let strong = document.createElement("strong");
                strong.textContent = opt.text; // textContent — safe

                let hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "variation_values[]";
                hiddenInput.value = opt.value;

                let priceInput = document.createElement("input");
                priceInput.type = "number";
                priceInput.step = "0.01";
                priceInput.min = "0";
                priceInput.className = "form-control mb-1";
                priceInput.name = "variation_price[]";
                priceInput.placeholder = "Price override";

                let skuInput = document.createElement("input");
                skuInput.type = "text";
                skuInput.className = "form-control";
                skuInput.name = "variation_sku[]";
                skuInput.placeholder = "SKU";

                div.appendChild(strong);
                div.appendChild(hiddenInput);
                div.appendChild(priceInput);
                div.appendChild(skuInput);
                container.appendChild(div);
            });
        });

        variationSelect.addEventListener("change", function () {
            if (!this.value) return;

            let attrID = this.value;

            fetch("getVariationValues.php?attribute_id=" + attrID)
                .then(res => res.json())
                .then(data => {
                    let container = document.getElementById("variationValues");
                    container.replaceChildren();

                    data.forEach(v => {
                        let div = document.createElement("div");
                        div.className = "border p-2 mb-2";

                        let strong = document.createElement("strong");
                        strong.textContent = v.value;

                        let hiddenInput = document.createElement("input");
                        hiddenInput.type = "hidden";
                        hiddenInput.name = "variation_values[]";
                        hiddenInput.value = v.id;

                        let priceInput = document.createElement("input");
                        priceInput.type = "number";
                        priceInput.name = "variation_price[]";
                        priceInput.className = "form-control mb-1";

                        let skuInput = document.createElement("input");
                        skuInput.type = "text";
                        skuInput.name = "variation_sku[]";
                        skuInput.className = "form-control";

                        div.appendChild(strong);
                        div.appendChild(hiddenInput);
                        div.appendChild(priceInput);
                        div.appendChild(skuInput);
                        container.appendChild(div);
                    });
                });
        });
    }

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

    // Build with createElement instead of innerHTML with data
    let headerDiv = document.createElement("div");
    headerDiv.className = "d-flex justify-content-between";

    let strong = document.createElement("strong");
    strong.textContent = name; // textContent — safe

    let removeBtn = document.createElement("button");
    removeBtn.type = "button";
    removeBtn.className = "btn btn-sm btn-danger remove-attr";
    removeBtn.textContent = "✕";

    headerDiv.appendChild(strong);
    headerDiv.appendChild(removeBtn);

    let valuesSelect = document.createElement("select");
    valuesSelect.multiple = true;
    valuesSelect.name = `attributes[${id}][values][]`;
    valuesSelect.className = "form-control values mt-2";

    block.appendChild(headerDiv);
    block.appendChild(valuesSelect);
    container.appendChild(block);

    fetch("getAttributeValues.php?attribute_id=" + id)
        .then(res => res.json())
        .then(values => {
            values.forEach(v => {
                let opt = document.createElement("option");
                opt.value = v.id;
                opt.textContent = v.value;
                valuesSelect.appendChild(opt);
            });
        });

    let varSelect = document.getElementById("variationSelect");
    if (varSelect) {
        let opt = document.createElement("option");
        opt.value = id;
        opt.textContent = name; 
        varSelect.appendChild(opt);
    }

    removeBtn.addEventListener("click", () => {
        block.remove();
        if (varSelect) {
            [...varSelect.options].forEach(opt => {
                if (opt.value == id) opt.remove();
            });
        }
        document.getElementById("variationValues").replaceChildren();
    });
}

// =========================
// TINYMCE INIT
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