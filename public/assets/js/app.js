function confirmDelete(url) {
    if (confirm("Are you sure you want to delete this item?")) {
        window.location.href = url;
    }
}

function createSearchableDropdown(selectElement) {
    const wrapper = document.createElement("div");
    wrapper.classList.add("position-relative");

    const input = document.createElement("input");
    input.type = "text";
    input.className = "form-control mb-1";
    input.placeholder = "Search barangay...";

    const list = document.createElement("div");
    list.className = "list-group position-absolute w-100";
    list.style.zIndex = "1000";
    list.style.maxHeight = "200px";
    list.style.overflowY = "auto";
    list.hidden = true;

    // Insert before the select
    selectElement.parentNode.insertBefore(wrapper, selectElement);
    wrapper.appendChild(input);
    wrapper.appendChild(list);
    wrapper.appendChild(selectElement);

    selectElement.style.display = "none";

    async function search(q) {
        const url = "/WEBSYS_FINAL_PROJECT/public/?route=ajax/search_barangay&q=" + encodeURIComponent(q);
        const res = await fetch(url);
        const items = await res.json();

        list.innerHTML = "";

        if (items.length === 0) {
            list.innerHTML = "<div class='list-group-item disabled'>No results</div>";
            list.hidden = false;
            return;
        }

        items.forEach(b => {
            const item = document.createElement("button");
            item.type = "button";
            item.className = "list-group-item list-group-item-action";
            item.textContent = b;

            item.addEventListener("click", () => {
                selectElement.value = b;
                input.value = b;
                list.hidden = true;
            });

            list.appendChild(item);
        });

        list.hidden = false;
    }

    input.addEventListener("input", () => {
        const q = input.value.trim();
        search(q);
    });

    input.addEventListener("focus", () => {
        search(input.value.trim());
    });

    document.addEventListener("click", (e) => {
        if (!wrapper.contains(e.target)) list.hidden = true;
    });
}
