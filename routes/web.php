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
});
