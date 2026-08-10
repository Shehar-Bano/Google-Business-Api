@extends('layouts/contentNavbarLayout')

@section('title', 'Tables - Orders')
@section('page-script')
    <script src="{{ asset('assets/js/pages-account-settings-account.js') }}"></script>
@endsection
@section('content')


    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="py-3"><span class="text-muted fw-medium">Orders</span>
            </h4>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#largeModal">
                Export
            </button>
        </div>

    </div>

    <div class="card">
        <!-- Filter Section -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body pt-0">
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="breedFilter" class="form-label">Breed</label>
                    <select id="breedFilter" class="form-select">
                        <option value="">All Breeds</option>
                        <option value="Arabian">Arabian</option>
                        <option value="Angus">Angus</option>
                        <option value="Merino">Merino</option>
                        <option value="Rhode Island Red">Rhode Island Red</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Confirmed">Shipped</option>
                        <option value="Pending">UnShipped</option>

                    </select>
                </div>
                <div class="col-md-4">
                    <label for="orderIdFilter" class="form-label">Order ID</label>
                    <input type="text" id="orderIdFilter" class="form-control" placeholder="Search by Order ID">
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-responsive text-nowrap">
            <table class="table table-hover" id="ordersTable">
                <!-- Your existing table content -->
            </table>
        </div>
    </div>


    <!-- Hoverable Table rows -->
    <div class="card">

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" class="form-check-input"
                                style="width: 18px; height: 18px;">
                        </th>
                        <th>Order Id</th>
                        <th>Name</th>
                        <th>Breed</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input">
                        </td>
                        <td class="small-text">123456789</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://carutharabians.com/wp-content/uploads/2023/05/CA-Rromance-_3.webp"
                                    class="rounded-circle me-3" alt="Product Image" width="40" height="40">
                                <span class="fw-medium" role="button" data-bs-toggle="modal"
                                    data-bs-target="#orderDetailsModal"
                                    data-image="https://carutharabians.com/wp-content/uploads/2023/05/CA-Rromance-_3.webp"
                                    data-name="Arainy" data-breed="Arabian" data-quantity="5" data-status="Confirmed"
                                    data-date="2023-05-15" data-shipping="123 Desert Rd, Dubai, UAE"
                                    data-expected-date="2023-05-20" data-tracking="UAE123456789" data-carrier="DHL"
                                    data-notes="Please contact recipient before delivery">Arainy</span>
                            </div>
                        </td>
                        <td class="small-text">Arabian</td>
                        <td class="small-text">5</td>
                        <td class="small-text"><span
                                class="badge rounded-pill bg-label-primary me-1 small-text">Shipped</span></td>
                        <td class="small-text">2023-05-15</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-pencil-outline me-1"></i> Edit</a>

                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-trash-can-outline me-1"></i> Confirm Failure</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-download me-1"></i> Download Invoice</a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-action="mark-dispensed"><i
                                            class="mdi mdi-align-vertical-distribute me-1"></i> Mark Dispencesed</a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-action="mark-failure">
                                        <i class="mdi mdi-message-alert-outline me-1"></i> Mark Failure
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input">
                        </td>
                        <td class="small-text">123456789</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://carutharabians.com/wp-content/uploads/2023/05/CA-Rromance-_3.webp"
                                    class="rounded-circle me-3" alt="Product Image" width="40" height="40">
                                <span class="fw-medium" role="button" data-bs-toggle="modal"
                                    data-bs-target="#orderDetailsModal"
                                    data-image="https://carutharabians.com/wp-content/uploads/2023/05/CA-Rromance-_3.webp"
                                    data-name="Arainy" data-breed="Arabian" data-quantity="5" data-status="Pending"
                                    data-date="2023-05-15" data-shipping="123 Desert Rd, Dubai, UAE"
                                    data-expected-date="2023-05-20" data-tracking="UAE123456789" data-carrier="DHL"
                                    data-notes="Please contact recipient before delivery">Arainy</span>
                            </div>
                        </td>

                        <td class="small-text">Angus</td>
                        <td class="small-text">12</td>
                        <td class="small-text"><span
                                class="badge rounded-pill bg-label-warning me-1 small-text">UnShipped</span></td>
                        <td class="small-text">2023-06-20</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-pencil-outline me-1"></i> Edit</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-trash-can-outline me-1"></i> Confirm Failure</a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-action="mark-dispensed"><i
                                            class="mdi mdi-align-vertical-distribute me-1"></i> Mark Dispencesed</a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-action="mark-failure">
                                        <i class="mdi mdi-message-alert-outline me-1"></i> Mark Failure
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input">
                        </td>
                        <td class="small-text">123456789</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://carutharabians.com/wp-content/uploads/2023/05/CA-Rromance-_3.webp"
                                    class="rounded-circle me-3" alt="Product Image" width="40" height="40">
                                <span class="fw-medium" role="button" data-bs-toggle="modal"
                                    data-bs-target="#orderDetailsModal"
                                    data-image="https://carutharabians.com/wp-content/uploads/2023/05/CA-Rromance-_3.webp"
                                    data-name="Arainy" data-breed="Arabian" data-quantity="5"
                                    data-status="Waiting
                                for
                                Approval"
                                    data-date="2023-05-15" data-shipping="123 Desert Rd, Dubai, UAE"
                                    data-expected-date="2023-05-20" data-tracking="UAE123456789" data-carrier="DHL"
                                    data-notes="Please contact recipient before delivery">Arainy</span>
                            </div>
                        </td>


                        <td class="small-text">Merino</td>
                        <td class="small-text">8</td>
                        <td class="small-text"><span
                                class="badge rounded-pill bg-label-warning me-1 small-text">UnShipped</span></td>
                        <td class="small-text">2023-07-10</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-pencil-outline me-1"></i> Edit</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-trash-can-outline me-1"></i> Confirm Failure</a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-action="mark-dispensed"><i
                                            class="mdi mdi-align-vertical-distribute me-1"></i> Mark Dispencesed</a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-action="mark-failure">
                                        <i class="mdi mdi-message-alert-outline me-1"></i> Mark Failure
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input">
                        </td>
                        <td class="small-text">123456789</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://carutharabians.com/wp-content/uploads/2023/05/CA-Rromance-_3.webp"
                                    class="rounded-circle me-3" alt="Product Image" width="40" height="40">
                                <span class="fw-medium" role="button" data-bs-toggle="modal"
                                    data-bs-target="#orderDetailsModal"
                                    data-image="https://carutharabians.com/wp-content/uploads/2023/05/CA-Rromance-_3.webp"
                                    data-name="Arainy" data-breed="Arabian" data-quantity="5" data-status="Confirmed"
                                    data-date="2023-05-15" data-shipping="123 Desert Rd, Dubai, UAE"
                                    data-expected-date="2023-05-20" data-tracking="UAE123456789" data-carrier="DHL"
                                    data-notes="Please contact recipient before delivery">Arainy</span>
                            </div>
                        </td>
                        <td class="small-text">Rhode Island Red</td>
                        <td class="small-text">25</td>
                        <td class="small-text"><span
                                class="badge rounded-pill bg-label-primary me-1 small-text">Shipped</span></td>
                        <td class="small-text">2023-08-05</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-pencil-outline me-1"></i> Edit</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-trash-can-outline me-1"></i> Confirm Failure</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="mdi mdi-download me-1"></i> Download Invoice</a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-action="mark-dispensed"><i
                                            class="mdi mdi-align-vertical-distribute me-1"></i> Mark Dispencesed</a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-action="mark-failure">
                                        <i class="mdi mdi-message-alert-outline me-1"></i> Mark Failure
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details  </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img id="detailImage" src="" class="img-fluid rounded mb-3" alt="Product Image">
                        </div>
                        <div class="col-md-8">
                            <h4 id="detailName" class="mb-3"></h4>
                            <div class="row">
                                <div class="col-5">
                                    <p class="mb-1"><strong>Breed:</strong> <span id="detailBreed"></span></p>
                                    <p class="mb-1"><strong>Quantity:</strong> <span id="detailQuantity"></span></p>
                                    <p class="mb-1"><strong>Status:</strong> <span id="detailStatus"></span></p>
                                    <p class="mb-1"><strong>Order Date:</strong> <span id="detailDate"></span></p>
                                </div>
                                <div class="col-7">
                                    <p class="mb-1"><strong>Expected Shipping:</strong> <span
                                            id="detailExpectedDate"></span></p>
                                    <p class="mb-1"><strong>Tracking Number:</strong> <span id="detailTracking"></span>
                                    </p>
                                    <p class="mb-1"><strong>Carrier:</strong> <span id="detailCarrier"></span></p>
                                    <div class="mb-1">
                                        <label for="retryCount" class="form-label"><strong>Retry Count:</strong></label>
                                        <input type="number" id="retryCount"
                                            class="form-control form-control-sm d-inline-block w-auto" value="0"
                                            min="0">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <p class="mb-1"><strong> Shipping Address:</strong></p>
                            <p id="detailShipping" class="text-muted"></p>
                            <p class="mb-1"><strong> Notes:</strong></p>
                            <p id="detailNotes" class="text-muted"></p>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Generate Invoice</button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="largeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel3">New Products</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar"
                                class="d-block w-px-120 h-px-120 rounded" id="uploadedAvatar" />
                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-2 mb-3" tabindex="0">
                                    <span class="d-none d-sm-block">Add Photo</span>
                                    <i class="mdi mdi-tray-arrow-up d-block d-sm-none"></i>
                                    <input type="file" id="upload" class="account-file-input" hidden
                                        accept="image/png, image/jpeg" />
                                </label>
                                <button type="button" class="btn btn-outline-danger account-image-reset mb-3">
                                    <i class="mdi mdi-reload d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                </button>

                                <div class="text-muted small">Allowed JPG, GIF or PNG. Max size of 800K</div>
                            </div>
                        </div>
                        <div class="input-group input-group-merge mb-4 mt-4">
                            <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                    class="mdi mdi-account-outline"></i></span>
                            <input type="text" class="form-control" id="basic-icon-default-fullname"
                                placeholder="Product Name" aria-label="Product Name"
                                aria-describedby="basic-icon-default-fullname2" />
                        </div>
                        <div class="input-group input-group-merge mb-4">
                            <span class="input-group-text" id="client-icon">
                                <i class="mdi mdi-account-outline"></i>
                            </span>
                            <select class="form-select" aria-label="Default select example"
                                aria-placeholder="Horse Breed">
                                <option value="" disabled selected hidden>Select a Breed</option>
                                <option value="1">John Doe</option>
                                <option value="2">Jane Smith</option>
                                <option value="3">Michael Johnson</option>
                                <option value="4">Emily Davis</option>
                                <option value="5">Chris Thompson</option>
                            </select>

                        </div>
                        <div class="input-group input-group-merge mb-4">
                            <span class="input-group-text">
                                <i class="mdi mdi-calendar"></i>
                            </span>
                            <input type="text" class="form-control" id="date-range-picker"
                                placeholder="Select date range">
                        </div>

                        <div class="input-group input-group-merge mb-4">
                            <span id="basic-icon-default-phone2" class="input-group-text"><i
                                    class="mdi mdi-calculator-variant-outline"></i></span>
                            <input type="text" id="basic-icon-default-phone" class="form-control phone-mask"
                                placeholder="Quantity" aria-label="Quantity"
                                aria-describedby="basic-icon-default-phone2" />
                        </div>
                        {{-- <div class="input-group input-group-merge mb-4">
                            <span id="basic-icon-default-message2" class="input-group-text"><i
                                    class="mdi mdi-message-outline"></i></span>
                            <textarea id="basic-icon-default-message" class="form-control" placeholder="Message" aria-label="Message"
                                aria-describedby="basic-icon-default-message2" style="height: 60px;"></textarea>
                        </div> --}}
                        {{-- <button type="submit" class="btn btn-primary">Send</button> --}}
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <!--/ Hoverable Table rows -->
    <!-- Mark Dispensed Modal -->
    <div class="modal fade" id="markDispensedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Mark Order as Dispensed</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="dispensedForm">
                        <div class="mb-3">
                            <label class="form-label">Order ID</label>
                            <input type="text" class="form-control" id="dispensedOrderId" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" rows="3" placeholder="Enter additional notes..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mark Failure Modal -->
    <div class="modal fade" id="markFailureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Mark Order as Failed</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="failureForm">
                        <div class="mb-3">
                            <label class="form-label">Order ID</label>
                            <input type="text" class="form-control" id="failureOrderId" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Failure</label>
                            <textarea class="form-control" rows="3" placeholder="Enter reason for failure..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">Submit Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



