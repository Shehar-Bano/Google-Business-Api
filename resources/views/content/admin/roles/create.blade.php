@extends('layouts/contentNavbarLayout')

@section('title', 'Create Role')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Create Role</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            @include('content.admin.roles.partials.form', ['role' => null, 'selectedPermissions' => []])
        </form>
    </div>
</div>
@endsection
