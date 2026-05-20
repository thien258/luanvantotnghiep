document.addEventListener('DOMContentLoaded', function() {
    
    // Đưa đoạn code của ông vào trong này
    document.querySelectorAll('.volume-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('.row');
            if (row) { // Thêm cái if check cho chắc ăn
                row.querySelectorAll('.variant-input').forEach(input => {
                    input.disabled = !this.checked;
                    if(!this.checked) input.value = ''; 
                });
            }
        });
    });

});