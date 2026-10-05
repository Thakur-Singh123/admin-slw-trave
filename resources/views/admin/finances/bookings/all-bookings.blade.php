@extends('admin.layouts.master') 
@section('content')
<div class="booking-page-content">
   <nav class="page-breadcrumb">
      <a href="{{ url('admin/dashboard') }}">Dashboard</a>
      <i class="bi bi-chevron-right"></i>
      <span>All Bookings</span>
   </nav>
   <section class="page-heading booking-page-heading">
      <div>
         <p>Booking Management</p>
         <h1>All Bookings</h1>
         <span>View and manage tours, transfers, hotels, ferries and package bookings.</span>
      </div>
      <div class="heading-actions">
         <button
            type="button"
            class="export-source-button"
         >
            <i class="bi bi-download"></i>
            Export Report
        </button>
         <!-- <button type="button" class="primary-button"><i class="bi bi-plus-lg"></i> Add Booking</button> -->
      </div>
   </section>
   <section class="booking-summary-grid">
      <article>
         <span class="summary-icon blue"><i class="bi bi-journal-check"></i></span>
         <div>
            <p>Total Bookings</p>
            <h2>{{ number_format($totalBookings) }}</h2>
            <small>All booking types</small>
         </div>
      </article>
      <article>
         <span class="summary-icon green"><i class="bi bi-check2-circle"></i></span>
         <div>
            <p>Confirmed</p>
            <h2>{{ number_format($confirmedBookings) }}</h2>
            <small
               >{{ $totalBookings > 0 ? number_format( ($confirmedBookings / $totalBookings) * 100, 1 ) : 0 }}%
               confirmation rate</small
            >
         </div>
      </article>
      <article>
         <span class="summary-icon yellow"><i class="bi bi-hourglass-split"></i></span>
         <div>
            <p>Pending / On Hold</p>
            <h2>{{ number_format($pendingBookings) }}</h2>
            <small>Require attention</small>
         </div>
      </article>
      <article>
         <span class="summary-icon orange"><i class="bi bi-x-circle"></i></span>
         <div>
            <p>Cancelled</p>
            <h2>{{ number_format($cancelledBookings) }}</h2>
            <small>Including refunded bookings</small>
         </div>
      </article>
   </section>
   <section class="content-card booking-list-card">
      {{-- Booking Type Tabs --}}
      <div class="booking-type-tabs" id="bookingTypeTabs">
        <button
            type="button"
            class="{{ request('type', 'all') == 'all' ? 'active' : '' }}"
            data-type="all"
         >
            <i class="bi bi-grid"></i>
            All
            <span>{{ number_format($typeCounts['all']) }}</span>
         </button>
         <button
            type="button"
            class="{{ request('type') == 'tour' ? 'active' : '' }}"
            data-type="tour"
         >
            <i class="bi bi-map"></i>
            Tours
            <span>{{ number_format($typeCounts['tour']) }}</span>
         </button>
        <button
            type="button"
            class="{{ request('type') == 'transfer' ? 'active' : '' }}"
            data-type="transfer"
         >
            <i class="bi bi-car-front"></i>
            Transfers
            <span>{{ number_format($typeCounts['transfer']) }}</span>
         </button>
         <button
            type="button"
            class="{{ request('type') == 'hotel' ? 'active' : '' }}"
            data-type="hotel"
         >
            <i class="bi bi-building"></i>
            Hotels
            <span>{{ number_format($typeCounts['hotel']) }}</span>
         </button>
        <button
            type="button"
            class="{{ request('type') == 'ferry' ? 'active' : '' }}"
            data-type="ferry"
         >
            <i class="bi bi-water"></i>
            Ferry
            <span>{{ number_format($typeCounts['ferry']) }}</span>
         </button>
         <button
            type="button"
            class="{{ request('type') == 'package' ? 'active' : '' }}"
            data-type="package"
         >
            <i class="bi bi-suitcase"></i>
            Packages
            <span>{{ number_format($typeCounts['package']) }}</span>
         </button>
      </div>
      {{-- Toolbar --}}
      <div class="booking-toolbar">
         <div class="booking-search">
            <i class="bi bi-search"></i>
            <input
               type="search"
               id="bookingSearch"
               value="{{ request('search') }}"
               placeholder="Search Booking ID, customer, agent, product or email..."
            />
         </div>
         <div class="booking-filter-toggle">
            <button type="button" id="showBookingFilters">
               <i class="bi bi-funnel"></i> Filters <span id="activeFilterCount">0</span>
            </button>
         </div>
      </div>
      {{-- Filter Panel --}}
      <div class="booking-filter-panel" id="bookingFilterPanel">
         <div class="filter-field">
            <label for="bookingFromDate">Booking From</label>
           <input type="date" class="form-control" id="bookingFromDate" value="{{ request('booking_from', date('Y-m-d')) }}" />
         </div>
         <div class="filter-field">
            <label for="bookingToDate">Booking To</label>
            <input
               type="date"
               class="form-control"
               id="bookingToDate"
               value="{{ request('booking_to') }}"
            />
         </div>
         <div class="filter-field">
            <label for="travelFromDate">Travel From</label>
            <input
               type="date"
               class="form-control"
               id="travelFromDate"
               value="{{ request('travel_from') }}"
            />
         </div>
         <div class="filter-field">
            <label for="travelToDate">Travel To</label> 
            <input
               type="date"
               class="form-control"
               id="travelToDate"
               value="{{ request('travel_to') }}"
            />
         </div>
         {{-- STATUS STATIC --}}
         <div class="filter-field">
            <label for="bookingStatusFilter">Booking Status</label>
           <select class="form-select" id="bookingStatusFilter">
            <option
               value="all"
               {{ request('status', 'all') == 'all' ? 'selected' : '' }}
            >
               All Status
            </option>

            <option
               value="confirmed"
               {{ request('status') == 'confirmed' ? 'selected' : '' }}
            >
               Confirmed
            </option>

            <option
               value="pending"
               {{ request('status') == 'pending' ? 'selected' : '' }}
            >
               Pending
            </option>

            <option
               value="on-hold"
               {{ request('status') == 'on-hold' ? 'selected' : '' }}
            >
               On Hold
            </option>

            <option
               value="cancelled"
               {{ request('status') == 'cancelled' ? 'selected' : '' }}
            >
               Cancelled
            </option>

         </select>
         </div>
         {{-- SOURCE DYNAMIC --}}
         <div class="filter-field">
            <label for="bookingSourceFilter">Source</label>
            <select class="form-select" id="bookingSourceFilter">
            <option
               value="all"
               {{ request('source', 'all') == 'all' ? 'selected' : '' }}
            >
               All Sources
            </option>
            @foreach($sources as $source)
               <option
                     value="{{ strtolower(trim($source)) }}"
                     {{ request('source') == strtolower(trim($source)) ? 'selected' : '' }}
               >
                     {{ $source }}
               </option>
            @endforeach
         </select>
         </div>
         <div class="filter-buttons">
            <!-- <button type="button" class="apply-filter-button" id="applyBookingFilter">
               <i class="bi bi-check2"></i> Apply
            </button> -->
            <button type="button" class="reset-filter-button" id="resetBookingFilter">
               <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
         </div>
      </div>
      {{-- Table --}}
      <div class="table-responsive">
         <table class="table booking-table align-middle mb-0" id="bookingTable">
            <thead>
               <tr>
                  <th>Booking &amp; Product</th>
                  <th>Travel Details</th>
                  <th>Guests</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Customer</th>
                  <th>Agent / Source</th>
                  <!-- <th>Action</th> -->
               </tr>
            </thead>
            <tbody>
               @forelse($bookings as $booking) 
               @php $type = strtolower( trim( $booking->type ?? 'tour' ) ); 
               $customer =
               trim( ($booking->first_name ?? '') . ' ' . ($booking->last_name ?? '') ); $product = $booking->item_name
               ?: $booking->HotelName ?: 'Booking'; if ($type == 'hotel') { $location = $booking->HotelAddress ?:
               $booking->HotelName ?: 'N/A'; } elseif ($type == 'transfer') { $location = $booking->TransferFrom ?:
               $booking->TransferFromAddress ?: $booking->from ?: $booking->to ?: 'N/A'; } else { if (
               !empty($booking->from) && !empty($booking->to) ) { $location = $booking->from . ' → ' . $booking->to; }
               else { $location = $booking->from ?: $booking->to ?: 'N/A'; } } if ( strtolower( trim( (string)
               $booking->userRole ) ) == 'agent' && !empty($booking->userID) ) { $agentName = $agents[$booking->userID]
               ?? 'N/A'; $agentId = 'AGT-' . $booking->userID; } else { $agentName = 'Direct'; $agentId = 'Direct'; }
               $source = $booking->source ?: 'Manual'; $statusValue = strtolower( trim( (string) $booking->status ) );
               if (in_array( $statusValue, [ '1', 'confirmed', 'confirm', 'success', 'successful', 'complete',
               'completed' ] )) { $status = 'Confirmed'; $statusClass = 'confirmed'; } elseif (in_array( $statusValue, [
               '0', 'pending' ] )) { $status = 'Pending'; $statusClass = 'pending'; } elseif (in_array( $statusValue, [
               'hold', 'on hold', 'on-hold' ] )) { $status = 'On Hold'; $statusClass = 'on-hold'; } elseif (in_array(
               $statusValue, [ 'cancel', 'cancelled', 'canceled' ] )) { $status = 'Cancelled'; $statusClass =
               'cancelled'; } else { $status = ucfirst( $booking->status ?? 'Pending' ); $statusClass = strtolower(
               str_replace( ' ', '-', $status ) ); } $icon = match ($type) { 'tour' => 'bi-map', 'transfer' =>
               'bi-car-front', 'hotel' => 'bi-building', 'ferry' => 'bi-water', 'package' => 'bi-suitcase', default =>
               'bi-grid', }; $payment = $booking->payment_status ?: 'N/A'; $paymentClass = strtolower( preg_replace(
               '/[^a-z0-9]+/i', '-', $payment ) ); @endphp
               <tr
                  data-type="{{ $type }}"
                  data-status="{{ $statusClass }}"
                  data-source="{{ strtolower(str_replace(' ', '-', $source)) }}"
                  data-booking-date="{{ $booking->date ?? '' }}"
                  data-travel-date="{{ $booking->start_date ?? '' }}"
                  data-amount="{{ number_format((float) ($booking->total_amount ?? 0), 2, '.', '') }}"
               >
                  {{-- Booking --}}
                  <td>
                     <div class="booking-product-cell">
                        <span class="product-thumbnail {{ $type }}"><i class="bi {{ $icon }}"></i></span>
                        <div>
                           <span class="booking-type-label">{{ ucfirst($type) }}</span>
                           <h3>{{ $product }}</h3>
                           <p>
                              <strong>SLW-{{ $booking->id }}</strong> · Ref: {{ $booking->booking_reference ?? 'N/A' }}
                           </p>
                        </div>
                     </div>
                  </td>
                  {{-- Travel --}}
                  <td>
                     <span class="location-text"><i class="bi bi-geo-alt"></i> {{ $location }}</span>
                     <small
                        >Travel:
                        <b
                           >@if( !empty( $booking->start_date ) ) {{ date( 'd M Y', strtotime( $booking->start_date ) )
                           }} @else N/A @endif</b
                        ></small
                     >
                     <small
                        >Booked: @if( !empty( $booking->date ) ) {{ date( 'd M Y', strtotime( $booking->date ) ) }}
                        @else N/A @endif</small
                     >
                  </td>
                  {{-- Guests --}}
                  <td>
                     <span class="guest-text"
                        ><i class="bi bi-person"></i> {{ (int) ( $booking->qty_adult ?? 0 ) }} Adult</span
                     >
                     <span class="guest-text"
                        ><i class="bi bi-person-standing"></i> {{ (int) ( $booking->qty_child ?? 0 ) }} Child</span
                     >
                  </td>
                  {{-- Amount --}}
                  <td>
                     <strong class="booking-amount"
                        >{{ $booking->currency ?? '$' }} {{ number_format( (float) ( $booking->total_amount ?? 0 ), 2 )
                        }}</strong
                     >
                     <span class="payment-status {{ $paymentClass }}">{{ $payment }}</span>
                  </td>
                  {{-- Status --}}
                  <td>
                     <span class="booking-status {{ $statusClass }}"><i></i> {{ $status }}</span>
                  </td>
                   {{-- Customer --}}
                  <td>
                     <strong>{{ $customer ?: 'Guest' }}</strong>
                     <small><i class="bi bi-telephone"></i> {{ $booking->phone ?? 'N/A' }}</small>
                     <small><i class="bi bi-envelope"></i> {{ $booking->email ?? 'N/A' }}</small>
                  </td>
                  {{-- Agent --}}
                  <td>
                     <strong>{{ $agentName }}</strong> <small>{{ $agentId }}</small>
                     <span class="source-badge">{{ $source }}</span>
                  </td>
                  {{-- Actions --}}
                  <!-- <td>
                     <div class="booking-actions">
                        <a href="#" class="view-booking" title="View Details" data-id="{{ $booking->id }}"
                           ><i class="bi bi-eye"></i
                        ></a>
                        <button type="button" title="Amend / Edit" data-id="{{ $booking->id }}">
                           <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" title="Messages" class="message-button" data-id="{{ $booking->id }}">
                           <i class="bi bi-chat-dots"></i>
                        </button>
                        <div class="dropdown">
                           <button type="button" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="bi bi-three-dots"></i>
                           </button>
                           <ul class="dropdown-menu dropdown-menu-end">
                              <li>
                                 <a class="dropdown-item" href="#"><i class="bi bi-envelope"></i> Send Confirmation</a>
                              </li>
                              <li>
                                 <a class="dropdown-item" href="#"><i class="bi bi-receipt"></i> View Invoice</a>
                              </li>
                              <li>
                                 <a class="dropdown-item" href="#"><i class="bi bi-cash-coin"></i> Refund Amount</a>
                              </li>
                              <li>
                                 <a class="dropdown-item text-danger" href="#"
                                    ><i class="bi bi-x-circle"></i> Cancel Booking</a
                                 >
                              </li>
                           </ul>
                        </div>
                     </div>
                  </td> -->
               </tr>
               @empty
               <tr>
                  <td colspan="7" class="text-center py-4">No bookings found.</td>
               </tr>
               @endforelse
            </tbody>
            {{-- Visible Total --}}
            <tfoot>
    @php
        $currencyTotals = $bookings
            ->getCollection()
            ->groupBy(function ($booking) {
                return strtoupper(trim($booking->currency ?? '$'));
            })
            ->map(function ($items) {
                return $items->sum(function ($booking) {
                    return (float) ($booking->total_amount ?? 0);
                });
            });
    @endphp

    <tr>
        <td colspan="5" class="booking-total-label">
            <strong>Visible Booking Total</strong>
            <small id="visibleBookingCount">
                {{ $bookings->count() }}
                {{ $bookings->count() == 1 ? 'booking' : 'bookings' }}
            </small>
        </td>

        <td>
            <strong class="booking-total-amount" id="visibleBookingTotal">
                @forelse($currencyTotals as $currency => $total)
                    {{ $currency }} {{ number_format($total, 2) }}
                    @if(!$loop->last) · @endif
                @empty
                    0.00
                @endforelse
            </strong>

            <small>Filter by source/type for totals</small>
        </td>

        <td colspan="1"></td>
    </tr>
