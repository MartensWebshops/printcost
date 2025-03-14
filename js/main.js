$(document).ready(function() {
    // Toast notification function
    function showToast(message, type = 'success') {
        const $toast = $('<div class="toast"></div>').text(message).addClass(type);
        $('.toast-container').append($toast);
        
        setTimeout(() => $toast.addClass('show'), 10);
        setTimeout(() => {
            $toast.removeClass('show');
            setTimeout(() => $toast.remove(), 300);
        }, 5000);
    }

    // Sidebar toggle - Only toggle on button click, stays expanded otherwise
    $('.toggle-btn').on('click', function(e) {
        e.stopPropagation();
        $('.sidebar').toggleClass('collapsed');
    });

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

    // MQTT for printer.php
    if (typeof printerConfig !== 'undefined') {
        const client = new Paho.MQTT.Client(printerConfig.printerIp, 8883, 'webapp_' + Math.random().toString(16).substr(2, 8));

        client.onConnectionLost = function(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.error('MQTT Connection Lost:', responseObject.errorMessage);
                document.getElementById('printer-status').textContent = 'Verbindingsfout';
                showToast('Verbinding met printer verloren!', 'error');
            }
        };

        client.onMessageArrived = function(message) {
            try {
                const data = JSON.parse(message.payloadString);
                document.getElementById('printer-status').textContent = data.print?.print_state || 'Idle';
                document.getElementById('bed-temp').textContent = data.print?.bed_temper || 'N/A';
                document.getElementById('nozzle-temp').textContent = data.print?.nozzle_temper || 'N/A';
                document.getElementById('progress').textContent = data.print?.gcode_state === 'RUNNING' ? `${Math.round(data.print.mc_percent)}%` : '0%';
            } catch (e) {
                console.error('Error parsing MQTT message:', e);
            }
        };

        client.connect({
            userName: 'bblp',
            password: printerConfig.accessCode,
            useSSL: true,
            onSuccess: function() {
                console.log('Connected to MQTT');
                client.subscribe(`device/${printerConfig.deviceSerial}/report`);
                const message = new Paho.MQTT.Message(JSON.stringify({ 
                    print: { command: 'pushall', sequence_id: '1' }
                }));
                message.destinationName = `device/${printerConfig.deviceSerial}/request`;
                client.send(message);
            },
            onFailure: function(err) {
                console.error('MQTT Connection Failed:', err.errorMessage);
                showToast('Kon geen verbinding maken met de printer!', 'error');
            }
        });

        // Handle printer form submission
        $('#printerForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const fileInput = document.getElementById('gcode_file');
            const file = fileInput.files[0];

            $.ajax({
                url: `printer.php?page=${printerConfig.page}`,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    if (!response.success) {
                        showToast(response.message, 'error');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const fileContent = e.target.result;
                        const payload = JSON.stringify({
                            print: {
                                command: 'pushall',
                                filename: response.file_name,
                                file: btoa(fileContent),
                                sequence_id: Math.random().toString(36).substr(2)
                            }
                        });

                        const message = new Paho.MQTT.Message(payload);
                        message.destinationName = `device/${printerConfig.deviceSerial}/request`;
                        client.send(message);
                        showToast('Bestand succesvol verzonden naar de printer!', 'success');
                    };
                    reader.onerror = function() {
                        showToast('Fout bij het lezen van het bestand!', 'error');
                    };
                    reader.readAsBinaryString(file);
                },
                error: function() {
                    showToast('Er ging iets mis bij het valideren!', 'error');
                }
            });
        });
    }

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

    // Add handler for .back button in edit product modal
    $('#editProductModal .back').on('click', function(e) {
        e.preventDefault();
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

    // Form submission handlers for index.php, filaments.php
    $('#editProductForm, #editFilamentForm').on('submit', function(e) {
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
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Fout bij het verzenden van het formulier.', 'error');
            }
        });
    });

    // Open Create Filament Modal
    $('#createFilamentBtn').on('click', function() {
        $('#createFilamentModal').show();
    });

    // Close Create Filament Modal
    $('#createFilamentModal .close').on('click', function() {
        $('#createFilamentModal').hide();
    });

    // Close Create Filament Modal on outside click
    $(window).on('click', function(e) {
        if (e.target === $('#createFilamentModal')[0]) {
            $('#createFilamentModal').hide();
        }
    });

    // Handle Create Filament Form Submission
    $('#createFilamentForm').on('submit', function(e) {
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
                    $('#createFilamentModal').hide();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Fout bij het toevoegen van filament.', 'error');
            }
        });
    });

    // Open Create Product Modal
    $('#createProductBtn').on('click', function() {
        $('#createProductModal').show();
    });

    // Close Create Product Modal
    $('#createProductModal .close').on('click', function() {
        $('#createProductModal').hide();
    });

    // Close Create Product Modal with Cancel Button
    $('#createProductModal .btn-cancel').on('click', function() {
        $('#createProductModal').hide();
    });

    // Close Create Product Modal on outside click
    $(window).on('click', function(e) {
        if (e.target === $('#createProductModal')[0]) {
            $('#createProductModal').hide();
        }
    });

    // Handle Create Product Form Submission
    $('#createProductForm').on('submit', function(e) {
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
                    $('#createProductModal').hide();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Fout bij het toevoegen van product.', 'error');
            }
        });
    });
});