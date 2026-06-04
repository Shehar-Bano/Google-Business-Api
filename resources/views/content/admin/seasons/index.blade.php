@extends('layouts/contentNavbarLayout')

@section('title', 'Seasons')

@section('page-script')
    <script src="{{ asset('assets/js/pages-account-settings-account.js') }}"></script>
@endsection

@section('content')
<div class="admin-page">

    {{-- Page Header --}}
    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Seasons</h4>
            <p class="admin-page-header__subtitle">Manage breeding seasons, contracts, and seasonal scheduling.</p>
        </div>
        <div class="admin-page-header__actions">
            <button type="button" class="admin-btn-primary" data-bs-toggle="modal" data-bs-target="#largeModal">
                <i class="mdi mdi-plus"></i> Add Season
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="admin-filter-card">
        <span class="admin-filter-card__label">Filter Seasons</span>
        <div class="admin-filter-grid">
            <div>
                <label for="statusFilter" class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Confirmed">Signed</option>
                    <option value="Pending">Un Signed</option>
                </select>
            </div>
            <div>
                <label for="fromDateFilter" class="form-label">From</label>
                <input type="date" id="fromDateFilter" class="form-control">
            </div>
            <div>
                <label for="toDateFilter" class="form-label">To</label>
                <input type="date" id="toDateFilter" class="form-control">
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="admin-table-card">
        <div class="admin-card-header">
            <div>
                <h5 class="admin-card-header__title">Season List</h5>
                <p class="admin-card-header__subtitle">All seasons with contract status and creation date.</p>
            </div>
            <span class="admin-card-header__meta">Total <strong>4</strong> seasons</span>
        </div>
        <div class="table-responsive">
            <table class="admin-table" id="ordersTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th>Contract ID</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td class="cell-primary">123456789</td>
                        <td><span class="admin-badge admin-badge-primary">Signed</span></td>
                        <td class="cell-muted">2023-05-15</td>
                        <td class="col-actions">
                            <div class="dropdown">
                                <button type="button" class="admin-action-btn dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item view-contract" href="javascript:void(0);"
                                        data-contract-id="123456789" data-status="Signed" data-created-at="2023-05-15"
                                        data-signed-by="John Doe" data-signed-date="2023-05-15"
                                        data-signature-image="/path/to/signature.png"
                                        data-contract-text="This is the signed contract content...">
                                        <i class="mdi mdi-eye-outline me-1"></i> View
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);">
                                        <i class="mdi mdi-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td class="cell-primary">987654321</td>
                        <td><span class="admin-badge admin-badge-warning">Un Signed</span></td>
                        <td class="cell-muted">2023-06-20</td>
                        <td class="col-actions">
                            <div class="dropdown">
                                <button type="button" class="admin-action-btn dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item view-contract" href="javascript:void(0);"
                                        data-contract-id="987654321" data-status="Un Signed" data-created-at="2023-06-20"
                                        data-contract-text="This is the unsigned contract content...">
                                        <i class="mdi mdi-pencil-outline me-1"></i> Sign
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);">
                                        <i class="mdi mdi-eye-outline me-1"></i> View
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td class="cell-primary">112233445</td>
                        <td><span class="admin-badge admin-badge-warning">Un Signed</span></td>
                        <td class="cell-muted">2023-07-10</td>
                        <td class="col-actions">
                            <div class="dropdown">
                                <button type="button" class="admin-action-btn dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item view-contract" href="javascript:void(0);"
                                        data-contract-id="987654321" data-status="Un Signed" data-created-at="2023-06-20"
                                        data-contract-text="This is the unsigned contract content...">
                                        <i class="mdi mdi-pencil-outline me-1"></i> Sign
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);">
                                        <i class="mdi mdi-eye-outline me-1"></i> View
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td class="cell-primary">556677889</td>
                        <td><span class="admin-badge admin-badge-primary">Signed</span></td>
                        <td class="cell-muted">2023-08-05</td>
                        <td class="col-actions">
                            <div class="dropdown">
                                <button type="button" class="admin-action-btn dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item view-contract" href="javascript:void(0);"
                                        data-contract-id="123456789" data-status="Signed" data-created-at="2023-05-15"
                                        data-signed-by="John Doe" data-signed-date="2023-05-15"
                                        data-signature-image="/path/to/signature.png"
                                        data-contract-text="This is the signed contract content...">
                                        <i class="mdi mdi-eye-outline me-1"></i> View
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);">
                                        <i class="mdi mdi-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Contract Details Modal --}}
<div class="modal fade" id="contractDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Breeding Season Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <p><strong>Status:</strong> <span id="contractStatus" class="badge">active</span></p>
                            <p><strong>Created At:</strong> <span id="contractCreatedAt"></span></p>
                        </div>
                        <div class="contract-content mb-4 p-3 border rounded bg-light">
                            <div id="contractText"></div>
                            <div id="signatureSection" class="mt-4 d-none">
                                <hr>
                                <h5>Sign Contract</h5>
                                <div class="mb-3">
                                    <label for="signatureName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="signatureName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="signatureDate" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="signatureDate" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Signature</label>
                                    <div id="signaturePad" class="border rounded" style="height: 150px;"></div>
                                    <button type="button" id="clearSignature" class="btn btn-sm btn-secondary mt-2">Clear Signature</button>
                                </div>
                            </div>
                            <div id="signedInfo" class="mt-4 d-none">
                                <hr>
                                <h5>Signature Details</h5>
                                <p><strong>Signed By:</strong> <span id="signedByName"></span></p>
                                <p><strong>Signed On:</strong> <span id="signedDate"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="signContractBtn" class="btn btn-primary d-none">Sign Contract</button>
                <button type="button" id="downloadContractBtn" class="btn btn-success d-none">
                    <i class="mdi mdi-download me-1"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Season Modal --}}
