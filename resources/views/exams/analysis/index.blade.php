@extends('layouts.dashboard')

@section('title', $exam->name)

@section('content')

<div class="d-flex justify-content-between">
    <h1 class="h4 fw-bold text-muted">{{ $exam->name }} Analysis</h1>
</div>
<div class="row g-4 py-3">
    @foreach ($exam->levels as $level)
    <div class="col-md-12">
        <div class="card h-100 rounded-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3>{{ $level->name }}</h3>
                        </div>
                    </div>
                    <hr>
                    <div class="col-md-6">
                        <canvas id="myChart" width="600" height="200"></canvas>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex flex-column">
                            <h6 class="text-secondary">Mean Points</h6>
                            <span class="text-success fw-bolder display-6">7.5491</span>
                            <span class="text-secondary fw-bold">+.0054</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex flex-column">
                            <h6 class="text-secondary">Mean Grade</h6>
                            <span class="text-success fw-bolder display-6">B-</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex flex-column">
                            <h6 class="text-secondary">Students</h6>
                            <span class="text-success fw-bolder display-6">251</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('myChart').getContext('2d');

    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Red', 'Blue', 'Green', 'White', 'Yellow'],
            datasets: [{
                label: 'Level Performance',
                data: [7, 6, 5, 5, 7, 6],
                borderColor: [
                    'red',
                    'blue',
                    'green',
                    'white',
                    'yellow'
                ]
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush