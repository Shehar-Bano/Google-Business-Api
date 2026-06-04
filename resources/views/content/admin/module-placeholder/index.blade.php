@extends('layouts/contentNavbarLayout')

@section('title', ucfirst($module) . ' Module')

@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="mb-2 text-capitalize">{{ str_replace('-', ' ', $module) }}</h4>
        <p class="mb-0">This module is permission-protected and ready for implementation.</p>
    </div>
</div>
@endsection
