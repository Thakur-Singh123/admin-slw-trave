@extends('admin.layouts.master') 
@section('content')
<div class="admin-wrapper">
    @include('admin.partials.notification')
    <div class="package-form-page">
        <nav class="page-breadcrumb"><a href="{{ url('admin/dashboard')}}">Dashboard</a><i class="bi bi-chevron-right"></i><a href="{{ url('admin/package-profit-report')}}">Package Finance</a><i class="bi bi-chevron-right"></i><span>Add Payment</span></nav>
        <section class="page-heading package-page-heading">
            <div><p>Finance Module</p><h1>Add Package Booking Payment</h1><span>Manually record package revenue, operational cost, supplier payment and profit.</span></div>
            <a href="{{ url('admin/package-profit-report')}}" class="view-report-button"><i class="bi bi-bar-chart"></i> View Profit Report</a>
        </section>
        <form action="{{ route('admin.package-payment.store') }}" method="post" enctype="multipart/form-data" id="packagePaymentForm">
        @csrf
            <section class="form-card">
                <div class="section-heading"><span>01</span><div><h2>Booking Information</h2><p>Package and customer booking details.</p></div></div>
                <div class="form-grid four-column">
                    <div class="form-field"><label for="bookingReference">Booking Reference <b>*</b></label><input type="text" class="form-control" id="bookingReference" name="booking_reference" placeholder="Auto generated after save" readonly></div>
                    <div class="form-field span-2"><label for="packageName">Package Name <b>*</b></label><input type="text" class="form-control" id="packageName" name="package_name" value="{{ old('package_name') }}" placeholder="Example: Bangkok Pattaya 5 Nights Package" required></div>
                    <div class="form-field"><label for="packageCountry">Package Country <b>*</b></label>
                        <select class="form-select" id="packageCountry" name="country" required>
                            <option value="">Select country</option>
                            @foreach(['India','Thailand','Singapore','Sri Lanka','Vietnam'] as $c)
                                <option value="{{ $c }}" {{ old('country') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field"><label for="bookingDate">Booking Date <b>*</b></label><input type="date" class="form-control" id="bookingDate" name="booking_date" value="{{ old('booking_date', date('Y-m-d')) }}" required></div>
                    <div class="form-field"><label for="travelFrom">Travel From <b>*</b></label><input type="date" class="form-control" id="travelFrom" name="travel_from" value="{{ old('travel_from') }}" required></div>
                    <div class="form-field"><label for="travelTo">Travel To <b>*</b></label><input type="date" class="form-control" id="travelTo" name="travel_to" value="{{ old('travel_to') }}" required></div>
                    <div class="form-field"><label for="packageStatus">Booking Status</label>
                        <select class="form-select" id="packageStatus" name="booking_status">
                            @foreach(['Confirmed','Pending','Completed','Cancelled'] as $s)
                                <option value="{{ $s }}" {{ old('booking_status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field"><label for="customerName">Customer / Group Name <b>*</b></label><input type="text" class="form-control" id="customerName" name="customer_name" value="{{ old('customer_name') }}" placeholder="Enter customer or group name" required></div>
                    <div class="form-field"><label for="customerEmail">Email Address</label><input type="email" class="form-control" id="customerEmail" name="customer_email" value="{{ old('customer_email') }}" placeholder="name@example.com"></div>
                    <div class="form-field"><label for="customerPhone">Mobile Number</label><input type="text" class="form-control" id="customerPhone" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="Include country code"></div>
                    <div class="pax-fields"><div class="form-field"><label for="adultPax">Adults</label><input type="number" class="form-control" id="adultPax" name="adult_pax" min="1" value="{{ old('adult_pax', 2) }}"></div><div class="form-field"><label for="childPax">Children</label><input type="number" class="form-control" id="childPax" name="child_pax" min="0" value="{{ old('child_pax', 0) }}"></div><div class="form-field"><label for="infantPax">Infants</label><input type="number" class="form-control" id="infantPax" name="infant_pax" min="0" value="{{ old('infant_pax', 0) }}"></div></div>
                </div>
            </section>
            <section class="form-card">
                <div class="section-heading"><span>02</span><div><h2>Package Revenue</h2><p>Amount charged to customer and payment received.</p></div></div>
                <div class="form-grid four-column">
                    {{-- <div class="form-field"><label for="currency">Currency <b>*</b></label>
                        <select class="form-select finance-input" id="currency" name="currency" required>
                            @foreach(['THB','USD','INR','SGD','LKR','VND'] as $cur)
                                <option value="{{ $cur }}" {{ old('currency') == $cur ? 'selected' : '' }}>{{ $cur }}</option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="form-field">
                        <label for="currency">Currency <b>*</b></label>
                        <select class="form-select finance-input" id="currency" name="currency" required>
                            <option value="THB" selected>THB</option>
                        </select>
                    </div>
                    <div class="form-field"><label for="saleAmount">Package Sale Amount <b>*</b></label><input type="number" class="form-control finance-input" id="saleAmount" name="sale_amount" min="0" step="0.01" value="{{ old('sale_amount') }}" placeholder="0.00" required></div>
                    <div class="form-field"><label for="receivedAmount">Amount Received</label><input type="number" class="form-control finance-input" id="receivedAmount" name="received_amount" min="0" step="0.01" value="{{ old('received_amount', 0) }}"></div>
                    <div class="form-field"><label for="customerPaymentStatus">Customer Payment Status</label>
                        <select class="form-select" id="customerPaymentStatus" name="customer_payment_status">
                            @foreach(['Unpaid','Partially Paid','Paid'] as $ps)
                                <option value="{{ $ps }}" {{ old('customer_payment_status') == $ps ? 'selected' : '' }}>{{ $ps }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>
            <section class="form-card">
                <div class="section-heading"><span>03</span><div><h2>Package Cost Breakdown</h2><p>Enter every operational cost in the selected currency.</p></div></div>
                <div class="form-grid three-column cost-grid">
                    <div class="cost-field"><span class="cost-icon hotel"><i class="bi bi-building"></i></span><div class="form-field"><label for="hotelCost">Hotel Cost</label><input type="number" class="form-control cost-input" id="hotelCost" name="hotel_cost" min="0" step="0.01" value="{{ old('hotel_cost', 0) }}"></div></div>
                    <div class="cost-field"><span class="cost-icon transport"><i class="bi bi-car-front"></i></span><div class="form-field"><label for="transportCost">Transport Cost</label><input type="number" class="form-control cost-input" id="transportCost" name="transport_cost" min="0" step="0.01" value="{{ old('transport_cost', 0) }}"></div></div>
                    <div class="cost-field"><span class="cost-icon tour"><i class="bi bi-map"></i></span><div class="form-field"><label for="tourCost">Sightseeing / Tour Cost</label><input type="number" class="form-control cost-input" id="tourCost" name="tour_cost" min="0" step="0.01" value="{{ old('tour_cost', 0) }}"></div></div>
                    <div class="cost-field"><span class="cost-icon guide"><i class="bi bi-person-badge"></i></span><div class="form-field"><label for="guideCost">Guide Cost</label><input type="number" class="form-control cost-input" id="guideCost" name="guide_cost" min="0" step="0.01" value="{{ old('guide_cost', 0) }}"></div></div>
                    <div class="cost-field"><span class="cost-icon visa"><i class="bi bi-passport"></i></span><div class="form-field"><label for="visaCost">Visa Cost</label><input type="number" class="form-control cost-input" id="visaCost" name="visa_cost" min="0" step="0.01" value="{{ old('visa_cost', 0) }}"></div></div>
                    <div class="cost-field"><span class="cost-icon other"><i class="bi bi-three-dots"></i></span><div class="form-field"><label for="otherPackageCost">Other Package Cost</label><input type="number" class="form-control cost-input" id="otherPackageCost" name="other_package_cost" min="0" step="0.01" value="{{ old('other_package_cost', 0) }}"></div></div>
                </div>
                <div class="cost-total-row"><span>Total Package Cost</span><strong id="totalPackageCost">THB 0.00</strong><input type="hidden" id="totalPackageCostInput" name="total_package_cost" value="0"></div>
            </section>
            <section class="form-card">
                <div class="section-heading"><span>04</span><div><h2>Supplier &amp; Other Expense</h2><p>Record supplier settlement and expenses outside the package cost.</p></div></div>
                <div class="form-grid four-column">
                    <div class="form-field span-2"><label for="supplierName">Main Supplier / DMC</label><input type="text" class="form-control" id="supplierName" name="supplier_name" value="{{ old('supplier_name') }}" placeholder="Enter supplier company name"></div>
                    <div class="form-field"><label for="supplierPaid">Amount Paid to Supplier</label><input type="number" class="form-control finance-input" id="supplierPaid" name="supplier_paid" min="0" step="0.01" value="{{ old('supplier_paid', 0) }}"></div>
                    <div class="form-field"><label for="supplierPaymentStatus">Supplier Payment Status</label>
                        <select class="form-select" id="supplierPaymentStatus" name="supplier_payment_status">
                            @foreach(['Unpaid','Partially Paid','Paid'] as $sp)
                                <option value="{{ $sp }}" {{ old('supplier_payment_status') == $sp ? 'selected' : '' }}>{{ $sp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field"><label for="agentCommission">Agent Commission</label><input type="number" class="form-control finance-input" id="agentCommission" name="agent_commission" min="0" step="0.01" value="{{ old('agent_commission', 0) }}"></div>
                    <div class="form-field"><label for="otherExpense">Other Expense</label><input type="number" class="form-control finance-input" id="otherExpense" name="other_expense" min="0" step="0.01" value="{{ old('other_expense', 0) }}"></div>
                    <div class="form-field"><label for="paymentMode">Payment Mode</label>
                        <select class="form-select" id="paymentMode" name="payment_mode">
                            <option value="">Select mode</option>
                            @foreach(['Bank Transfer-India','Bank Transfer-Thailand','Cash','Credit Card','UPI','PayPal','Other'] as $pm)
                                <option value="{{ $pm }}" {{ old('payment_mode') == $pm ? 'selected' : '' }}>{{ $pm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field"><label for="transactionReference">UTR / Transaction Reference</label><input type="text" class="form-control" id="transactionReference" name="transaction_reference" value="{{ old('transaction_reference') }}" placeholder="Enter reference number"></div>
                    <div class="form-field"><label for="paymentDate">Payment Date</label><input type="date" class="form-control" id="paymentDate" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}"></div>
                    <div class="form-field span-2"><label for="paymentRemark">Internal Remark</label><textarea class="form-control" id="paymentRemark" name="remark" rows="3" placeholder="Add payment or package cost note">{{ old('remark') }}</textarea></div>
                    <div class="form-field"><label for="paymentReceipt">Payment Receipt</label><input type="file" class="form-control" id="paymentReceipt" name="payment_receipt" accept=".pdf,.jpg,.jpeg,.png"></div>
                </div>
            </section>
            <section class="profit-preview" id="profitPreview">
                <div><span>Sale Amount</span><strong id="previewSale">THB 0.00</strong></div>
                <div><span>Package Cost</span><strong id="previewCost">THB 0.00</strong></div>
                <div><span>Commission + Expense</span><strong id="previewExpense">THB 0.00</strong></div>
                <div class="profit-box"><span>Estimated Net Profit</span><strong id="previewProfit">THB 0.00</strong><small id="profitMargin">0.00% margin</small></div>
                <input type="hidden" id="netProfitInput" name="net_profit" value="0">
            </section>
            <div class="form-actions"><button type="reset" class="reset-form"><i class="bi bi-arrow-counterclockwise"></i> Reset Form</button><button type="submit" class="save-booking"><i class="bi bi-check2-circle"></i> Save Package Payment</button></div>
        </form>
    </div>
</div>
@endsection