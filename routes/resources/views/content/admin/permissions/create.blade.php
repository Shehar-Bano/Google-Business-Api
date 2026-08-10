@extends('layouts/contentNavbarLayout')

@section('title', 'Create Permission')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Create Permission</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.permissions.store') }}" method="POST">
            @csrf
            @include('content.admin.permissions.partials.form', ['permission' => null])
        </form>
    </div>
</div>
@endsection
