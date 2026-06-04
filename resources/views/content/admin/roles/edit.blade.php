@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Role')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Edit Role</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            @include('content.admin.roles.partials.form', ['selectedPermissions' => $selectedPermissions])
        </form>
    </div>
</div>
@endsection
