@props(['levelId' => null, 'optionalSubjects' => []])

<div wire:ignore.self id="upsert-level-modal" class="modal fade" tabindex="-1" data-bs-backdrop="static"
    aria-labelledby="upsert-level-modal-title">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                @if (is_null($levelId))
                <h5 id="upsert-level-modal-title" class="modal-title">Add Level</h5>
                @else
                <h5 id="upsert-level-modal-title" class="modal-title">Update Level</h5>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="row g-3">
                    <div class="col-md-9">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" wire:model.lazy="name" id="name"
                            class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="name" class="form-label">Numeric</label>
                        <input type="integer" wire:model.lazy="numeric" id="numeric"
                            class="form-control @error('numeric') is-invalid @enderror">
                        @error('numeric')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="subjects" class="form-label fw-bold">Optional Subjects</label>
                        <fieldset class="row g-2">
                            @foreach ($optionalSubjects as $subject)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="selectedOptionalSubjects.{{ $subject->id }}" id="optional-subject-{{ $loop->index }}" value="true" class="form-check-input">
                                        <label for="optional-subject-{{ $loop->index }}" class="form-check-label">{{ $subject->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </fieldset>
                    </div>
                    <div class="col-md-12">
                        <label for="name" class="form-label">Description</label>
                        <textarea wire:model.lazy="description" id="description" cols="100" rows="3"
                            class="form-control @error('description') is-invalid @enderror"></textarea>
                        @error('description')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary">Cancel</button>
                @if(is_null($levelId))
                <button type="submit" wire:click="createLevel" class="btn btn-outline-info">Create</button>
                @else
                <button type="submit" wire:click="updateLevel" class="btn btn-outline-info">Update</button>
                @endif
            </div>
        </div>
    </div>
</div>