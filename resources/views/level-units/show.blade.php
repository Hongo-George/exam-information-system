@extends('layouts.dashboard')

@section('title', $levelUnit->alias)

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-md-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('level-units.index') }}">Classes</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $levelUnit->alias }}</li>
        </ol>
    </nav>
    <button data-bs-toggle="modal" data-bs-target="#upsert-level-unit-student-modal"
        class="btn btn-outline-primary d-inline-flex gap-2 align-items-center">
        <i class="fa fa-plus"></i>
        <span>Promote Students</span>
    </button>
</div>

<div class="row g-4 py-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                @livewire('level-unit-students', ['levelUnit' => $levelUnit])
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="text-center">{{ $levelUnit->alias }} Responsibilities</h5>
                <hr>
                <livewire:level-unit-responsibilities :levelUnit="$levelUnit" />
            </div>
        </div>
    </div>
</div>


@endsection