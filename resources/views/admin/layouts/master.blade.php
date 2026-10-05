<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard | SLW Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('public/assets/css/common.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/agent-list.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/agent-wallet.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/agent-bulk-email.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/all-enquiries.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/agent-topup-report.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/agent-subscriber-list.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/all-bookings.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/source-summary.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/agent-booking-summary.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/supplier-payment.css') }}" rel="stylesheet">
  </head>
  <body>
    <div class="admin-wrapper">
      @include('admin.partials.sidebar')
      <button type="button" class="sidebar-overlay" id="sidebarOverlay" aria-label="Close sidebar"></button>
      <main class="admin-main">
       @include('admin.partials.header')
        @yield('content')
       @include('admin.partials.footer')
      </main>
    </div>
    <script>
        var base_url = '{{ url("/") }}'; 
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('public/assets/js/custom-ajax.js') }}"></script>
    <script src="{{ asset('public/assets/js/custom-script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('public/assets/js/common.js') }}"></script>
    <script src="{{ asset('public/assets/js/dashboard.js') }}"></script>
    <script src="{{ asset('public/assets/js/agent-bulk-email.js') }}"></script>
    <script src="{{ asset('public/assets/js/agent-topup-report.js') }}"></script>
    <script src="{{ asset('public/assets/js/agent-subscriber-list.js') }}"></script>
    <script src="{{ asset('public/assets/js/all-bookings.js') }}"></script>
    <script src="{{ asset('public/assets/js/source-summary.js') }}"></script>
    <script>
      $(document).ready(function () {
        $('#agentBookingTable').DataTable({
            pageLength: 20,
            lengthMenu: [20, 50, 100],
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            autoWidth: false,
            scrollX: true,
            language: {
                lengthMenu: 'Show _MENU_ entries',
                search: '',
                searchPlaceholder: 'Search agent, company or email...',
                paginate: {
                    previous: '‹',
                    next: '›'
                }
            },
            drawCallback: function () {
                let table = this.api();
                let rows = table.rows({ search: 'applied' }).nodes();
                let bookings = 0;
                let net = 0;
                let sell = 0;
                let wallet = 0;
                $(rows).each(function () {
                    bookings += parseFloat($(this).attr('data-bookings')) || 0;
                    net += parseFloat($(this).attr('data-net')) || 0;
                    sell += parseFloat($(this).attr('data-sell')) || 0;
                    wallet += parseFloat($(this).attr('data-wallet')) || 0;

                });
                let margin = sell - net;
                $('#footerBookings').html(
                    bookings.toLocaleString()
                );
                $('#footerNet').html(
                    'THB ' + net.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );
                $('#footerSales').html(
                    'THB ' + sell.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );
                $('#footerMargin').html(
                    'THB ' + margin.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );
                $('#footerWallet').html(
                    'USD ' + wallet.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );
            }
        });
      });
    </script>
  </body>
</html>