@endsection
@push('my-styles')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@push('my-script')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(function() {
            $('#date-range-picker').daterangepicker({
                opens: 'right',
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: 'Apply',
                    cancelLabel: 'Cancel',
                    fromLabel: 'From',
                    toLabel: 'To',
                    customRangeLabel: 'Custom',
                    daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
                        'September', 'October', 'November', 'December'
                    ],
                    firstDay: 1
                }
            });
        });
    </script>
    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('tbody .form-check-input');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
    <script>
        const statusStyles = {
            'Confirmed': 'bg-label-primary',
            'Pending': 'bg-label-warning',
            'Waiting for Approval': 'bg-label-secondary',
            'Shipped': 'bg-label-success',
            'Cancelled': 'bg-label-danger'
        };
        // Order Details Modal Handler
        document.getElementById('orderDetailsModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const modal = this;
            const status = button.dataset.status;
            const statusClass = statusStyles[status] || 'bg-label-primary';
            // Update modal content with data attributes
            modal.querySelector('#detailImage').src = button.dataset.image;
            modal.querySelector('#detailName').textContent = button.dataset.name;
            modal.querySelector('#detailBreed').textContent = button.dataset.breed;
            modal.querySelector('#detailQuantity').textContent = button.dataset.quantity;
            modal.querySelector('#detailStatus').innerHTML =
                `<span class="badge ${statusClass}">${status}</span>`;
            modal.querySelector('#detailDate').textContent = button.dataset.date;
            modal.querySelector('#detailShipping').textContent = button.dataset.shipping;
            modal.querySelector('#detailExpectedDate').textContent = button.dataset.expectedDate;
            modal.querySelector('#detailTracking').textContent = button.dataset.tracking;
            modal.querySelector('#detailCarrier').textContent = button.dataset.carrier;
            modal.querySelector('#detailNotes').textContent = button.dataset.notes;
        });
    </script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable if you're using it
            // const table = $('#ordersTable').DataTable();

            // Filter functionality
            $('#breedFilter, #statusFilter, #orderIdFilter').on('change keyup', function() {
                const breed = $('#breedFilter').val().toLowerCase();
                const status = $('#statusFilter').val().toLowerCase();
                const orderId = $('#orderIdFilter').val().toLowerCase();

                $('#ordersTable tbody tr').each(function() {
                    const rowBreed = $(this).find('td:nth-child(3)').text().toLowerCase();
                    const rowStatus = $(this).find('td:nth-child(5)').text().toLowerCase();
                    const rowOrderId = $(this).find('td:nth-child(1)').text().toLowerCase();

                    const breedMatch = breed === '' || rowBreed.includes(breed);
                    const statusMatch = status === '' || rowStatus.includes(status);
                    const orderIdMatch = orderId === '' || rowOrderId.includes(orderId);

                    if (breedMatch && statusMatch && orderIdMatch) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Mark Dispensed
            document.querySelectorAll('[data-action="mark-dispensed"]').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const orderId = row.querySelector('.small-text:first-child').textContent;
                    const modal = new bootstrap.Modal(document.getElementById(
                        'markDispensedModal'));
                    document.getElementById('dispensedOrderId').value = orderId;
                    modal.show();
                });
            });

            // Handle Mark Failure
            document.querySelectorAll('[data-action="mark-failure"]').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const orderId = row.querySelector('.small-text:first-child').textContent;
                    const modal = new bootstrap.Modal(document.getElementById('markFailureModal'));
                    document.getElementById('failureOrderId').value = orderId;
                    modal.show();
                });
            });

            // Form submissions
            document.getElementById('dispensedForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // Handle dispensed form submission
                const orderId = document.getElementById('dispensedOrderId').value;
                const notes = this.querySelector('textarea').value;
                console.log('Mark Dispensed:', orderId, notes);
                bootstrap.Modal.getInstance(document.getElementById('markDispensedModal')).hide();
            });

            document.getElementById('failureForm').addEventListener('submit', function(e) {
                e.preventDefault();
                // Handle failure form submission
                const orderId = document.getElementById('failureOrderId').value;
                const reason = this.querySelector('textarea').value;
                console.log('Mark Failure:', orderId, reason);
                bootstrap.Modal.getInstance(document.getElementById('markFailureModal')).hide();
            });
        });
    </script>
@endpush
