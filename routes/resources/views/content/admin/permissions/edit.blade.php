@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Permission')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Edit Permission</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
            @csrf
            @method('PUT')
            @include('content.admin.permissions.partials.form')
        </form>
    </div>
</div>
@endsection
