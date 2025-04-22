<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sub Tasks Report</title>
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
        .status-active {
            color: #4CAF50;
        }
        .status-inactive {
            color: #F44336;
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
    <h1>Sub Tasks Report</h1>
    
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
                <th>Name</th>
                <th>Type</th>
                <th>Leader</th>
                <th>Status</th>
                <th>Deadline</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $subtask)
                <tr>
                    <td>{{ $subtask->name }}</td>
                    <td>{{ $subtask->type }}</td>
                    <td>{{ $subtask->leader ? $subtask->leader->name : 'N/A' }}</td>
                    <td class="status-{{ $subtask->status }}">{{ ucfirst($subtask->status) }}</td>
                    <td>{{ $subtask->deadline ? $subtask->deadline->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $subtask->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No sub tasks found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Sales Mission Tracking System &copy; {{ date('Y') }}</p>
    </div>
</body>
</html> 