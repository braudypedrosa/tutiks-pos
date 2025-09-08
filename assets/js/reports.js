jQuery(function($) {
    let minDate, maxDate; // DateTime pickers

    function updateSummary(table) {
        let total = 0;
        let count = 0;
        // Only count filtered rows currently in the table
        table.rows({ filter: 'applied' }).every(function() {
            const $row = $(this.node());
            const amountText = $row.find('td').eq(2).text().replace(/[^0-9.]/g, '');
            const amount = parseFloat(amountText) || 0;
            total += amount;
            count += 1;
        });

        const hasMin = !!minDate?.val();
        const hasMax = !!maxDate?.val();
        const isFiltered = hasMin || hasMax;

        if (!isFiltered) {
            // Keep today's summary from server-rendered data attributes
            const $totalEl = $('#reports_total_value');
            const $ordersEl = $('#reports_orders_value');
            const todayTotal = $totalEl.data('today-total');
            const todayOrders = $ordersEl.data('today-orders');
            $('#reports_total_label').text("Today's Total");
            $('#reports_orders_label').text("Today's Orders");
            if (todayTotal !== undefined) {
                $totalEl.text('₱' + (Number(todayTotal).toFixed(2)));
            }
            if (todayOrders !== undefined) {
                $ordersEl.text(Number(todayOrders));
            }
            return;
        }

        $('#reports_total_label').text('Total Sale');
        $('#reports_orders_label').text('Total Orders');
        $('#reports_total_value').text('₱' + (total.toFixed(2)));
        $('#reports_orders_value').text(count);
    }
    // DateTime extension instances with guards
    if (typeof DateTime === 'undefined') {
        console.error('DataTables DateTime extension not loaded.');
    } else {
        const minEl = document.getElementById('reports_min');
        const maxEl = document.getElementById('reports_max');

        console.log('Initializing DateTime extension...');
        if (minEl) { minDate = new DateTime(minEl, { format: 'YYYY-MM-DD' }); }
        if (maxEl) { maxDate = new DateTime(maxEl, { format: 'YYYY-MM-DD' }); }
        console.log('DateTime extension initialized.');
    }

    // Extend DataTables search using DateTime values
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'reportsTable') return true;

        const min = minDate ? minDate.val() : null; // Date or null
        const max = maxDate ? maxDate.val() : null; // Date or null

        // If no min and max selected, do not filter (show all)
        if (!min && !max) return true;

        const $row = $(settings.aoData[dataIndex].nTr);
        const iso = $row.find('.date-display').data('iso');
        if (!iso) return true;

        const rowDate = new Date(iso);
        if (Number.isNaN(rowDate.getTime())) return true;

        if (min && rowDate < min) return false;
        if (max) {
            const maxEnd = new Date(max);
            maxEnd.setHours(23,59,59,999);
            if (rowDate > maxEnd) return false;
        }
        return true;
    });

    const table = $('#reportsTable').DataTable({
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        order: [[1, 'desc']], // sort by date column desc
        language: {
            search: 'Search:',
            emptyTable: 'No orders found.'
        },
        // Use Bootstrap 5 classes
        dom: "<'row'<'col-sm-12 col-md-8 d-flex align-items-center gap-2'l f><'col-sm-12 col-md-4 text-md-end'B>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            {
                extend: 'csvHtml5',
                text: 'Export CSV',
                title: 'sales_report',
                exportOptions: {
                    // export all rows currently matching filter
                    modifier: { search: 'applied' }
                }
            }
        ]
    });

    // Open Bootstrap modal on row click
    function renderModal(items, total, payment) {
        const tbody = $('#orderDetailsBody');
        tbody.empty();
        if (!Array.isArray(items) || items.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center">No items</td></tr>');
        } else {
            items.forEach(function(item) {
                const qty = Number(item.quantity || 0);
                const price = Number(item.price || 0);
                const subtotal = (qty * price).toFixed(2);
                tbody.append(
                    '<tr>' +
                    '<td>' + (item.name || '') + '</td>' +
                    '<td class="text-end">' + qty + '</td>' +
                    '<td class="text-end">₱' + price.toFixed(2) + '</td>' +
                    '<td class="text-end fw-semibold">₱' + subtotal + '</td>' +
                    '</tr>'
                );
            });
        }
        $('#orderDetailsPayment').text(payment === 'qr' ? 'QR' : (payment || '').toString().toUpperCase());
        $('#orderDetailsTotal').text('₱' + Number(total || 0).toFixed(2));
        const modalEl = document.getElementById('orderDetailsModal');
        if (!modalEl) return;
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function parseItemsFromRow(tr) {
        const raw = tr.attr('data-items') || '';
        if (!raw) return [];
        // Try base64 → JSON (ASCII)
        try {
            const jsonAscii = atob(raw);
            try { return JSON.parse(jsonAscii); } catch (_) {}
        } catch (_) {}
        // Try base64 UTF-8 → JSON
        try {
            const bytes = Uint8Array.from(atob(raw), c => c.charCodeAt(0));
            const jsonUtf8 = (window.TextDecoder ? new TextDecoder().decode(bytes) : String.fromCharCode.apply(null, bytes));
            try { return JSON.parse(jsonUtf8); } catch (_) {}
        } catch (_) {}
        // Fallback: raw may already be JSON
        try { return JSON.parse(raw); } catch (_) { return []; }
    }

    $('#reportsTable tbody').on('click', 'tr', function() {
        const tr = $(this);
        let items = parseItemsFromRow(tr);
        if (items && !Array.isArray(items)) {
            // Convert object with numeric keys to array
            items = Object.values(items);
        }
        console.debug('Order details items:', items);
        const total = tr.attr('data-total');
        const payment = tr.attr('data-payment');
        renderModal(items, total, payment);
    });

    // Refilter on DateTime change
    $('#reports_min, #reports_max').on('change input', function() {
        table.draw();
        updateSummary(table);
    });

    $('#reports_clear_dates').on('click', function() {
        if (minDate) minDate.val(null);
        if (maxDate) maxDate.val(null);
        table.draw();
        updateSummary(table);
    });

    // Initial draw (no filtering). Cards show today's totals from PHP.
    table.draw();
});


