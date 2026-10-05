@extends('admin.layouts.master')
@section('content')
@include('admin.partials.notification')
<div class="bulk-email-page">
    <nav class="page-breadcrumb"><a href="agent-list.php">Agents</a> 
        <i class="bi bi-chevron-right"></i> 
        <span>Send Bulk Email</span>
    </nav>
    <section class="page-heading email-page-heading">
        <div>
        <p>Agent Communication</p>
        <h1>Send Email to Agents</h1>
        <span>Create an email and send it to selected B2B agents.</span>
        </div>
        <button type="button" class="draft-button"><i class="bi bi-folder2-open"></i> View Drafts</button>
    </section>
    <section class="email-stats-grid">
        <article>
        <span class="stat-icon blue"><i class="bi bi-people"></i></span>
        <div>
            <p>Total Agents</p>
            <h2>{{ $totalAgents }}</h2>
        </div>
        </article>
        <article>
        <span class="stat-icon green"><i class="bi bi-person-check"></i></span>
        <div>
            <p>Active Agents</p>
            <h2>{{ $activeAgents }}</h2>
        </div>
        </article>
        <article>
        <span class="stat-icon orange"><i class="bi bi-check2-square"></i></span>
        <div>
            <p>Selected Recipients</p>
            <h2 id="selectedAgentStat">0</h2>
        </div>
        </article>
        <article>
        <span class="stat-icon purple"><i class="bi bi-send-check"></i></span>
        <div>
            <p>Emails Sent Today</p>
            <h2>126</h2>
        </div>
        </article>
    </section>
    <form id="bulkEmailForm" action="{{ url('admin/send-bulk-email') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="email-layout">
        <div class="email-compose-column">
            <section class="content-card compose-card">
            <div class="card-heading">
                <div>
                <h3><i class="bi bi-pencil-square"></i> Compose Email</h3>
                <p>Enter the message details below.</p>
                </div>
                <span class="autosave-status"><i class="bi bi-cloud-check"></i> Draft saved</span>
            </div>
            <div class="compose-form">
                <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="senderName">From Name</label> <input type="text" class="form-control" id="senderName" name="sender_name" value="Sun Leisure World" required></div>
                <div class="col-md-6"><label class="form-label" for="replyTo">Reply-to Email</label> <input type="email" class="form-control" id="replyTo" name="reply_to" value="tech@sunleisureworld.com" required></div>
                <div class="col-12">
                    <label class="form-label" for="emailTemplate">Email Template</label>
                    <select class="form-select" id="emailTemplate" name="template">
                    <option value="custom">Custom Message</option>
                    <option value="announcement">Portal Announcement</option>
                    <option value="promotion">Special Offer / Promotion</option>
                    <option value="maintenance">Scheduled Maintenance</option>
                    <option value="training">Agent Training Invitation</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="emailSubject">Subject <span>*</span></label>
                    <div class="subject-field"><i class="bi bi-type"></i> <input type="text" class="form-control" id="emailSubject" name="subject" maxlength="150" placeholder="Enter email subject" required> <small><b id="subjectCount">0</b>/150</small></div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="emailBody">Email Body <span>*</span></label>
                    <div class="email-editor">
                    <div class="editor-toolbar" role="toolbar" aria-label="Email formatting"><button type="button" data-command="bold" title="Bold"><i class="bi bi-type-bold"></i></button> <button type="button" data-command="italic" title="Italic"><i class="bi bi-type-italic"></i></button> <button type="button" data-command="underline" title="Underline"><i class="bi bi-type-underline"></i></button> <span></span> <button type="button" data-command="insertUnorderedList" title="Bullet list"><i class="bi bi-list-ul"></i></button> <button type="button" data-command="insertOrderedList" title="Numbered list"><i class="bi bi-list-ol"></i></button> <button type="button" title="Add link"><i class="bi bi-link-45deg"></i></button></div>
                    <textarea id="emailBody" name="body" rows="11" placeholder="Write your email message here..." required></textarea>
                    <div class="editor-footer"><span>Use <code>{agent_name}</code> or <code>{company_name}</code> for personalization.</span> <small><b id="bodyCount">0</b> characters</small></div>
                    </div>
                </div>
                <div class="col-12"><label class="form-label" for="emailAttachment">Attachment <small>(Optional)</small></label> <label class="attachment-box" for="emailAttachment"><i class="bi bi-paperclip"></i> <span><strong id="attachmentName">Choose a file</strong><small>PDF, JPG, PNG or DOCX · Maximum 10 MB</small></span> <em>Browse</em></label> <input type="file" id="emailAttachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" hidden></div>
                </div>
            </div>
            </section>
        </div>
        <aside class="email-side-column">
            <section class="content-card recipient-card">
            <div class="card-heading">
                <div>
                <h3><i class="bi bi-people"></i> Recipients</h3>
                <p>Select agents who will receive this email.</p>
                </div>
            </div>
            <div class="recipient-content">
                <label class="form-label">Send Email To <span>*</span></label>
                <div class="agent-select" id="agentSelect">
                <button type="button" class="agent-select-button" id="agentSelectButton" aria-expanded="false"><span><i class="bi bi-person-plus"></i><b id="agentSelectLabel">Choose agents</b></span> <i class="bi bi-chevron-down select-arrow"></i></button>
                <div class="agent-dropdown" id="agentDropdown">
                    <div class="agent-search"><i class="bi bi-search"></i><input type="search" id="agentSearch" placeholder="Search name, ID or email..."></div>
                    <div class="agent-filter-row">
                    <select id="agentStatusFilter" aria-label="Filter by status">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select id="agentCountryFilter" aria-label="Filter by country">
                        <option value="all">All Countries</option>
                        @foreach($countries as $country)
                        <option value="{{ strtolower($country) }}">{{ $country }}</option>
                        @endforeach
                    </select>
                    </div>
                    <label class="select-all-row"><input type="checkbox" id="selectAllAgents"><span><b>Select All Visible Agents</b><small id="visibleAgentCount">5 agents available</small></span></label>
                    <div class="agent-option-list" id="agentOptionList">@foreach($agents as $agent) @php $status = (int) $agent->isactive === 1 ? 'active' : ((int) $agent->isactive === 0 ? 'inactive' : 'pending'); $statusText = ucfirst($status); $initials = collect(explode(' ', $agent->name ?? 'Agent')) ->filter() ->map(fn($word) => strtoupper(substr($word, 0, 1))) ->take(2) ->implode(''); $searchText = strtolower( ($agent->name ?? '') . ' ' . 'AGT-' . $agent->add_agent_id . ' ' . ($agent->email ?? '') ); @endphp <label class="agent-option"
                data-search="{{ $searchText }}"
                data-status="{{ $status }}"
                data-country="{{ strtolower($agent->country ?? '') }}"><input type="checkbox"
                    class="agent-checkbox"
                    name="agents[]"
                    value="{{ $agent->add_agent_id }}"> <span class="agent-avatar violet">{{ $initials }}</span> <span class="agent-info"><b>{{ $agent->name ?? 'N/A' }}</b> <small>AGT-{{ $agent->add_agent_id }} · {{ $agent->email ?? 'N/A' }}</small></span> <em class="{{ $status }}">{{ $statusText }}</em></label> @endforeach</div>
                    <div class="agent-dropdown-footer"><button type="button" id="clearAgentSelection">Clear</button><button type="button" id="applyAgentSelection">Apply Selection</button></div>
                </div>
                </div>
                <div class="selected-summary">
                <span><i class="bi bi-check-circle"></i></span>
                <div><strong><b id="selectedAgentCount">0</b> agents selected</strong><small>Email addresses are protected and sent individually.</small></div>
                </div>
                <div class="delivery-options"><label><input type="checkbox" name="only_active" id="onlyActiveAgents"><span><b>Active agents only</b><small>Skip pending and inactive accounts</small></span></label> <label><input type="checkbox" name="send_copy"><span><b>Send me a copy</b><small>Copy this email to the admin address</small></span></label></div>
            </div>
            </section>
            <section class="content-card delivery-card">
            <span class="delivery-icon"><i class="bi bi-shield-check"></i></span>
            <div>
                <h4>Safe Bulk Delivery</h4>
                <p>Every agent receives a separate email. Recipient email addresses will not be visible to other agents.</p>
            </div>
            </section>
        </aside>
        </div>
        <section class="email-action-bar">
        <p><i class="bi bi-info-circle"></i> Please review subject, message and recipients before sending.</p>
        <div><button type="button" class="secondary-action" id="saveDraftButton"><i class="bi bi-floppy"></i> Save Draft</button> <button type="button" class="secondary-action" data-bs-toggle="modal" data-bs-target="#testEmailModal"><i class="bi bi-envelope-check"></i> Send Test</button> <button type="submit" class="primary-action"><i class="bi bi-send"></i> Review &amp; Send</button></div>
        </section>
    </form>
  @endsection
</div>