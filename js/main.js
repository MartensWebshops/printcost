$(document).ready(function() {
    // Search functionality
    const $searchInput = $('#search');
    const $clearButton = $('.clear-search');
    const $showList = $('#show-list');
    let searchTimeout;

    $searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        $clearButton.toggle(!!query);

        if (query) {
            searchTimeout = setTimeout(() => {
                $.ajax({
                    url: 'search.php',
                    method: 'POST',
                    data: { query },
                    success: response => $showList.html(response),
                    error: () => $showList.html('<div class="search-result">Zoekfout</div>')
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
        $searchInput.val($(this).text());
        $showList.empty();
        window.location.href = $(this).attr('href');
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
    const $modal = $('#editModal');
    $('.filament-row').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_brand').val($(this).data('brand'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_type').val($(this).data('type'));
        $('#edit_color').val($(this).data('color'));
        $('#edit_weight').val($(this).data('weight'));
        $('#edit_price').val($(this).data('price'));
        $modal.show();
    });

    $('.close, .close-btn').on('click', () => $modal.hide());
    $(window).on('click', e => {
        if (e.target === $modal[0]) $modal.hide();
    });

    // Clickable rows in index
    $('.clickable-row').on('click', function(e) {
        if (e.target.className !== 'delete-link') {
            window.location.href = $(this).data('href');
        }
    });

    // Prevent Enter key submission in forms
    $('#createForm, #updateForm').on('keydown', function(e) {
        if (e.key === 'Enter') e.preventDefault();
    });
});