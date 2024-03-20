document.addEventListener("DOMContentLoaded", function(event) {

    const filterItems   = document.querySelectorAll('.hki-events-list-filters-item');
    const listItems     = document.querySelectorAll('.hki-events-list-item');

    filterItems.forEach(filterEl => {

        filterEl.addEventListener("click", function(event) {

            event.preventDefault();

            let termSlug = filterEl.getAttribute("data-term-slug");

            filterItems.forEach(el => {
                el.classList.remove("selected");
            });

            this.classList.add("selected");

            listItems.forEach(el => {
                if(filterEl.classList.contains('show-all')) {
                    el.classList.remove("hidden");
                }
                else {
                    if(!el.classList.contains(termSlug)) {
                        el.classList.add("hidden");
                    }
                    else {
                        el.classList.remove("hidden");
                    }
                }

            });
        });
    });

});