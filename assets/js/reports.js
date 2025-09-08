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
        language: { search: 'Search:' },
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


