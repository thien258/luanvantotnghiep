document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('searchInput');
    const tbody = document.getElementById('product-tbody');

    if (!searchInput || !tbody) return;

    let timeout = null;

    searchInput.addEventListener('input', function () {

        clearTimeout(timeout);

        timeout = setTimeout(() => {

            let query = this.value;
            let festivalId = this.dataset.festivalId;

            fetch(`/admin/festival/${festivalId}/products?search=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(data => {

                tbody.innerHTML = data;

            })
            .catch(error => {
                console.error(error);
            });

        }, 300);

    });

});