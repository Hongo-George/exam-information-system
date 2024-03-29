<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th colspan="{{ $colsCount }}">{{ $level->name }} Streams Performance</th>
                    </tr>
                    <tr>
                        <th>Position</th>
                        <th>Alias</th>
                        @if ($systemSettings->school_level === 'secondary')
                        <th>Points</th>
                        <th>Grade</th>
                        @else
                        <th>Average</th>
                        <th>Deviation</th>
                        @endif
                        @if (request()->routeIs('exams.analysis.index'))
                        <th class="d-print-none">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if ($levelUnits->count())
                    @foreach ($levelUnits as $levelUnit)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $levelUnit->alias }}</td>
                        @if ($systemSettings->school_level === 'secondary')
                        <td>{{ $levelUnit->pivot->points }}</td>
                        <td>{{ $levelUnit->pivot->grade }}</td>
                        @else
                        <td>{{ $levelUnit->pivot->average }}</td>
                        @if ($levelUnit->pivot->average_deviation > 0)
                        <td class="text-success">{{ $levelUnit->pivot->average_deviation ?? 0 }}</td>
                        @elseif($levelUnit->pivot->average_deviation < 0)
                        <td class="text-danger">{{ $levelUnit->pivot->average_deviation ?? 0 }}</td>
                        @else
                        <td class="text-warning">{{ $levelUnit->pivot->average_deviation ?? 0 }}</td>
                        @endif
                        @endif
                        @if (request()->routeIs('exams.analysis.index'))                           
                        <th class="d-print-none">
                            <a href="{{ route('exams.analysis.index', [
                                'exam' => $exam,
                                'level-unit' => $levelUnit
                            ]) }}" class="btn btn-sm btn-outline-primary d-inline-flex gap-2 align-items-center">
                                <i class="fa fa-eye"></i>
                                <span>Details</span>
                            </a>
                        </th>
                        @endif
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="{{ $colsCount }}">Streams Performance not Published yet</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>