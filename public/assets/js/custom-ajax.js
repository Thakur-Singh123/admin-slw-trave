$(document).ready(function() {
    //Agent filter
    $('#agentSearch, #agentEmailSearch').on('keyup', function () {
        $.get(base_url + '/admin/agents', {
            search: $('#agentSearch').val(),
            email: $('#agentEmailSearch').val(),
            from_date: $('#fromDate').val(),
            to_date: $('#toDate').val(),
            status: $('#statusFilter').val(),
            country: $('#countryFilter').val(),
            amount: $('#amountFilter').val()
        }, function (html) {
            $('#agentTable tbody').html(
                $(html).find('#agentTable tbody').html()
            );
            $('.agent-table-footer').html(
                $(html).find('.agent-table-footer').html()
            );
        });
    });
    $('#fromDate, #toDate, #statusFilter, #countryFilter, #amountFilter')
    .on('change', function () {
        $('#agentSearch').trigger('keyup');
    });
    // Clear Agent Filters
    $('#clearAgentFilters').on('click', function () {
        $('#agentSearch, #agentEmailSearch').val('');
        $('#fromDate, #toDate').val('');
        $('#statusFilter, #countryFilter, #amountFilter').val('all');
        $.get(base_url + '/admin/agents', {
            clear: 1
        }, function (html) {

            $('#agentTable tbody').html(
                $(html).find('#agentTable tbody').html()
            );

            $('.agent-table-footer').html(
                $(html).find('.agent-table-footer').html()
            );
        });
    });
    //Activate/Deactivate agent
    $(document).on('click', '.deactivate-agent, .activate-agent', function () {
        let id = $(this).data('id');
        let isActivate = $(this).hasClass('activate-agent');
        Swal.fire({
            title: isActivate ? 'Activate agent?' : 'Deactivate agent?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: isActivate ? 'Yes, Activate' : 'Yes, Deactivate'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post(base_url + '/admin/agent-status', {
                ids: [id],
                action: isActivate ? 'activate' : 'deactivate',
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire(
                        'Error',
                        data.message,
                        'error'
                    );
                }
            }).fail(function () {
                Swal.fire(
                    'Error',
                    'Something went wrong.',
                    'error'
                );
            });
        });
    });
    // Select all agents
    $(document).on('change', '#selectAllAgents', function () {
        let checked = $(this).prop('checked');
        $('.agent-checkbox').prop('checked', checked);
    });
    // Update select all checkbox
    $(document).on('change', '.agent-checkbox', function () {
        let total = $('.agent-checkbox').length;
        let checked = $('.agent-checkbox:checked').length;
        $('#selectAllAgents').prop(
            'checked',
            total > 0 && total === checked
        );
    });
    
    //Agent Wallet Transaction filter
    $('#transactionSearch').on('keyup', function () {
        let agentId = $('#transactionSearch').data('agent-id');
        $.get(base_url + '/admin/agent-wallet/' + agentId, {
            search: $('#transactionSearch').val(),
            from_date: $('#transactionFromDate').val(),
            to_date: $('#transactionToDate').val(),
            type: $('#transactionTypeFilter').val()
        }, function (html) {
            $('#transactionTable tbody').html(
                $(html).find('#transactionTable tbody').html()
            );
            $('.transaction-footer').html(
                $(html).find('.transaction-footer').html()
            );
        });
    });
    $('#transactionFromDate, #transactionToDate, #transactionTypeFilter').on('change', function () {
        $('#transactionSearch').trigger('keyup');
    });

    //Add Agent Wallet Top-up
    $('#specificAgentTopupForm').validate({
        rules: {
            amount: {
                required: true,
                number: true,
                min: 1
            },
            payment_method: {
                required: true
            },
            payment_reference: {
                required: true
            },
        },
        messages: {},
        submitHandler: function (form) {
            var formData = $(form).serialize();
            $.ajax({
                type: 'POST',
                url: base_url + '/admin/agent-wallet/topup',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    $('.disable-submit')
                        .prop('disabled', true)
                        .text('Adding Top-up...');
                },
                success: function (response) {
                    if (response.status) {
                        window.location.href = response.redirect_url;
                    }
                }
            });
        }
    });
    //Agent topup ajax
    function loadTopups() {
        $.ajax({
            url: base_url + '/admin/agent-topup-report',
            type: 'GET',
            data: {
                search: $('#topupSearch').val(),
                from_date: $('#topupFromDate').val(),
                to_date: $('#topupToDate').val(),
                status: $('#topupStatusFilter').val()
            },
            success: function (response) {
                $('#topupReportTable tbody').html(
                    $(response).find('#topupReportTable tbody').html()
                );
                $('#topupReportTable tfoot').html(
                    $(response).find('#topupReportTable tfoot').html()
                );

            },
            error: function (xhr) {
                console.log(xhr.responseText);
            }
        });
    }
    //Search
    $('#topupSearch').on('keyup', function () {
        loadTopups();
    });
    //Date + Status
    $('#topupFromDate, #topupToDate, #topupStatusFilter').on('change', function () {
        loadTopups();
    });
    //Reset
    $('#resetTopupFilter').on('click', function () {
        $('#topupSearch').val('');
        $('#topupFromDate').val('{{ now()->startOfMonth()->format("Y-m-d") }}');
        $('#topupToDate').val('{{ now()->endOfMonth()->format("Y-m-d") }}');
        $('#topupStatusFilter').val('all');

        loadTopups();
    });

    //Subscriptions
    function loadSubscriptions(page = 1) {
        $.ajax({
            url: "{{ route('admin.agent.subscriptions') }}",
            type: "GET",
            data: {
                search: $('#subscriberSearch').val(),
                plan: $('#subscriptionPlanFilter').val(),
                status: $('#subscriptionStatusFilter').val(),
                expiry_date: $('#subscriptionExpiryDate').val(),
                page: page
            },
            success: function (response) {
                let html = $(response);
                $('#subscriberTable tbody').html(
                    html.find('#subscriberTable tbody').html()
                );
                $('.table-footer').html(
                    html.find('.table-footer').html()
                );
                $('#totalSubscribers').html(
                    html.find('#totalSubscribers').html()
                );
                $('#activeSubscribers').html(
                    html.find('#activeSubscribers').html()
                );
                $('#expiringSubscribers').html(
                    html.find('#expiringSubscribers').html()
                );
                $('#expiredSubscribers').html(
                    html.find('#expiredSubscribers').html()
                );
            }
        });
    }
    $('#subscriberSearch').on('keyup', function () {
        loadSubscriptions(1);
    });
    $('#subscriptionPlanFilter, #subscriptionStatusFilter, #subscriptionExpiryDate')
        .on('change', function () {
            loadSubscriptions(1);
        });
    $('#resetSubscriptionFilter').on('click', function () {
        $('#subscriberSearch').val('');
        $('#subscriptionPlanFilter').val('all');
        $('#subscriptionStatusFilter').val('all');
        $('#subscriptionExpiryDate').val('');
        loadSubscriptions(1);
    });
    //Subscription Amount
    function updateSubscriptionAmount() {
        let plan = $('#modalSubscriptionPlan option:selected').val();
        let billing = $('#modalBillingCycle').val();
        if (!plan) return;
        let amount = billing === 'Yearly'
            ? $('#modalSubscriptionPlan option:selected').data('yearly')
            : $('#modalSubscriptionPlan option:selected').data('monthly');
        $('#subscriptionAmount').val(amount);
    }
    $('#modalSubscriptionPlan, #modalBillingCycle').on('change', updateSubscriptionAmount);
    // Expiry Date
    $('#subscriptionStartDate, #modalBillingCycle').on('change', function () {
        let start = $('#subscriptionStartDate').val();
        let billing = $('#modalBillingCycle').val();
        if (!start) return;
        let date = new Date(start + 'T00:00:00');
        billing === 'Yearly'
            ? date.setFullYear(date.getFullYear() + 1)
            : date.setMonth(date.getMonth() + 1);
        let yyyy = date.getFullYear();
        let mm = String(date.getMonth() + 1).padStart(2, '0');
        let dd = String(date.getDate()).padStart(2, '0');
        $('#subscriptionEndDate').val(yyyy + '-' + mm + '-' + dd);
    });
});