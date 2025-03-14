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
        $('#printer-form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const fileInput = document.getElementById('gcode-file');
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
    const $filamentModal = $('#edit-modal');
    const $deleteFilamentModal = $('#delete-filament-modal');
    $('.filament-row').on('click', function() {
        const $row = $(this);
        $('#edit-id').val($row.data('id'));
        $('#edit-brand').val($row.data('brand'));
        $('#edit-name').val($row.data('name'));
        $('#edit-type').val($row.data('type'));
        $('#edit-color').val($row.data('color'));
        $('#edit-weight').val($row.data('weight'));
        $('#edit-price').val($row.data('price'));
        $('#delete-filament-id').val($row.data('id')); // Set for delete modal
        $filamentModal.show();
    });

    $('#delete-filament-btn').on('click', function() {
        $filamentModal.hide();
        $deleteFilamentModal.show();
    });

    $('#cancel-filament-delete').on('click', function() {
        $deleteFilamentModal.hide();
    });

    // Product edit modal
    const $productModal = $('#edit-product-modal');
    const $deleteProductModal = $('#delete-product-modal');
    $('.product-row').on('click', function() {
        const $row = $(this);
        $('#edit-product-id').val($row.data('id'));
        $('#edit-artikelnaam').val($row.data('artikelnaam'));
        $('#edit-gewicht').val($row.data('gewicht'));
        $('#edit-printtijd').val($row.data('printtijd'));
        $('#edit-printprijs').val($row.data('printprijs'));
        $('#edit-verkoopprijs').val($row.data('verkoopprijs'));
        $('#edit-idnummer2').val($row.data('idnummer2'));
        $('#edit-idnummer3').val($row.data('idnummer3'));
        $('#edit-idnummer4').val($row.data('idnummer4'));
        $('#edit-idnummer5').val($row.data('idnummer5'));
        $('#edit-idnummer6').val($row.data('idnummer6'));
        $('#edit-idnummer7').val($row.data('idnummer7'));
        $('#edit-idnummer8').val($row.data('idnummer8'));
        $('#edit-orderaantal').val($row.data('orderaantal'));
        $('#edit-aantal-afwijkend').val($row.data('aantal_afwijkend'));
        $('#edit-geconstateerde-afwijking').val($row.data('geconstateerde_afwijking'));
        $('#delete-product-id').val($row.data('id')); // Set for delete modal
        $productModal.show();
    });

    $('#delete-product-btn').on('click', function() {
        $productModal.hide();
        $deleteProductModal.show();
    });

    $('#cancel-delete').on('click', function() {
        $deleteProductModal.hide();
    });

    // Modal close handlers
    $('.close').on('click', function() {
        $(this).closest('.modal').hide();
    });

    // Cancel buttons
    $('.btn-cancel').on('click', function() {
        $(this).closest('.modal').hide();
    });

    $(window).on('click', function(e) {
        if (e.target === $filamentModal[0]) $filamentModal.hide();
        if (e.target === $productModal[0]) $productModal.hide();
        if (e.target === $deleteFilamentModal[0]) $deleteFilamentModal.hide();
        if (e.target === $deleteProductModal[0]) $deleteProductModal.hide();
        if (e.target === $('#create-filament-modal')[0]) $('#create-filament-modal').hide();
        if (e.target === $('#create-product-modal')[0]) $('#create-product-modal').hide();
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

    // Form submission handlers for edit and delete forms
    $('#edit-product-form, #edit-filament-form, #delete-product-form, #delete-filament-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const isDelete = $form.attr('id').includes('delete');
        
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    if (isDelete) {
                        const id = $form.find('[name="delete_id"]').val();
                        if ($form.attr('id') === 'delete-filament-form') {
                            $(`.filament-row[data-id="${id}"]`).remove();
                            $deleteFilamentModal.hide();
                        } else {
                            $(`.product-row[data-id="${id}"]`).remove();
                            $deleteProductModal.hide();
                        }
                    } else {
                        $form.closest('.modal').hide();
                        setTimeout(() => window.location.reload(), 1000);
                    }
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
    $('#create-filament-btn').on('click', function() {
        $('#create-filament-modal').show();
    });

    // Close Create Filament Modal
    $('#create-filament-modal .close').on('click', function() {
        $('#create-filament-modal').hide();
    });

    // Close Create Filament Modal with Cancel Button
    $('#create-filament-modal .btn-cancel').on('click', function() {
        $('#create-filament-modal').hide();
    });

    // Handle Create Filament Form Submission
    $('#create-filament-form').on('submit', function(e) {
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
                    $('#create-filament-modal').hide();
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
    $('#create-product-btn').on('click', function() {
        $('#create-product-modal').show();
    });

    // Close Create Product Modal
    $('#create-product-modal .close').on('click', function() {
        $('#create-product-modal').hide();
    });

    // Close Create Product Modal with Cancel Button
    $('#create-product-modal .btn-cancel').on('click', function() {
        $('#create-product-modal').hide();
    });

    // Handle Create Product Form Submission
    $('#create-product-form').on('submit', function(e) {
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
                    $('#create-product-modal').hide();
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