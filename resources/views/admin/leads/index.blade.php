@extends('admin.layouts.master')
@section('content')
<div class="enquiry-page-content">
    @include('admin.partials.notification')
    <!-- Page Heading -->
    <section class="page-heading enquiry-page-heading">
        <div>
            <p>Lead Management</p>

            <h1>All Enquiries &amp; Leads</h1>

            <span>
                View, publish, assign and manage all customer enquiries.
            </span>
        </div>
        <button
            type="button"
            class="primary-button"
            data-bs-toggle="modal"
            data-bs-target="#addLeadModal"
        >
            <i class="bi bi-plus-lg"></i>
            Add Lead
        </button>
    </section>
    <!-- Summary -->
    <section class="lead-summary-grid">
        <article>
            <span class="summary-icon blue">
                <i class="bi bi-inbox"></i>
            </span>
            <div>
                <p>Total Enquiries</p>
                <h2>{{ number_format($total_enquiries) }}</h2>
                <small>All received leads</small>
            </div>
        </article>
        <article>
            <span class="summary-icon orange">
                <i class="bi bi-stars"></i>
            </span>
            <div>
                <p>New Leads</p>
                <h2>{{ number_format($new_leads) }}</h2>
                <small>Need first response</small>
            </div>
        </article>
        <article>
            <span class="summary-icon yellow">
                <i class="bi bi-telephone-forward"></i>
            </span>
            <div>
                <p>Follow-up</p>
                <h2>{{ number_format($follow_up) }}</h2>
                <small>Currently in progress</small>
            </div>
        </article>
        <article>
            <span class="summary-icon green">
                <i class="bi bi-check2-circle"></i>
            </span>
            <div>
                <p>Converted</p>
                <h2>{{ number_format($converted) }}</h2>
                <small>Successful bookings</small>
            </div>
        </article>
    </section>
    <!-- Enquiry List -->
    <section class="content-card enquiry-list-card">
        <div class="enquiry-toolbar">
            <div class="enquiry-search">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    id="enquirySearch"
                    placeholder="Search ID, name, email or mobile..."
                    value="{{ request('search') }}"
                >
            </div>
            <div class="enquiry-filters">
                <input
                    type="date"
                    class="form-control"
                    id="leadFromDate"
                    title="From date"
                    value="{{ request('from_date') }}"
                >
                <input
                    type="date"
                    class="form-control"
                    id="leadToDate"
                    title="To date"
                    value="{{ request('to_date') }}"
                >
                <select
                    class="form-select"
                    id="leadStatusFilter"
                >
                    <option value="all">
                        All Status
                    </option>
                    <option
                        value="new"
                        {{ request('status') == 'new' ? 'selected' : '' }}
                    >
                        New
                    </option>
                    <option
                        value="follow-up"
                        {{ request('status') == 'follow-up' ? 'selected' : '' }}
                    >
                        Follow Up
                    </option>
                    <option
                        value="quotation-sent"
                        {{ request('status') == 'quotation-sent' ? 'selected' : '' }}
                    >
                        Quotation Sent
                    </option>
                    <option
                        value="converted"
                        {{ request('status') == 'converted' ? 'selected' : '' }}
                    >
                        Converted
                    </option>
                    <option
                        value="closed"
                        {{ request('status') == 'closed' ? 'selected' : '' }}
                    >
                        Closed
                    </option>

                </select>
                <select
                    class="form-select"
                    id="leadSourceFilter"
                >
                    <option value="all">
                        All Sources
                    </option>

                    <option
                        value="website"
                        {{ request('source') == 'website' ? 'selected' : '' }}
                    >
                        Website
                    </option>
                    <option
                        value="facebook"
                        {{ request('source') == 'facebook' ? 'selected' : '' }}
                    >
                        Facebook
                    </option>

                    <option
                        value="referral"
                        {{ request('source') == 'referral' ? 'selected' : '' }}
                    >
                        Referral
                    </option>

                    <option
                        value="manual"
                        {{ request('source') == 'manual' ? 'selected' : '' }}
                    >
                        Manual
                    </option>

                    <option
                        value="whatsapp"
                        {{ request('source') == 'whatsapp' ? 'selected' : '' }}
                    >
                        WhatsApp
                    </option>

                    <option
                        value="other"
                        {{ request('source') == 'other' ? 'selected' : '' }}
                    >
                        Other
                    </option>

                </select>
                <button
                    type="button"
                    class="export-button"
                >
                    <i class="bi bi-download"></i>
                    Export
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table
                class="table enquiry-table align-middle mb-0"
                id="enquiryTable"
            >
                <thead>
                    <tr>
                        <th>Enquiry</th>
                        <th>Customer</th>
                        <th>Travel Plan</th>
                        <th>Guests</th>
                        <th>Requirement</th>
                        <th>Lead Details</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enquiries as $enquiry)
                        @php
                            $statusValue = strtolower(
                                str_replace(' ', '-', $enquiry->status ?? 'new')
                            );
                            $sourceValue = strtolower(
                                $enquiry->source ?? 'manual'
                            );
                            $initials = collect(
                                explode(' ', trim($enquiry->name ?? 'NA'))
                            )
                            ->filter()
                            ->map(function ($word) {
                                return strtoupper(substr($word, 0, 1));
                            })
                            ->take(2)
                            ->implode('');
                        @endphp
                        <tr
                            data-status="{{ $statusValue }}"
                            data-source="{{ $sourceValue }}"
                            data-date="{{ $enquiry->from_date }}"
                        >
                            <!-- Enquiry -->
                            <td>
                                <strong>
                                    {{ $enquiry->enquiry_no ?? 'ENQ-' . $enquiry->id }}
                                </strong>
                                <small>
                                    #{{ $enquiry->id }}
                                    ·
                                    {{ $enquiry->date
                                        ? \Carbon\Carbon::parse($enquiry->date)->format('d M Y')
                                        : 'N/A'
                                    }}
                                </small>
                            </td>
                            <!-- Customer -->
                            <td>
                                <div class="customer-cell">

                                    <span>
                                        {{ $initials }}
                                    </span>

                                    <div>

                                        <strong>
                                            {{ $enquiry->name ?? 'N/A' }}
                                        </strong>

                                        <small>
                                            <i class="bi bi-envelope"></i>
                                            {{ $enquiry->email ?? 'N/A' }}
                                        </small>

                                        <small>
                                            <i class="bi bi-telephone"></i>
                                            {{ $enquiry->mobile ?? 'N/A' }}
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <!-- Travel Plan -->

                            <td>

                                <strong>

                                    {{ $enquiry->destination_1 ?? 'N/A' }}

                                    <i class="bi bi-arrow-right"></i>

                                    {{ $enquiry->destination_2 ?? 'N/A' }}

                                </strong>


                                <small>

                                    @if($enquiry->from_date && $enquiry->to_date)

                                        {{ \Carbon\Carbon::parse($enquiry->from_date)->format('d M Y') }}

                                        –

                                        {{ \Carbon\Carbon::parse($enquiry->to_date)->format('d M Y') }}

                                    @else

                                        N/A

                                    @endif

                                </small>


                                <small>

                                    {{ $enquiry->nights_1 ?? 0 }} nights

                                </small>

                            </td>


                            <!-- Guests -->

                            <td>

                                <span class="guest-count">

                                    <i class="bi bi-person"></i>

                                    {{ $enquiry->adult ?? 0 }} Adult

                                </span>


                                <span class="guest-count">

                                    <i class="bi bi-person-standing"></i>

                                    {{ $enquiry->child ?? 0 }} Child

                                </span>

                            </td>


                            <!-- Requirement -->

                            <td>

                                <strong>
                                    {{ $enquiry->hotel_type ?? 'N/A' }}
                                </strong>

                                <small>
                                    {{ $enquiry->food_type ?? 'N/A' }}
                                </small>

                            </td>

                             <!-- Lead Details -->

                            <td>

                                <span class="source-label">

                                    <i class="bi bi-globe2"></i>

                                    {{ $enquiry->source ?? 'Manual' }}

                                </span>


                                <small>

                                    {{ (int) $enquiry->lead_is_published === 1
                                        ? 'Published to agents'
                                        : 'Private lead'
                                    }}

                                </small>


                                <small>

                                    @if(!empty($enquiry->lead_assigned_agent_id))

                                        @php
                                            $assignedAgent = $agents->firstWhere(
                                                'add_agent_id',
                                                $enquiry->lead_assigned_agent_id
                                            );

                                            $leadCount = $agentLeadCounts[
                                                $enquiry->lead_assigned_agent_id
                                            ] ?? 0;
                                        @endphp

                                        Agent:
                                        <span style="color:#198754; font-weight:700;">
                                            {{ $assignedAgent->name ?? 'Assigned' }}
                                            · {{ $leadCount }} Leads
                                        </span>

                                    @else

                                        <span style="color:#dc3545; font-weight:700;">
                                            Agent: Not Assigned
                                        </span>

                                    @endif

                                </small>

                            </td>


                            <!-- Status -->

                            <td>

                                <span class="lead-status {{ $statusValue }}">

                                    <i></i>

                                    {{ $enquiry->status ?? 'New' }}

                                </span>

                            </td>

                            <!-- Action -->

                            <td>

                                <div class="row-actions">


                                    <a
                                        href="#"
                                        title="View details"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>


                                    <button
                                        type="button"
                                        title="Edit lead"
                                        class="edit-lead"
                                        data-id="{{ $enquiry->id }}"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>


                                    <button
                                        type="button"
                                        class="more-action"
                                        title="More options"
                                        data-id="{{ $enquiry->id }}"
                                    >
                                        <i class="bi bi-three-dots"></i>
                                    </button>


                                </div>

                            </td>


                        </tr>


                    @empty


                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-4"
                            >
                                No enquiries found.
                            </td>

                        </tr>


                    @endforelse

                </tbody>

            </table>

        </div>


        <!-- Footer -->

       <div class="enquiry-table-footer">
    <p>
        Showing
        <strong>
            {{ $enquiries->firstItem() ?? 0 }}–{{ $enquiries->lastItem() ?? 0 }}
        </strong>
        of
        <strong>
            {{ $enquiries->total() }}
        </strong>
        enquiries
    </p>

    <nav aria-label="Enquiry pagination">
        <ul class="pagination pagination-sm mb-0">

            <li class="page-item {{ $enquiries->onFirstPage() ? 'disabled' : '' }}">
                <a
                    class="page-link"
                    href="{{ $enquiries->previousPageUrl() ?? '#' }}"
                >
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>

            <li class="page-item {{ $enquiries->currentPage() == 1 ? 'active' : '' }}">
                <a
                    class="page-link"
                    href="{{ $enquiries->url(1) }}"
                >
                    1
                </a>
            </li>

            <li class="page-item {{ $enquiries->currentPage() == 2 ? 'active' : '' }}">
                <a
                    class="page-link"
                    href="{{ $enquiries->url(2) }}"
                >
                    2
                </a>
            </li>

            <li class="page-item {{ $enquiries->currentPage() == 3 ? 'active' : '' }}">
                <a
                    class="page-link"
                    href="{{ $enquiries->url(3) }}"
                >
                    3
                </a>
            </li>

            <li class="page-item">
                <button
                    class="page-link"
                    type="button"
                >
                    ...
                </button>
            </li>

            <li class="page-item {{ $enquiries->currentPage() == $enquiries->lastPage() ? 'active' : '' }}">
                <a
                    class="page-link"
                    href="{{ $enquiries->url($enquiries->lastPage()) }}"
                >
                    {{ $enquiries->lastPage() }}
                </a>
            </li>

            <li class="page-item {{ !$enquiries->hasMorePages() ? 'disabled' : '' }}">
                <a
                    class="page-link"
                    href="{{ $enquiries->nextPageUrl() ?? '#' }}"
                >
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>

        </ul>
    </nav>
