<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice['invoice_no'] }} | Payment Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('public/assets/css/package-payment-invoice.css') }}" rel="stylesheet">
</head>
<body>
<div class="invoice-toolbar">
    <a href="{{ route('admin.package-payment.index') }}"
    onclick="event.preventDefault(); var url = this.href; window.close(); setTimeout(function () { window.location.href = url; }, 300);">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <div>
        <button type="button" id="downloadInvoice"><i class="bi bi-download"></i> Download PDF</button>
        <button type="button" class="primary" id="printInvoice"><i class="bi bi-printer"></i> Print Invoice</button>
    </div>
</div>

<main class="invoice-sheet" id="paymentInvoice">
    <header class="invoice-header">
        <div class="brand-block">
            <img src="https://slw.travel/assets/images/logo.png" alt="SLW Travel">
            <div>
                <span>{{ $invoice['is_supplier'] ? 'PAYMENT VOUCHER' : 'PAYMENT RECEIPT' }}</span>
                <h1>Sun Leisure World</h1>
                <p>Your trusted B2B travel partner</p>
            </div>
        </div>
        <div class="invoice-number">
            <span>Invoice Number</span>
            <strong>{{ $invoice['invoice_no'] }}</strong>
            <small>Issued on {{ $invoice['payment_date'] }}</small>
        </div>
    </header>

    <section class="invoice-parties">
        <div>
            <span class="section-label">{{ $invoice['is_supplier'] ? 'Paid To' : 'Received From' }}</span>
            <h2>{{ $invoice['party_name'] }}</h2>
            @if($invoice['party_email'])
                <p><i class="bi bi-envelope"></i>{{ $invoice['party_email'] }}</p>
            @endif
            <p><i class="bi bi-globe2"></i>{{ $invoice['is_supplier'] ? 'Supplier' : 'B2B Travel Agent' }}</p>
        </div>
        <div class="company-address">
            <span class="section-label">Issued By</span>
            <h2>Sun Leisure World Corporation</h2>
            <p>GSTIN: 0105554069478</p>
            <p>TAT License No.: 14/0275</p>
            <p><i class="bi bi-envelope"></i>tech@sunleisureworld.com</p>
        </div>
    </section>

    <section class="payment-highlight">
        <div>
            <span>{{ $invoice['is_supplier'] ? 'Payment Made' : 'Payment Received' }}</span>
            <strong>{{ $invoice['currency'] }} {{ number_format($invoice['current_payment'], 2) }}</strong>
            <small>Amount {{ $invoice['is_supplier'] ? 'paid' : 'received' }} in this transaction</small>
        </div>
        <span class="status-badge"><i></i>{{ $invoice['status'] }}</span>
    </section>

    <section class="invoice-section">
        <div class="section-heading">
            <div><span>01</span><h3>Package Information</h3></div>
            <strong>{{ $invoice['package_reference'] }}</strong>
        </div>
        <div class="package-box">
            <span><i class="bi bi-suitcase-lg"></i></span>
            <div>
                <h4>{{ $invoice['package_name'] }}</h4>
                <p><i class="bi bi-geo-alt"></i>{{ $invoice['country'] }}<b></b><i class="bi bi-calendar3"></i>{{ $invoice['travel_date'] }}</p>
            </div>
        </div>
    </section>

    <section class="invoice-section">
        <div class="section-heading"><div><span>02</span><h3>Transaction Details</h3></div></div>
        <div class="detail-grid">
            <div><span>Transaction Type</span><strong>{{ $invoice['transaction_type'] }}</strong></div>
            <div><span>Payment Mode</span><strong>{{ $invoice['payment_mode'] }}</strong></div>
            <div><span>Transaction Reference</span><strong>{{ $invoice['transaction_reference'] }}</strong></div>
            <div><span>Payment Date</span><strong>{{ $invoice['payment_date'] }}</strong></div>
        </div>
    </section>

    <section class="invoice-section">
        <div class="section-heading"><div><span>03</span><h3>Payment Summary</h3></div></div>
        <div class="summary-table">
            <div><span>Total Package Amount</span><strong>{{ $invoice['currency'] }} {{ number_format($invoice['package_total'], 2) }}</strong></div>
            <div><span>Amount Paid Before This Payment</span><strong>{{ $invoice['currency'] }} {{ number_format($invoice['previous_paid'], 2) }}</strong></div>
            <div class="current"><span>Current Payment</span><strong>+ {{ $invoice['currency'] }} {{ number_format($invoice['current_payment'], 2) }}</strong></div>
            <div><span>Total Amount Paid</span><strong>{{ $invoice['currency'] }} {{ number_format($invoice['total_paid'], 2) }}</strong></div>
            <div class="balance"><span>Remaining Balance</span><strong>{{ $invoice['currency'] }} {{ number_format($invoice['remaining_balance'], 2) }}</strong></div>
        </div>
    </section>

    <section class="invoice-note">
        <i class="bi bi-chat-left-text"></i>
        <div><strong>Payment Remark</strong><p>{{ $invoice['remark'] }}</p></div>
    </section>

    <footer class="invoice-footer">
        <div>
            <strong>Thank you for your payment!</strong>
            <p>This is a computer-generated payment receipt and does not require a signature.</p>
        </div>
        <div class="support"><span>Need assistance?</span><strong>tech@sunleisureworld.com</strong></div>
    </footer>
</main>

<script src="{{ asset('public/assets/js/package-payment-invoice.js') }}"></script>
</body>
</html>