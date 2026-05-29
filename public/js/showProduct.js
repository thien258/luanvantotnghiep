// public/js/showProduct.js

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('filterForm');

    // =========================
    // AJAX LOAD PRODUCTS
    // =========================
    function loadProducts() {

        const formData = new FormData(form);

        // lấy sort
        const sort =
            document.querySelector('#sort');

        if (sort) {

            formData.append('sort', sort.value);

        }

        const params =
            new URLSearchParams(formData);

        fetch(
            window.location.pathname + '?' + params.toString(),
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        )
        .then(response => response.text())
        .then(html => {

            const parser =
                new DOMParser();

            const doc =
                parser.parseFromString(html, 'text/html');

            const newProducts =
                doc.querySelector('#product-container');

            document.querySelector('#product-container')
                .innerHTML = newProducts.innerHTML;

            // update url không reload
            window.history.pushState(
                {},
                '',
                window.location.pathname + '?' + params.toString()
            );

        });

    }

    // =========================
    // SORT
    // =========================
    const sort =
        document.querySelector('#sort');

    if (sort) {

        sort.addEventListener('change', function () {

            loadProducts();

        });

    }

    // =========================
    // CHECKBOX FILTER
    // =========================
    document.querySelectorAll(
        '#filterForm input[type="checkbox"]'
    ).forEach(item => {

        item.addEventListener('change', function () {

            loadProducts();

        });

    });

    // =========================
    // PRICE SLIDER
    // =========================
    const slider =
        document.getElementById('price-range');

    if (!slider) return;

    const minInput =
        document.getElementById('min_price');

    const maxInput =
        document.getElementById('max_price');

    const minDisplay =
        document.getElementById('price-min-display');

    const maxDisplay =
        document.getElementById('price-max-display');

    const currentMin =
        parseInt(minInput.value) || 0;

    const currentMax =
        parseInt(maxInput.value) || 10000000;

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

                return Math.round(value)
                    .toLocaleString('vi-VN');

            },

            from: function (value) {

                return Number(
                    value.replace(/[^0-9.-]+/g, "")
                );

            }

        }

    });

    // update text
    slider.noUiSlider.on('update', function (values, handle) {

        if (handle === 0) {

            minDisplay.innerHTML =
                values[0] + 'đ';

            minInput.value =
                values[0].replace(/\./g, '');

        } else {

            maxDisplay.innerHTML =
                values[1] + 'đ';

            maxInput.value =
                values[1].replace(/\./g, '');

        }

    });

    // load ajax khi thả slider
    slider.noUiSlider.on('change', function () {

        loadProducts();

    });

});