 document.querySelectorAll('.volume-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('.row');
            row.querySelectorAll('.variant-input').forEach(input => {
                input.disabled = !this.checked;
                if(!this.checked) input.value = ''; 
            });
            // Hiệu ứng bôi xanh viền khi được chọn
            if(this.checked) {
                row.classList.add('border-primary', 'border');
            } else {
                row.classList.remove('border-primary');
            }
        });
    });