@extends('layouts.dashboard')

@section('title', 'Teachers')

@section('content')

<div class="d-flex justify-content-between">
    <h1 class="h4 fw-bold text-muted">Level Units</h1>
    <button data-bs-toggle="modal" data-bs-target="#upsert-level-unit-modal" class="btn btn-outline-primary hstack gap-2 align-items-center">
        <i class="fa fa-plus"></i>
        <span>Level Unit</span>
    </button>
</div>
<hr>

<livewire:level-units />

@endsection

@push('scripts')
<script>
    livewire.on('show-upsert-level-unit-modal', () => $('#upsert-level-unit-modal').modal('show'))
    livewire.on('hide-upsert-level-unit-modal', () => $('#upsert-level-unit-modal').modal('hide'))

    livewire.on('show-delete-level-unit-modal', () => $('#delete-level-unit-modal').modal('show'))
    livewire.on('hide-delete-level-unit-modal', () => $('#delete-level-unit-modal').modal('hide'))
</script>
@endpush