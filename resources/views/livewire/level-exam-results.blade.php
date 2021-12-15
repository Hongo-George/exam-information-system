<div>
    <x-feedback />

    <div class="d-inline-flex gap-2">
        <button data-bs-toggle="modal" data-bs-target="#filter-exam-results"
            class="btn btn-outline-primary d-inline-flex gap-2 align-items-center">
            <i class="fa fa-filter"></i>
            <span>Filter Results</span>
        </button>
        <button data-bs-toggle="modal" data-bs-target="#order-exam-results"
            class="btn btn-outline-primary d-inline-flex gap-2 align-items-center">
            <i class="fa fa-sort"></i>
            <span>Order Results</span>
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="text-uppercase">
                <tr class="text-center">
                    <th colspan="{{ count($cols) }}">{{ $level->name }} - {{ $exam->name }} Results</th>
                </tr>
                <tr>
                    @foreach ($cols as $col)
                    <th>{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if ($data->count())
                @foreach ($data as $item)
                <tr>
                    @foreach ($cols as $col)
                    @if (in_array($col, $subjectCols))
                    <td>{{ optional(json_decode($item->$col))->score ?? null }}{{ optional(json_decode($item->$col))->grade ?? null }}
                    </td>
                    @else
                    <td>{{ $item->$col }}</td>
                    @endif
                    @endforeach
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="{{ count($cols) }}" class="text-center">No data found yet</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>