<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth/login');
});

//Login
Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::post('submit-login', [App\Http\Controllers\Auth\LoginController::class, 'submit_login'])->name('submit.login');
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

//Common dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('admin/dashboard',[App\Http\Controllers\Admin\DashboardController::class, 'dashboard'])->name('admin.dashboard');
});

//Admin agent Middleware
Route::middleware(['auth','role:admin,agent'])->group(function () {
    //Agent
    Route::get('admin/agents', [App\Http\Controllers\Admin\AgentController::class, 'index'])->name('admin.agents');
    Route::post('admin/agent-status', [App\Http\Controllers\Admin\AgentController::class, 'agent_status'])->name('admin.agent.status');
    Route::get('admin/agents-export', [App\Http\Controllers\Admin\AgentController::class, 'agent_export'])->name('admin.agents.export');
    Route::get('admin/agent-wallet/{add_agent_id}', [App\Http\Controllers\Admin\AgentController::class, 'agent_wallet'])->name('admin.agent.wallet');
    Route::post('admin/agent-wallet/topup', [App\Http\Controllers\Admin\AgentController::class, 'agent_wallet_topup']);
    Route::post('admin/agent-wallet/refund', [App\Http\Controllers\Admin\AgentController::class, 'refund_agent_wallet'])->name('admin.agent.wallet.refund');
    Route::get('admin/agent-wallet/invoice/{wallet_id}', [App\Http\Controllers\Admin\AgentController::class, 'agent_wallet_invoice'])->name('admin.agent.wallet.invoice');
    //Agent Bulk Email
    Route::get('admin/agent-bulk-email', [App\Http\Controllers\Admin\AgentController::class, 'agent_bulk_email']);
    Route::post('admin/send-bulk-email', [App\Http\Controllers\Admin\AgentController::class, 'send_bulk_email']);
    //Agent topup report
    Route::get('admin/agent-topup-report', [App\Http\Controllers\Admin\AgentReportController::class, 'agent_topup_report']);
    Route::get('admin/agent-topup-report-export', [App\Http\Controllers\Admin\AgentReportController::class, 'agent_topup_export'])->name('admin.agent.topup.export');
    //Agent subscribers
    Route::get('admin/agent-subscribers', [App\Http\Controllers\Admin\AgentSubscriptionController::class, 'index'])->name('admin.agent.subscriptions');
    Route::post('admin/agent-subscriptions-store', [App\Http\Controllers\Admin\AgentSubscriptionController::class, 'store'])->name('admin.agent.subscriptions.store');

    //Agent Leads
    Route::get('admin-inquires', [App\Http\Controllers\Admin\LeadController::class, 'index'])->name('admin.inquires');
    Route::post('admin-inquires-store', [App\Http\Controllers\Admin\LeadController::class, 'store'])->name('admin.inquires.store');
});

//Admin finance Middleware
Route::middleware(['auth','role:admin,finance'])->group(function () {
    //Booking List
    Route::get('admin/bookings', [App\Http\Controllers\Admin\Finance\BookingController::class, 'index'])->name('admin.bookings');
    //Agent booking summaries
    Route::get('admin/agent-booking-summary', [App\Http\Controllers\Admin\Finance\AgentBookingSummaryController::class, 'index'])->name('admin.agent.booking.summary');
    Route::get('admin/agent-booking-settlement', [App\Http\Controllers\Admin\Finance\AgentBookingSummaryController::class, 'agent_booking_settlement'])->name('admin.agent.booking.settlement');
    //Source summaries
    Route::get('admin/source-summary', [App\Http\Controllers\Admin\Finance\SourceController::class, 'index'])->name('admin.source.summary');
    Route::get('admin/source-booking-details', [App\Http\Controllers\Admin\Finance\SourceController::class, 'source_booking_details'])->name('admin.source.booking.details');
    //Source summaries export
    Route::get('admin/source-summary-export', [App\Http\Controllers\Admin\Finance\SourceController::class, 'export'])->name('admin.source.summary.export');
    //Supplier payments
    Route::get('admin/supplier-payments', [App\Http\Controllers\Admin\Finance\SupplierPaymentController::class, 'index'])->name('admin.supplier.payments');
    Route::post('admin/supplier-payment/update', [App\Http\Controllers\Admin\Finance\SupplierPaymentController::class, 'update_payment'])->name('admin.supplier.payment.update');
    //Invoice
    Route::get('admin/supplier-invoice/{id}', [App\Http\Controllers\Admin\Finance\SupplierInvoiceController::class, 'show'])->name('admin.supplier.invoice');
    Route::get('admin/supplier-invoice-single/{id}', [App\Http\Controllers\Admin\Finance\SupplierInvoiceController::class, 'show_single'])->name('admin.supplier.invoice.single');
    //Package payment
    Route::get('admin/package-profit-report', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'index'])->name('admin.package-payment.index');
    Route::get('admin/add-package-payment', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'create'])->name('admin.package-payment.create');
    Route::post('admin/package-payment-save', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'store'])->name('admin.package-payment.store');
    Route::get('admin/edit-package-payment/{id}', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'edit'])->name('admin.edit.package-payment');
    Route::post('admin/package-payment-update/{id}', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'update'])->name('admin.package-payment.update');
    Route::post('admin/package-payment-delete/{id}', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'destroy'])->name('admin.package-payment.delete');
    Route::post('admin/package-payment-transaction', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'storePayment'])->name('admin.package-payment.transaction.store');
    //Export
    Route::get('admin/package-profit-report/export', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'export'])->name('admin.package-payment.export');
    //Invoice
    Route::get('admin/package-payment/invoice/{id}', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'invoice'])->name('admin.package-payment.invoice');
    Route::get('admin/package-payment/invoice/booking/{id}/{type}', [App\Http\Controllers\Admin\Finance\PackageBookingFinanceController::class, 'initialInvoice'])->whereIn('type', ['customer', 'supplier'])->name('admin.package-payment.initial-invoice');
});
