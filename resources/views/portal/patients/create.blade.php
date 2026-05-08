@extends('layouts.portal', ['title' => 'Add Patient'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Add Patient</h1>
            <div class="page-subtitle">Create a new patient record with core demographics, insurance, and clinical notes.</div>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('portal.patients.store') }}">
            @csrf
            @include('portal.patients.form')
        </form>
    </div>
@endsection
