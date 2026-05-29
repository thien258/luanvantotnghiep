// public/js/showProduct.js
(function () {

    function initProductFilters() {

        const form = document.getElementById('filterForm');

        if (!form) {
            return;
        }

        function buildQueryParams() {

            const params = new URLSearchParams();

            new FormData(form).forEach(function (value, key) {

                if (value !== '') {
                    params.append(key, value);
                }

            });

            const sortEl = document.getElementById('sort');

            if (sortEl) {
                params.set('sort', sortEl.value);
            }

            return params;

        }

        function updateUrl(params) {

            const query = params.toString();

            const newUrl = query
                ? window.location.pathname + '?' + query
                : window.location.pathname;

            window.history.pushState({}, '', newUrl);

            return newUrl;

        }

        function loadProducts() {

            const params = buildQueryParams();

            const url = updateUrl(params);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    return response.text();
                })
                .then(function (html) {

                    const doc = new DOMParser()
                        .parseFromString(html, 'text/html');

                    const newProducts =
                        doc.querySelector('#product-container');

                    const container =
                        document.querySelector('#product-container');

                    if (newProducts && container) {
                        container.innerHTML = newProducts.innerHTML;
                    }

                })
                .catch(function (err) {
                    console.error('loadProducts:', err);
                });

        }

        window.applyProductFilters = loadProducts;

        const sort = document.getElementById('sort');

        if (sort) {
            sort.addEventListener('change', loadProducts);
        }

        form.querySelectorAll('input[type="checkbox"]').forEach(function (item) {

            item.addEventListener('change', loadProducts);

        });

        const slider = document.getElementById('price-range');

        if (!slider || typeof noUiSlider === 'undefined') {
            return;
        }

        const minInput = document.getElementById('min_price');
        const maxInput = document.getElementById('max_price');
        const minDisplay = document.getElementById('price-min-display');
        const maxDisplay = document.getElementById('price-max-display');

        const currentMin = parseInt(minInput.value, 10) || 0;
        const currentMax = parseInt(maxInput.value, 10) || 10000000;

        noUiSlider.create(slider, {
            start: [currentMin, currentMax],
            connect: true,
            step: 100000,
            range: {
                min: 0,
                max: 10000000
            },
            format: {
                to: function (value) {
                    return Math.round(value).toLocaleString('vi-VN');
                },
                from: function (value) {
                    return Number(value.replace(/[^0-9.-]+/g, ''));
                }
            }
        });

        slider.noUiSlider.on('update', function (values, handle) {

            if (handle === 0) {
                minDisplay.innerHTML = values[0] + 'đ';
                minInput.value = values[0].replace(/\./g, '');
            } else {
                maxDisplay.innerHTML = values[1] + 'đ';
                maxInput.value = values[1].replace(/\./g, '');
            }

        });

        slider.noUiSlider.on('change', loadProducts);

    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductFilters);
    } else {
        initProductFilters();
    }

})();
