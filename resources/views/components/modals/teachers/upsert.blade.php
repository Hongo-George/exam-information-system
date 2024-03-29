@props([
'teacherId' => null,
'employers' => [],
'subjects' => []
])

<div wire:ignore.self id="upsert-teacher-modal" class="modal fade" tabindex="-1" data-bs-backdrop="static"
    aria-labelledby="upsert-teacher-modal-title">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                @if (is_null($teacherId))
                <h5 id="upsert-teacher-modal-title" class="modal-title">Add Teacher</h5>
                @else
                <h5 id="upsert-teacher-modal-title" class="modal-title">Update Teacher</h5>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" wire:model.lazy="name" id="name"
                                class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                            <span class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" wire:model.lazy="phone" id="phone" aria-describedby="phone-help"
                                class="form-control @error('phone') is-invalid @enderror" placeholder="254707427854">
                            <div id="phone-help" class="form-text">Begin with the Kenyas country code(254) without the (+) symbol.</div>
                            @error('phone')
                            <span class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" wire:model.lazy="email" id="email"
                                class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                            <span class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="employer" class="form-label">Employer</label>
                            <select wire:model="employer" id="employer"
                                class="form-select @error('employer') is-invalid @enderror">
                                <option value="">-- Select Employer --</option>
                                @foreach ($employers as $employer)
                                <option value="{{ $employer }}">{{ $employer }}</option>
                                @endforeach
                            </select>
                            @error('employer')
                            <span class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tsc-number" class="form-label">TSC Number</label>
                            <input type="text" wire:model.lazy="tsc_number" id="tsc-number"
                                class="form-control @error('tsc_number') is-invalid @enderror">
                            @error('tsc_number')
                            <span class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="subjects" class="form-label fw-bold">Select Teacher Subject(s)</label>
                            <fieldset id="subjects" class="row g-3">
                                @foreach ($subjects as $subject)
                                <div class="col-md-4">
                                    <div class="form-check ps-0">
                                        <input type="checkbox" wire:model="selectedSubjects.{{ $subject->id }}"
                                            id="subject-{{ $loop->iteration }}" class="form-check-control" value="true">
                                        <label for="subject-{{ $loop->iteration }}"
                                            class="form-check-label">{{ $subject->name }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary">Cancel</button>

                @if (is_null($teacherId))
                <button type="button" wire:click="addTeacher" class="btn btn-outline-primary">Submit</button>
                @else
                <button type="button" wire:click="updateTeacher" class="btn btn-outline-info">Update</button>
                @endif
            </div>
        </div>
    </div>
</div>