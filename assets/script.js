document.addEventListener("DOMContentLoaded", function(event) {

    const eventList     = document.querySelector('.hki-events-list');
    const filterItems   = document.querySelectorAll('.hki-events-list-filters-item');
    const loader        = document.querySelector('#loader');
    const loadMoreBtn   = document.querySelector('#loadmore-btn');
    const backToTopBtn  = document.querySelector('#back-to-top-btn');
    const fallBackImg   = 'https://i.imgur.com/XBaFPUf.png';

    let termId;
    let currentPage = 2;
    let loading = false;

    filterItems.forEach(filterEl => {

        filterEl.addEventListener("click", function(event) {

            event.preventDefault();

            if(loading) {
                return;
            }

            loadMoreBtn.style.display = "none";

            if(termId === filterEl.getAttribute("data-term-id")) {
                return;
            }

            termId = filterEl.getAttribute("data-term-id");

            filterItems.forEach(el => {
                el.classList.remove("selected");
            });

            this.classList.add("selected");

            currentPage = 1;
            eventList.innerHTML = '';
            fetchEvents(termId);

        });
    });

    loadMoreBtn.addEventListener("click", async () => {
        fetchEvents(termId);
    });

    const fetchEvents = async (termId) => {

        loading = true;
        loadMoreBtn.disabled = true;
        backToTopBtn.style.display = "none";
        loader.style.display = "block";
        let termStr = termId ? `&hki_event_tag=${termId}` : '';

        let url = `/wp-json/wp/v2/hki_event?per_page=9&page=${currentPage}${termStr}`;

        try {
            const response = await fetch(url);
            const result = await response.json();
            const total = response.headers.get('X-WP-Total');

            if(result.length) {

                result.forEach((item, index) => {
                    appendToList(item, index);
                });

                if(document.querySelectorAll('.hki-events-list-item').length >= total) {
                    loader.style.display = "none";
                    loadMoreBtn.style.display = "none";
                }
                else {
                    loader.style.display = "none";
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.style.display = "block";
                    currentPage++;
                }
                if(document.querySelectorAll('.hki-events-list-item').length >= 9) {
                    backToTopBtn.style.display = "block";
                }

            }
            else {
                eventList.innerHTML('<div class="no-posts">Ei tapahtumia.</div>');
            }

            loading = false;

        } catch (error) {
            console.error(`Download error: ${error.message}`);
        }
    }

    backToTopBtn.addEventListener("click", () => {
        window.scroll({ top: 0, left: 0, behavior: 'smooth' });
    });

    const appendToList = (item, index) => {

        const currentCount  = document.querySelectorAll('.hki-events-list-item').length;
        let thumbnail       = item.hki_event_image_url ? `<img src="${item.hki_event_image_url}" alt="${item.hki_event_image_alt_text}" onerror="this.src='${fallBackImg}'" loading="lazy"></img>` : fallBackImg;
        let itemId          = `hki-events-list-item-${currentCount + index}`;

        let listItem =
        `<div class="hki-events-list-item" id="${itemId }" style="opacity:0;">
            <div class="post-image">
                ${thumbnail}
            </div>
            <div class="post-content">
                <div class="post-content-wrapper">
                    <div class="post-date">${new Date(Date.parse(item.hki_event_start_time)).toLocaleDateString('fi')}</div>
                    <div class="post-title">${item.title.rendered}</div>
                    <div class="post-button">
                        <a class="btn btn-news btn-primary" href="${item.link}">
                            Lue lisää
                        </a>
                    </div>
                </div>
                <div class="post-overlay"></div>
            </div>
        </div>`;

        let itemEl = document.createElement('div');
        eventList.append(itemEl);
        itemEl.outerHTML = listItem;

        itemEl = document.querySelector(`#${itemId}`);
        fadeIn(itemEl, 1000);

    };

});

/**
 * @see https://jsfiddle.net/TH2dn/606/
 */
function fadeIn(el, time) {
    el.style.opacity = 0;
  
    var last = +new Date();
    var tick = function() {
      el.style.opacity = +el.style.opacity + (new Date() - last) / time;
      last = +new Date();
  
      if (+el.style.opacity < 1) {
        (window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16);
      }
    };
  
    tick();
}

