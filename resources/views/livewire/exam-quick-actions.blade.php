<div class="card h-100">
    <div class="card-body">
        <h5>Exam Quick Actions</h5>
        <hr>
        <x-feedback />
        <div class="d-flex gap-3 flex-wrap">
            <a href="{{ route('exams.activities.index', $exam) }}" class="btn btn-sm btn-outline-primary gap-2 align-items-center">
                <i class="fa fa-table"></i>
                <span class="">Exam Activities</span>
            </a>

            @can('updateScoresTable', $exam)
            <button data-bs-toggle="modal" data-bs-target="#update-scores-table-modal"
                class="btn btn-sm btn-primary hstack gap-2">
                <i class="fa fa-sync"></i>
                <span>Scores Table</span>
            </button>
            @endcan

            @can('change-exam-status')
            <button data-bs-toggle="modal" data-bs-target="#change-status-exam-modal"
                class="btn btn-sm d-block btn-primary hstack gap-2">
                <i class="fa fa-pencil-alt"></i>
                <span>Change Status</span>
            </button>
            @endcan

            @can('viewScoresPage', $exam)
            <a href="{{ route('exams.scores.index', $exam) }}" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-cog"></i>
                <span class="">Manage Scores</span>
            </a>
            @endcan

            @if ($exam->fresh()->isPublished())
            <a href="{{ route('exams.results.index', $exam) }}"
                class="btn btn-sm btn-outline-primary gap-2 align-items-center">
                <i class="fa fa-table"></i>
                <span class="">Results</span>
            </a>
            <a href="{{ route('exams.analysis.index', $exam) }}"
                class="btn btn-sm btn-outline-primary gap-2 align-items-center">
                <i class="fa fa-poll"></i>
                <span class="">Analysis</span>
            </a>
            @endif

            @can('viewTranscripts', $exam)
            <a href="{{ route('exams.transcripts.index', $exam) }}"
                class="btn btn-sm btn-outline-primary gap-2 align-items-center">
                <i class="fa fa-eye"></i>
                <span class="">Transcripts</span>
            </a>
            @endcan

        </div>

        <x-modals.exams.change-status :name="$exam->name" :statuses="$statuses" />
        <x-modals.exams.update-scores-table :name="$exam->name" />

    </div>
</div>