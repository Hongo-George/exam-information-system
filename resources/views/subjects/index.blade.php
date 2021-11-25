@extends('layouts.dashboard')

@section('title', 'Subjects')

@section('content')

<div class="d-flex justify-content-between">
    <h1 class="h4 fw-bold text-muted">Subjects</h1>
    <button data-bs-toggle="modal" data-bs-target="#upsert-subject-modal" class="btn btn-outline-primary hstack gap-2 align-items-center">
        <i class="fa fa-plus"></i>
        <span>Subject</span>
    </button>
</div>
<hr>

@livewire('subjects')

@endsection

@push('scripts')
<script>
    livewire.on('show-upsert-subject-modal', () => $('#upsert-subject-modal').modal('show'))
    livewire.on('hide-upsert-subject-modal', () => $('#upsert-subject-modal').modal('hide'))
    livewire.on('show-delete-subject-modal', () => $('#delete-subject-modal').modal('show'))
    livewire.on('hide-delete-subject-modal', () => $('#delete-subject-modal').modal('hide'))

</script>
@endpush