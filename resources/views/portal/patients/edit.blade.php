@extends('layouts.portal', ['title' => 'Edit Patient'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Patient</h1>
            <div class="page-subtitle">Update patient demographics, history, allergies, medications, and notes.</div>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('portal.patients.update', $patient) }}">
            @csrf
            @method('PUT')
            @include('portal.patients.form', ['patient' => $patient])
        </form>
    </div>
@endsection
