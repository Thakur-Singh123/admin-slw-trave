<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Top-up Invoice {{ $invoice['invoice_no'] }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link href="{{ asset('public/assets/css/topup-invoice.css') }}"
        rel="stylesheet">
  </head>
  <body>
    <div class="invoice-toolbar no-print">
      <a href="{{ route('admin.agent.wallet', $agent->add_agent_id) }}"
            class="toolbar-button back-button"><i class="bi bi-arrow-left"></i> Back to Wallet</a>
      <div class="toolbar-actions"><button type="button"
                class="toolbar-button"
                id="downloadInvoice"><i class="bi bi-download"></i> Download PDF</button> <button type="button"
                class="toolbar-button print-button"
                id="printInvoice"><i class="bi bi-printer"></i> Print Invoice</button></div>
    </div>
    <main class="invoice-page"
        id="invoiceDocument">
      <!-- Header -->
      <header class="invoice-header">
        <div class="invoice-brand">
          <img src="{{ asset('public/assets/images/logo.png') }}"
                    alt="Sun Leisure World"
                    class="invoice-logo">
          <div class="invoice-label">
            <span>Official Receipt</span>
            <h1>Wallet Top-up Invoice</h1>
          </div>
        </div>
        <div class="company-details">
          <h2>SUN LEISURE WORLD CORPORATION</h2>
          <p>GSTIN No.: 0105554069478</p>
          <p>TAT License Number: 14/0275</p>
          <p>support@sunleisureworld.com</p>
        </div>
      </header>
      <!-- Invoice title -->
      <section class="invoice-title-strip">
        <div><span>Invoice Number</span> <strong>{{ $invoice['invoice_no'] }}</strong></div>
        <span class="paid-badge"><i class="bi bi-check-circle-fill"></i> {{ $invoice['payment_status'] }}</span>
      </section>
      <!-- Invoice information -->
      <section class="invoice-information-grid">
        <div class="invoice-info-card"><span>Invoice Date</span> <strong>{{ $invoice['invoice_date'] }}</strong></div>
        <div class="invoice-info-card"><span>Transaction ID</span> <strong>{{ $invoice['transaction_id'] }}</strong></div>
        <div class="invoice-info-card"><span>Payment Method</span> <strong>{{ $invoice['payment_mathod'] }}</strong></div>
        <div class="invoice-info-card"><span>Payment Reference</span> <strong>{{ $invoice['payment_reference'] }}</strong></div>
      </section>
      <!-- Billing -->
      <section class="billing-grid">
        <div class="billing-block">
          <h3>Issued By</h3>
          <strong>Sun Leisure World Corporation</strong>
          <p>Bangkok, Thailand</p>
          <p>GSTIN: 0105554069478</p>
          <p>support@sunleisureworld.com</p>
        </div>
        <div class="billing-block agent-billing-block">
          <h3>{{ $invoice['type'] === 'credit' ? 'Top-up For' : 'Debit For' }}</h3>
          <strong>{{ $invoice['company'] }}</strong>
          <p>{{ $invoice['contact_person'] }}</p>
          <p>{{ $invoice['email'] }}</p>
          <p>{{ $invoice['mobile'] }}</p>
          <p>{{ $invoice['address'] }}</p>
          <span class="agent-id-label">Agent ID: {{ $invoice['agent_id'] }}</span>
        </div>
      </section>
      <!-- Invoice table -->
      <section class="invoice-table-section">
        <table class="invoice-table">
          <thead>
            <tr>
              <th>Description</th>
              <th>Reference</th>
              <th>Payment Date</th>
              <th class="amount-column">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>@if($invoice['type'] === 'credit') <strong>Agent Wallet Top-up</strong> <small>Wallet credit for {{ $invoice['company'] }}</small> @else <strong>Agent Wallet Debit</strong> <small>Wallet debit for {{ $invoice['company'] }}</small> @endif</td>
              <td>{{ $invoice['payment_reference'] }}</td>
              <td>{{ $invoice['payment_date'] }} <small>{{ $invoice['payment_time'] }}</small></td>
              <td class="amount-column">@if($invoice['type'] === 'credit') <strong style="color:#198754;">+{{ $invoice['currency'] }} {{ number_format( $invoice['amount'], 2 ) }}</strong> @else <strong style="color:#dc3545;">-{{ $invoice['currency'] }} {{ number_format( $invoice['amount'], 2 ) }}</strong> @endif</td>
            </tr>
          </tbody>
        </table>
      </section>
      <!-- Invoice summary -->
      <section class="invoice-summary-section">
        <div class="invoice-notes">
          <h3>Amount in Words</h3>
          <p>@if(!empty($invoice['amount_words'])) {{ $invoice['amount_words'] }} @else {{ $invoice['currency'] }} {{ number_format($invoice['amount'], 2) }} @endif</p>
          <h3>Remarks</h3>
          <p>{{ $invoice['commets'] ?: 'N/A' }}</p>
        </div>
        <div class="invoice-totals">
          <div><span>Previous Wallet Balance</span> <strong>{{ $invoice['currency'] }} {{ number_format( $invoice['previous_balance'], 2 ) }}</strong></div>
          <div><span>{{ $invoice['type'] === 'credit' ? 'Top-up Amount' : 'Debit Amount' }}</span> @if($invoice['type'] === 'credit') <strong class="credit-total"
                            style="color:#198754;">+ {{ $invoice['currency'] }} {{ number_format( $invoice['amount'], 2 ) }}</strong> @else <strong style="color:#dc3545;">- {{ $invoice['currency'] }} {{ number_format( $invoice['amount'], 2 ) }}</strong> @endif</div>
          <div class="grand-total"><span>New Wallet Balance</span> <strong>{{ $invoice['currency'] }} {{ number_format( $invoice['new_balance'], 2 ) }}</strong></div>
        </div>
      </section>
      <!-- Payment confirmation -->
      <section class="payment-confirmation">
        <span class="confirmation-check"><i class="bi bi-check-lg"></i></span>
        <div>
          <strong>{{ $invoice['type'] === 'credit' ? 'Payment received successfully' : 'Wallet debit recorded successfully' }}</strong>
          <p>This transaction was recorded by {{ $invoice['created_by'] }}.</p>
        </div>
        <span class="payment-status">{{ $invoice['payment_status'] }}</span>
      </section>
      <!-- Footer -->
      <footer class="invoice-footer">
        <div>
          <strong>Thank you for your business.</strong>
          <p>For assistance, contact support@sunleisureworld.com</p>
        </div>
        <div class="authorized-signatory"><span>Electronically generated invoice</span> <strong>Authorized by Sun Leisure World</strong></div>
      </footer>
      <p class="invoice-disclaimer">This is a computer-generated wallet transaction invoice and does not require a physical signature.</p>
    </main>
    <!-- PDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- Invoice JS -->
    <script src="{{ asset('public/assets/js/topup-invoice.js') }}"></script>
  </body>
</html>