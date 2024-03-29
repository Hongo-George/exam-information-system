@extends('layouts.dashboard')

@section('title', $teacher->auth->name)

@section('content')

<div class="d-flex justify-content-between align-items-center">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $teacher->auth->name }} Details</li>
        </ol>
    </nav>
</div>

<div class="row g-4 py-3">

    <div class="col-md-12">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Basic Details</h2>
                <hr>
                <div class="row g-2 align-items-center">
                    <dl class="col-md-6">
                        <dt>Name</dt>
                        <dd>{{ optional($teacher->auth)->name ?? '-' }}</dd>
                    </dl>
                    <dl class="col-md-6">
                        <dt>Phone</dt>
                        <dd>{{ optional($teacher->auth)->phone ?? '-' }}</dd>
                    </dl>
                    <dl class="col-md-6">
                        <dt>Email</dt>
                        <dd>{{ optional($teacher->auth)->email ?? '-' }}</dd>
                    </dl>
                    <dl class="col-md-6">
                        <dt>TSC Number</dt>
                        <dd>{{ $teacher->tsc_number ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5 my-0">Responsibilities</h2>

                    <div class="d-inline-flex flex-wrap gap-2 align-items-md-center">
                        <button data-bs-toggle="modal" data-bs-target="#assign-teacher-responsibility-modal"
                            class="btn btn-sm btn-outline-primary rounded-circle d-md-none">
                            <i class="fa fa-plus"></i>
                        </button>
    
                        <button data-bs-toggle="modal" data-bs-target="#assign-teacher-responsibility-modal"
                            class="d-none d-md-inline-flex btn btn-sm btn-outline-primary gap-2 align-items-center">
                            <i class="fa fa-plus"></i>
                            <span>Responsibility</span>
                        </button>           
                        <button data-bs-toggle="modal" data-bs-target="#assign-bulk-responsibilities-modal"
                            class="btn btn-sm btn-outline-primary rounded-circle d-md-none">
                            <i class="fa fa-plus"></i>
                        </button>
    
                        <button data-bs-toggle="modal" data-bs-target="#assign-bulk-responsibilities-modal"
                            class="d-none d-md-inline-flex btn btn-sm btn-outline-primary gap-2 align-items-center">
                            <i class="fa fa-plus"></i>
                            <span>Bulk Responsibilities</span>
                        </button>
                    </div>
                </div>
                <hr>
                <livewire:teacher-responsibilities :teacher="$teacher" type="table-sm" />
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5">Subjects</h2>

                    <button data-bs-toggle="modal" data-bs-target="#update-teacher-subjects-modal"
                        class="btn btn-sm btn-outline-primary hstack gap-2 align-items-center">
                        <i class="fa fa-pencil-alt"></i>
                        <span>Subject</span>
                    </button>
                </div>
                <hr>
                <livewire:teacher-subjects :teacher="$teacher" type="table-sm table-bordered" />
            </div>
        </div>
    </div>
</div>

@endsection



@push('scripts')
<script>
    livewire.on('hide-assign-teacher-responsibility-modal', () =>
        $('#assign-teacher-responsibility-modal').modal('hide'));

    livewire.on('hide-update-teacher-subjects-modal', () =>
        $('#update-teacher-subjects-modal').modal('hide'));

    livewire.on('hide-assign-bulk-responsibilities-modal', () =>
        $('#assign-bulk-responsibilities-modal').modal('hide'));
</script>
@endpush