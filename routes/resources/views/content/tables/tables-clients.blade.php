@extends('layouts/contentNavbarLayout')

@section('title', 'Tables - Users')

@section('content')


    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="py-3"><span class="text-muted fw-light">Users /</span> Profile Management
            </h4>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#largeModal">
                Add 
            </button>
        </div>

    </div>
<!-- ----------- -->
<div class="row">
  <!-- FormValidation -->
  <div class="col-12">
  <div class="card">
  <h5 class="card-header">Table Basic</h5>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
       
          <th>Client Name</th>
          <th>Status</th>
          <th>Associated Client</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <tr>
          <td><i class="mdi mdi-wallet-travel mdi-20px text-danger me-3"></i><span class="fw-medium">Tours Project</span></td>
         
          <td><span class="badge rounded-pill bg-label-primary me-1">Active</span></td>
          <td>
            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Lilian Fuller">
                <img src="{{asset('assets/img/avatars/5.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Sophia Wilkerson">
                <img src="{{asset('assets/img/avatars/6.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Christina Parker">
                <img src="{{asset('assets/img/avatars/7.png')}}" alt="Avatar" class="rounded-circle">
              </li>
            </ul>
          </td>
   
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td><i class="mdi mdi-wallet-travel mdi-20px text-danger me-3"></i><span class="fw-medium">Tours Project</span></td>
         
          <td><span class="badge rounded-pill bg-label-primary me-1">Active</span></td>
          <td>
            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Lilian Fuller">
                <img src="{{asset('assets/img/avatars/5.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Sophia Wilkerson">
                <img src="{{asset('assets/img/avatars/6.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Christina Parker">
                <img src="{{asset('assets/img/avatars/7.png')}}" alt="Avatar" class="rounded-circle">
              </li>
            </ul>
          </td>
   
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td><i class="mdi mdi-wallet-travel mdi-20px text-danger me-3"></i><span class="fw-medium">Tours Project</span></td>
         
          <td><span class="badge rounded-pill bg-label-primary me-1">Active</span></td>
          <td>
            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Lilian Fuller">
                <img src="{{asset('assets/img/avatars/5.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Sophia Wilkerson">
                <img src="{{asset('assets/img/avatars/6.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Christina Parker">
                <img src="{{asset('assets/img/avatars/7.png')}}" alt="Avatar" class="rounded-circle">
              </li>
            </ul>
          </td>
   
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>
              </div>
            </div>
          </td>
        </tr>
        <!-- <tr>
          <td><i class="mdi mdi-basketball mdi-20px text-info me-3"></i><span class="fw-medium">Sports Project</span></td>
          <td>Barry Hunter</td>
          <td>
            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Lilian Fuller">
                <img src="{{asset('assets/img/avatars/5.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Sophia Wilkerson">
                <img src="{{asset('assets/img/avatars/6.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Christina Parker">
                <img src="{{asset('assets/img/avatars/7.png')}}" alt="Avatar" class="rounded-circle">
              </li>
            </ul>
          </td>
          <td><span class="badge rounded-pill bg-label-success me-1">Completed</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-pencil-outline me-2"></i> Edit</a>
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-2"></i> Delete</a>
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td><i class="mdi mdi-greenhouse mdi-20px text-success me-3"></i><span class="fw-medium">Greenhouse Project</span></td>
          <td>Trevor Baker</td>
          <td>
            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Lilian Fuller">
                <img src="{{asset('assets/img/avatars/5.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Sophia Wilkerson">
                <img src="{{asset('assets/img/avatars/6.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Christina Parker">
                <img src="{{asset('assets/img/avatars/7.png')}}" alt="Avatar" class="rounded-circle">
              </li>
            </ul>
          </td>
          <td><span class="badge rounded-pill bg-label-info me-1">Scheduled</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-pencil-outline me-2"></i> Edit</a>
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-2"></i> Delete</a>
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td><i class="mdi mdi-bank mdi-20px text-primary me-3"></i><span class="fw-medium">Bank Project</span></td>
          <td>Jerry Milton</td>
          <td>
            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Lilian Fuller">
                <img src="{{asset('assets/img/avatars/5.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Sophia Wilkerson">
                <img src="{{asset('assets/img/avatars/6.png')}}" alt="Avatar" class="rounded-circle">
              </li>
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="Christina Parker">
                <img src="{{asset('assets/img/avatars/7.png')}}" alt="Avatar" class="rounded-circle">
              </li>
            </ul>
          </td>
          <td><span class="badge rounded-pill bg-label-warning me-1">Pending</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-pencil-outline me-2"></i> Edit</a>
                <a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-2"></i> Delete</a>
              </div>
            </div>
          </td>
        </tr> -->
      </tbody>
    </table>
  </div>
</div>
  </div>
  <!-- /FormValidation -->
</div>
<!-- ----------- -->

    <!-- Hoverable Table rows -->
    <div class="card">

     
    </div>

    <div class="modal fade" id="largeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel3">New Client</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="input-group input-group-merge mb-4">
                            <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                    class="mdi mdi-account-outline"></i></span>
                            <input type="text" class="form-control" id="basic-icon-default-fullname"
                                placeholder="Full Name" aria-label="Full Name"
                                aria-describedby="basic-icon-default-fullname2" />
                        </div>

                        <div class="mb-4">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
                                <input type="text" id="basic-icon-default-email" class="form-control"
                                    placeholder="Email" aria-label="Email"
                                    aria-describedby="basic-icon-default-email2" />
                                <span id="basic-icon-default-email2" class="input-group-text">@example.com</span>
                            </div>
                            <div class="form-text"> You can use letters, numbers & periods </div>
                        </div>
                        <div class="input-group input-group-merge mb-4">
                            <span id="basic-icon-default-password2" class="input-group-text"><i
                                    class="mdi mdi-lock-outline"></i></span>
                            <input type="password" id="basic-icon-default-password" class="form-control"
                                placeholder="Password" aria-label="Password"
                                aria-describedby="basic-icon-default-password2" />
                            <span class="input-group-text cursor-pointer" id="togglePassword">
                                <i class="mdi mdi-eye-outline" id="togglePasswordIcon"></i>
                            </span>
                        </div>
                        <div class="input-group input-group-merge mb-4">
                            <span id="basic-icon-default-phone2" class="input-group-text"><i
                                    class="mdi mdi-phone"></i></span>
                            <input type="text" id="basic-icon-default-phone" class="form-control phone-mask"
                                placeholder="Phone No" aria-label="Phone No"
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



@endsection

@push('my-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#basic-icon-default-password');
            const togglePasswordIcon = document.querySelector('#togglePasswordIcon');

            togglePassword.addEventListener('click', function() {

                // Toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                // Toggle the eye icon
                if (type === 'password') {
                    togglePasswordIcon.classList.remove('mdi-eye-off-outline');
                    togglePasswordIcon.classList.add('mdi-eye-outline');
                } else {
                    togglePasswordIcon.classList.remove('mdi-eye-outline');
                    togglePasswordIcon.classList.add('mdi-eye-off-outline');
                }
            });
        });
    </script>
@endpush
