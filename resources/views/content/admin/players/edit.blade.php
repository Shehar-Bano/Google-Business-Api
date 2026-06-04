@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Player — ' . $player->name)

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.players.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="mdi mdi-arrow-left"></i> Back
    </a>
    <h5 class="mb-0">Edit Player</h5>
</div>

<form action="{{ route('admin.players.update', $player) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="row g-4">

        {{-- Main Details --}}
        <div class="col-12 col-xl-8">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Account Information</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $player->name) }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $player->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $player->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach(['active' => 'Active', 'profile_incomplete' => 'Profile Incomplete', 'otp_pending' => 'OTP Pending', 'suspended' => 'Suspended'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old('status', $player->status) === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">Player Profile</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror"
                                value="{{ old('dob', $player->dob?->format('Y-m-d')) }}">
                            @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Select gender</option>
                                @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old('gender', $player->gender) === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                value="{{ old('city', $player->city) }}">
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Playing Level</label>
                            <select name="playing_level" class="form-select @error('playing_level') is-invalid @enderror">
                                <option value="">Select level</option>
                                @foreach(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced', 'professional' => 'Professional'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old('playing_level', $player->playing_level) === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('playing_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Primary Hand</label>
                            <select name="primary_hand" class="form-select @error('primary_hand') is-invalid @enderror">
                                <option value="">Select hand</option>
                                @foreach(['left' => 'Left', 'right' => 'Right'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old('primary_hand', $player->primary_hand) === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('primary_hand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" rows="3" class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $player->bio) }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar: Photo + Actions --}}
        <div class="col-12 col-xl-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Profile Photo</h6></div>
                <div class="card-body text-center">
                    @if($player->profile_image)
                        <img id="currentPhoto" src="{{ asset('storage/' . $player->profile_image) }}" alt="{{ $player->name }}"
                             class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                    @else
                        <div id="photoPlaceholder" class="rounded-circle d-flex align-items-center justify-content-center bg-label-success mx-auto mb-3"
                             style="width:100px;height:100px;">
                            <i class="mdi mdi-account" style="font-size:40px;"></i>
                        </div>
                    @endif
                    <div id="photoPreviewWrap" class="mb-3 d-none">
                        <img id="photoPreview" src="" alt="Preview" class="rounded-circle"
                             style="width:100px;height:100px;object-fit:cover;">
                    </div>
                    <input type="file" name="profile_image" id="profileImageInput"
                           class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                    @error('profile_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card">
                <div class="card-body d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="mdi mdi-check"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.players.index') }}" class="btn btn-outline-secondary w-100">Cancel</a>
                </div>
            </div>
        </div>

    </div>
</form>

@push('my-script')
<script>
    document.getElementById('profileImageInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const current = document.getElementById('currentPhoto');
        const placeholder = document.getElementById('photoPlaceholder');
        if (current) current.classList.add('d-none');
        if (placeholder) placeholder.classList.add('d-none');
        const wrap = document.getElementById('photoPreviewWrap');
        const img  = document.getElementById('photoPreview');
        img.src = URL.createObjectURL(file);
        wrap.classList.remove('d-none');
    });
</script>
@endpush
@endsection
