@extends('layouts/contentNavbarLayout')

@section('title', 'Add Club')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.clubs.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="mdi mdi-arrow-left"></i> Back
    </a>
    <h5 class="mb-0">Add New Club</h5>
</div>

<form action="{{ route('admin.clubs.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">

        {{-- Main Details --}}
        <div class="col-12 col-xl-8">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Club Information</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Club Name <span class="text-danger">*</span></label>
                            <input type="text" name="club_name" class="form-control @error('club_name') is-invalid @enderror"
                                value="{{ old('club_name') }}" placeholder="e.g. Elite Squash Club">
                            @error('club_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Owner / Manager Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="Full name">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" placeholder="club@example.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}" placeholder="+1234567890">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="Min. 8 characters">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                value="{{ old('city') }}" placeholder="City">
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Number of Courts</label>
                            <input type="number" name="number_of_courts" min="1" class="form-control @error('number_of_courts') is-invalid @enderror"
                                value="{{ old('number_of_courts') }}" placeholder="e.g. 4">
                            @error('number_of_courts')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Working Hours</label>
                            <input type="text" name="working_hours" class="form-control @error('working_hours') is-invalid @enderror"
                                value="{{ old('working_hours') }}" placeholder="e.g. 6 AM – 10 PM">
                            @error('working_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach(['active' => 'Active', 'pending' => 'Pending Approval', 'otp_pending' => 'OTP Pending', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old('status', 'active') === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address') }}" placeholder="Full address">
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar: Logo + Actions --}}
        <div class="col-12 col-xl-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Club Logo</h6></div>
                <div class="card-body">
                    <div id="logoPreviewWrap" class="mb-3 d-none text-center">
                        <img id="logoPreview" src="" alt="Preview" class="rounded" style="max-height:120px;max-width:100%;object-fit:cover;">
                    </div>
                    <input type="file" name="club_logo" id="clubLogoInput" class="form-control @error('club_logo') is-invalid @enderror" accept="image/*">
                    @error('club_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card">
                <div class="card-body d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="mdi mdi-check"></i> Create Club
                    </button>
                    <a href="{{ route('admin.clubs.index') }}" class="btn btn-outline-secondary w-100">Cancel</a>
                </div>
            </div>
        </div>

    </div>
</form>

@push('my-script')
<script>
    document.getElementById('clubLogoInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const wrap = document.getElementById('logoPreviewWrap');
        const img  = document.getElementById('logoPreview');
        img.src = URL.createObjectURL(file);
        wrap.classList.remove('d-none');
    });
</script>
@endpush
@endsection