<div class="modal fade" id="largeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Season</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="clientSelect" class="form-label">Select Client</label>
                        <select class="form-select" id="clientSelect">
                            <option value="">Select a client</option>
                            <option value="1">Client 1</option>
                            <option value="2">Client 2</option>
                            <option value="3">Client 3</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs" id="contractTypeTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="create-tab" data-bs-toggle="tab"
                                    data-bs-target="#create" type="button" role="tab" aria-selected="true">Create New</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="upload-tab" data-bs-toggle="tab"
                                    data-bs-target="#upload" type="button" role="tab" aria-selected="false">Upload PDF</button>
                            </li>
                        </ul>
                        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="contractTypeContent">
                            <div class="tab-pane fade show active" id="create" role="tabpanel">
                                <div id="contractEditor" style="min-height: 300px;"></div>
                            </div>
                            <div class="tab-pane fade" id="upload" role="tabpanel">
                                <div class="mb-3">
                                    <label for="contractFile" class="form-label">Upload Contract PDF</label>
                                    <input class="form-control" type="file" id="contractFile" accept=".pdf">
                                </div>
                                <div class="alert alert-info">
                                    <small>Upload a PDF file if you don't want to create a new contract from scratch.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contractTitle" class="form-label">Contract Title</label>
                        <input type="text" class="form-control" id="contractTitle" placeholder="e.g., Service Agreement">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="expiryDate" class="form-label">Expiry Date (Optional)</label>
                        <input type="date" class="form-control" id="expiryDate">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveContractBtn">Save Season</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('my-styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    #signaturePad { border: 1px solid #ddd; background-color: #f8f9fa; cursor: crosshair; }
    .contract-content { max-height: 500px; overflow-y: auto; }
    .tox-tinymce { border-radius: 0 0 6px 6px !important; }
</style>
@endpush

@push('my-script')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.getElementById('selectAll').addEventListener('change', function () {
        document.querySelectorAll('tbody .form-check-input').forEach(cb => cb.checked = this.checked);
    });

    $(document).ready(function () {
        let signaturePad;

        $('#contractDetailsModal').on('shown.bs.modal', function () {
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)', penColor: '#333' });
            }
        });

        $('#clearSignature').on('click', function () { if (signaturePad) signaturePad.clear(); });

        $(document).on('click', '.view-contract', function (e) {
            e.preventDefault();
            const contractId     = $(this).data('contract-id');
            const status         = $(this).data('status');
            const createdAt      = $(this).data('created-at');
            const signedBy       = $(this).data('signed-by') || '';
            const signedDate     = $(this).data('signed-date') || '';
            const contractText   = $(this).data('contract-text');

            $('#contractCreatedAt').text(createdAt);
            $('#contractText').html(contractText);

            const statusBadge = $('#contractStatus');
            statusBadge.text(status).removeClass('bg-label-primary bg-label-warning');

            if (status === 'Signed') {
                statusBadge.addClass('bg-label-primary');
                $('#signedInfo').removeClass('d-none');
                $('#signatureSection').addClass('d-none');
                $('#signedByName').text(signedBy);
                $('#signedDate').text(signedDate);
                $('#signContractBtn').addClass('d-none');
                $('#downloadContractBtn').removeClass('d-none');
            } else {
                statusBadge.addClass('bg-label-warning');
                $('#signedInfo').addClass('d-none');
                $('#signatureSection').removeClass('d-none');
                $('#signContractBtn').removeClass('d-none');
                $('#downloadContractBtn').addClass('d-none');
                $('#signatureDate').val(new Date().toISOString().split('T')[0]);
            }
            $('#contractDetailsModal').modal('show');
        });

        $('#signContractBtn').on('click', function () {
            const signatureName = $('#signatureName').val();
            const signatureDate = $('#signatureDate').val();
            if (!signatureName || !signatureDate || (signaturePad && signaturePad.isEmpty())) {
                alert('Please fill all fields and provide a signature');
                return;
            }
            $('#contractStatus').text('Signed').removeClass('bg-label-warning').addClass('bg-label-primary');
            $('#signedInfo').removeClass('d-none');
            $('#signatureSection').addClass('d-none');
            $('#signedByName').text(signatureName);
            $('#signedDate').text(signatureDate);
            $('#signContractBtn').addClass('d-none');
            $('#downloadContractBtn').removeClass('d-none');
            alert('Contract signed successfully!');
        });

        document.getElementById('saveContractBtn').addEventListener('click', function () {
            const clientId      = document.getElementById('clientSelect').value;
            const contractTitle = document.getElementById('contractTitle').value;
            if (!clientId)      { alert('Please select a client'); return; }
            if (!contractTitle) { alert('Please enter a contract title'); return; }
            alert('Season saved successfully!');
            $('#largeModal').modal('hide');
        });
    });
</script>
@endpush
