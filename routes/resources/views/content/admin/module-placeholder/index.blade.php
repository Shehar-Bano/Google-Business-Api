@extends('layouts/contentNavbarLayout')

@section('title', ucfirst($module) . ' Module')

@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="mb-2 text-capitalize">{{ str_replace('-', ' ', $module) }}</h4>
        <p class="mb-0">TODO: Add the {{ str_replace('-', ' ', $module) }} module here in future.</p>
    </div>
</div>
@endsection