</div>




    </section>





<!-- Add Lead Modal -->

<div
    class="modal fade"
    id="addLeadModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content add-lead-modal">


            <div class="modal-header">

                <div>

                    <span class="modal-heading-icon">
                        <i class="bi bi-person-plus"></i>
                    </span>

                    <div>

                        <h5 class="modal-title">
                            Add New Lead
                        </h5>

                        <small>
                            Enter customer and travel requirement details.
                        </small>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <form
                id="addLeadForm"
                action="{{ route('admin.inquires.store') }}"
                method="POST"
            >

                @csrf


                <div class="modal-body">


                    <!-- Customer Information -->

                    <div class="form-section">

                        <div class="form-section-title">

                            <span>
                                01
                            </span>

                            <div>

                                <h6>
                                    Customer Information
                                </h6>

                                <p>
                                    Primary contact details for this enquiry.
                                </p>

                            </div>

                        </div>


                        <div class="row g-3">


                            <div class="col-md-4">

                                <label class="form-label">
                                    Customer Name *
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="name"
                                    placeholder="Enter full name"
                                    required
                                >

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Email Address *
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    name="email"
                                    placeholder="name@example.com"
                                    required
                                >

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Mobile Number *
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    name="mobile"
                                    placeholder="Include country code"
                                    required
                                >

                            </div>


                        </div>

                    </div>


                    <!-- Travel Plan -->

                    <div class="form-section">

                        <div class="form-section-title">

                            <span>
                                02
                            </span>

                            <div>

                                <h6>
                                    Travel Plan
                                </h6>

                                <p>
                                    Dates, destinations and passenger count.
                                </p>

                            </div>

                        </div>


                        <div class="row g-3">


                            <div class="col-md-3">

                                <label class="form-label">
                                    From Date *
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    name="from_date"
                                    required
                                >

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    To Date *
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    name="to_date"
                                    required
                                >

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    Adults *
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="adult"
                                    value="2"
                                    min="1"
                                    required
                                >

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    Children
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="child"
                                    value="0"
                                    min="0"
                                >

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Destination 1 *
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="destination_1"
                                    placeholder="Example: Bangkok"
                                    required
                                >

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Nights
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="nights_1"
                                    min="0"
                                    placeholder="0"
                                >

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Destination 2
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="destination_2"
                                    placeholder="Example: Phuket"
                                >

                            </div>


                        </div>

                    </div>


                    <!-- Lead Preferences -->

                    <div class="form-section mb-0">

                        <div class="form-section-title">

                            <span>
                                03
                            </span>

                            <div>

                                <h6>
                                    Lead Preferences
                                </h6>

                                <p>
                                    Hotel, meal, source and distribution settings.
                                </p>

                            </div>

                        </div>


                        <div class="row g-3">


                            <div class="col-md-3">

                                <label class="form-label">
                                    Hotel Type
                                </label>

                                <select
                                    class="form-select"
                                    name="hotel_type"
                                >

                                    <option value="">
                                        Choose...
                                    </option>

                                    <option>
                                        Three Star
                                    </option>

                                    <option>
                                        Four Star
                                    </option>

                                    <option>
                                        Five Star
                                    </option>

                                    <option>
                                        Luxury Resort
                                    </option>

                                </select>

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    Food Type
                                </label>

                                <select
                                    class="form-select"
                                    name="food_type"
                                >

                                    <option value="">
                                        Choose...
                                    </option>

                                    <option>
                                        Room Only
                                    </option>

                                    <option>
                                        Breakfast
                                    </option>

                                    <option>
                                        Lunch
                                    </option>

                                    <option>
                                        Half Board
                                    </option>

                                    <option>
                                        Full Board
                                    </option>

                                </select>

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    Lead Source
                                </label>

                                <select
                                    class="form-select"
                                    name="source"
                                >

                                    <option value="Manual">
                                        Manual
                                    </option>

                                    <option value="Website">
                                        Website
                                    </option>

                                    <option value="Facebook">
                                        Facebook
                                    </option>

                                    <option value="WhatsApp">
                                        WhatsApp
                                    </option>

                                    <option value="Referral">
                                        Referral
                                    </option>

                                    <option value="Other">
                                        Other
                                    </option>

                                </select>

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    Status
                                </label>

                                <select
                                    class="form-select"
                                    name="status"
                                >

                                    <option value="New">
                                        New
                                    </option>

                                    <option value="Follow Up">
                                        Follow Up
                                    </option>

                                    <option value="Quotation Sent">
                                        Quotation Sent
                                    </option>

                                    <option value="Converted">
                                        Converted
                                    </option>

                                    <option value="Closed">
                                        Closed
                                    </option>

                                </select>

                            </div>


                            <div class="col-12">

                                <label class="form-label">
                                    Remark / Customer Requirement
                                </label>

                                <textarea
                                    class="form-control"
                                    name="remark"
                                    rows="3"
                                    placeholder="Add tour, transfer, hotel or other package requirements..."
                                ></textarea>

                            </div>


                            <div class="col-md-6">

                                <label class="publish-switch">

                                    <input
                                        type="checkbox"
                                        name="lead_is_published"
                                        value="1"
                                    >

                                    <span></span>

                                    <div>

                                        <strong>
                                            Publish lead to agents
                                        </strong>

                                        <small>
                                            Eligible agents will be able to view and claim this lead.
                                        </small>

                                    </div>

                                </label>

                            </div>


                            <div class="col-md-6">

                                <label class="form-label">
                                Lead Type
                                    <small></small>
                                </label>


                                <select
                                    class="form-select"
                                    name="lead_type"
                                >

                                    <option value="FIT">
                                        FIT
                                    </option>

                                     <option value="GIT">
                                        GIT
                                    </option>


                                </select>

                            </div>


                        </div>

                    </div>


                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="cancel-button"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="primary-button"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Save Lead

                    </button>

                </div>


            </form>


        </div>

    </div>

</div>

@endsection