</tfoot>
         </table>
      </div>
      {{-- Footer --}}
      <div class="booking-footer">
         <p>
            Showing <strong>{{ $bookings->firstItem() }}–{{ $bookings->lastItem() }}</strong> of
            <strong>{{ $bookings->total() }}</strong> bookings
         </p>
         <nav aria-label="Booking pagination">
            <ul class="pagination pagination-sm mb-0">
               {{-- Previous --}}
               <li class="page-item {{ $bookings->onFirstPage() ? 'disabled' : '' }}">
                  @if($bookings->onFirstPage())
                  <a class="page-link" href="#"><i class="bi bi-chevron-left"></i></a> @else
                  <a
                     class="page-link"
                     href="{{ $bookings->currentPage() - 1 == 1
                                ? route('admin.bookings')
                                : $bookings->url($bookings->currentPage() - 1)
                            }}"
                     ><i class="bi bi-chevron-left"></i
                  ></a>
                  @endif
               </li>
               {{-- Page 1 --}}
               <li class="page-item {{ $bookings->currentPage() == 1 ? 'active' : '' }}">
                  <a class="page-link" href="{{ route('admin.bookings') }}">1</a>
               </li>
               {{-- Page 2 --}} @if($bookings->lastPage() >= 2)
               <li class="page-item {{ $bookings->currentPage() == 2 ? 'active' : '' }}">
                  <a class="page-link" href="{{ $bookings->url(2) }}">2</a>
               </li>
               @endif {{-- Page 3 --}} @if($bookings->lastPage() >= 3)
               <li class="page-item {{ $bookings->currentPage() == 3 ? 'active' : '' }}">
                  <a class="page-link" href="{{ $bookings->url(3) }}">3</a>
               </li>
               @endif {{-- Dots --}} @if($bookings->lastPage() > 4)
               <li class="page-item"><button class="page-link" type="button">...</button></li>
               @endif {{-- Last Page --}} @if($bookings->lastPage() > 3)
               <li class="page-item {{ $bookings->currentPage() == $bookings->lastPage() ? 'active' : '' }}">
                  <a class="page-link" href="{{ $bookings->url($bookings->lastPage()) }}"
                     >{{ $bookings->lastPage() }}</a
                  >
               </li>
               @endif {{-- Next --}}
               <li class="page-item {{ !$bookings->hasMorePages() ? 'disabled' : '' }}">
                  @if($bookings->hasMorePages())
                  <a
                     class="page-link"
                     href="{{ $bookings->currentPage() + 1 == 1
                                ? route('admin.bookings')
                                : $bookings->url($bookings->currentPage() + 1)
                            }}"
                     ><i class="bi bi-chevron-right"></i
                  ></a>
                  @else <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a> @endif
               </li>
            </ul>
         </nav>
      </div>
   </section>
</div>
@endsection
