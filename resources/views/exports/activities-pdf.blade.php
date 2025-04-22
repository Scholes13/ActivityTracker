<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activities Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .meta {
            margin-bottom: 20px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .status-waiting {
            color: #ff9800;
        }
        .status-inprogress {
            color: #2196F3;
        }
        .status-done {
            color: #4CAF50;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Activities Report</h1>
    
    <div class="meta">
        <p><strong>Generated:</strong> {{ $generated_at }}</p>
        @if(isset($filters) && count($filters) > 0)
            <p><strong>Filters:</strong> 
                @foreach($filters as $key => $value)
                    {{ ucfirst($key) }}: {{ $value }}, 
                @endforeach
            </p>
        @endif
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Sub Task</th>
                <th>Assigned To</th>
                <th>Assigned By</th>
                <th>Status</th>
                <th>Deadline</th>
                <th>Completed At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $activity)
                <tr>
                    <td>{{ $activity->title }}</td>
                    <td>{{ $activity->subTask ? $activity->subTask->name : 'N/A' }}</td>
                    <td>{{ $activity->user ? $activity->user->name : 'N/A' }}</td>
                    <td>{{ $activity->assignedBy ? $activity->assignedBy->name : 'N/A' }}</td>
                    <td class="status-{{ $activity->status }}">{{ ucfirst($activity->status) }}</td>
                    <td>{{ $activity->deadline ? $activity->deadline->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $activity->completed_at ? $activity->completed_at->format('Y-m-d H:i') : 'Not completed' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No activities found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Sales Mission Tracking System &copy; {{ date('Y') }}</p>
    </div>
</body>
</html> 