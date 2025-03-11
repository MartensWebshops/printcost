$(document).ready(function() {
    // Toast notification function
    function showToast(message, type = 'success') {
        const $toast = $('<div class="toast"></div>').text(message).addClass(type);
        $('.toast-container').append($toast);
        
        setTimeout(() => $toast.addClass('show'), 10);
        setTimeout(() => {
            $toast.removeClass('show');
            setTimeout(() => $toast.remove(), 300);
        }, 3000);
    }

    // Search functionality
    const $searchInput = $('#search');
    const $clearButton = $('.clear-search');
    const $showList = $('#show-list');
    const currentPage = new URLSearchParams(window.location.search).get('page') || '1';
    let searchTimeout;

    $searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        $clearButton.toggle(!!query);

        if (query) {
            searchTimeout = setTimeout(() => {
                $.ajax({
                    url: `index.php?page=${currentPage}`,
                    method: 'POST',
                    data: { query },
                    success: response => $showList.html(response),
                    error: () => showToast('Zoekfout', 'error')
                });
            }, 300);
        } else {
            $showList.empty();
        }
    });

    $clearButton.on('click', function() {
        $searchInput.val('').trigger('input').focus();
    });

    $showList.on('click', 'a', function(e) {
        e.preventDefault();
        const $row = $(`.product-row[data-id="${$(this).data('id')}"]`);
        if ($row.length) {
            $row.trigger('click');
            $showList.empty();
        }
    });

    // Hamburger menu
    const $hamburgerMenus = $('[data-menu]');
    $hamburgerMenus.on('click', '.hamburger-icon', function(e) {
        e.stopPropagation();
        $(this).siblings('.dropdown-content').toggleClass('show');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('[data-menu]').length) {
            $('.dropdown-content').removeClass('show');
        }
    });

    // Filament edit modal
    const $filamentModal = $('#editModal');
    $('.filament-row').on('click', function() {
        const $row = $(this);
        $('#edit_id').val($row.data('id'));
        $('#edit_brand').val($row.data('brand'));
        $('#edit_name').val($row.data('name'));
        $('#edit_type').val($row.data('type'));
        $('#edit_color').val($row.data('color'));
        $('#edit_weight').val($row.data('weight'));
        $('#edit_price').val($row.data('price'));
        $('#delete_filament_id').val($row.data('id'));

        $('#editFilamentFields').show();
        $('#editFilamentButtons').show();
        $('#deleteFilamentConfirm').hide();
        $('#confirmFilamentButtons').hide();

        $filamentModal.show();
    });

    $('#deleteFilamentBtn').on('click', function() {
        $('#editFilamentFields').hide();
        $('#editFilamentButtons').hide();
        $('#deleteFilamentConfirm').show();
        $('#confirmFilamentButtons').show();
    });

    $('#cancelFilamentDelete').on('click', function() {
        $('#editFilamentFields').show();
        $('#editFilamentButtons').show();
        $('#deleteFilamentConfirm').hide();
        $('#confirmFilamentButtons').hide();
    });

    // Product edit modal
    const $productModal = $('#editProductModal');
    $('.product-row').on('click', function() {
        const $row = $(this);
        $('#edit_product_id').val($row.data('id'));
        $('#edit_artikelnaam').val($row.data('artikelnaam'));
        $('#edit_gewicht').val($row.data('gewicht'));
        $('#edit_printtijd').val($row.data('printtijd'));
        $('#edit_printprijs').val($row.data('printprijs'));
        $('#edit_verkoopprijs').val($row.data('verkoopprijs'));
        $('#edit_idnummer2').val($row.data('idnummer2'));
        $('#edit_idnummer3').val($row.data('idnummer3'));
        $('#edit_idnummer4').val($row.data('idnummer4'));
        $('#edit_idnummer5').val($row.data('idnummer5'));
        $('#edit_idnummer6').val($row.data('idnummer6'));
        $('#edit_idnummer7').val($row.data('idnummer7'));
        $('#edit_idnummer8').val($row.data('idnummer8'));
        $('#edit_orderaantal').val($row.data('orderaantal'));
        $('#edit_aantal_afwijkend').val($row.data('aantal_afwijkend'));
        $('#edit_geconstateerde_afwijking').val($row.data('geconstateerde_afwijking'));
        $('#delete_product_id').val($row.data('id'));
        
        $('#editFormFields').show();
        $('#editButtons').show();
        $('#deleteConfirm').hide();
        $('#confirmButtons').hide();
        
        $productModal.show();
    });

    $('#deleteProductBtn').on('click', function() {
        $('#editFormFields').hide();
        $('#editButtons').hide();
        $('#deleteConfirm').show();
        $('#confirmButtons').show();
    });

    $('#cancelDelete').on('click', function() {
        $('#editFormFields').show();
        $('#editButtons').show();
        $('#deleteConfirm').hide();
        $('#confirmButtons').hide();
    });

    // Modal close handlers
    $('.close, .close-btn').on('click', function() {
        $filamentModal.hide();
        $productModal.hide();
    });

    $(window).on('click', function(e) {
        if (e.target === $filamentModal[0]) $filamentModal.hide();
        if (e.target === $productModal[0]) $productModal.hide();
    });

    // Cost value update handling
    $('.cost-value').on('input', function() {
        const $input = $(this);
        const originalValue = $input.data('original-value');
        const currentValue = $input.val();
        const $updateBtn = $input.siblings('.update-btn');

        if (currentValue !== originalValue) {
            $updateBtn.show();
        } else {
            $updateBtn.hide();
        }
    });

    $('.inline-update-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $input = $form.find('.cost-value');
        const $updateBtn = $form.find('.update-btn');
        const $row = $form.closest('tr');
        
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $input.data('original-value', response.cost_value);
                    $input.val(response.cost_value);
                    $row.find('.date-updated').text(response.date_updated);
                    $updateBtn.hide();
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Fout bij het bijwerken van de kosten.', 'error');
            }
        });
    });

    // Form submission handlers for create.php, index.php, filaments.php
    $('#createForm, #editProductForm, #editFilamentForm').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(() => window.location.reload(), 1000); // Reload after toast
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Fout bij het verzenden van het formulier.', 'error');
            }
        });
    });
});