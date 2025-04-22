<?php

namespace App\Exports;

use App\Models\Activity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $sub_task_id;
    protected $user_id;
    protected $status;
    protected $startDate;
    protected $endDate;
    
    public function __construct($sub_task_id = null, $user_id = null, $status = null, $startDate = null, $endDate = null)
    {
        $this->sub_task_id = $sub_task_id;
        $this->user_id = $user_id;
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Activity::with(['subTask', 'user', 'assignedBy']);
        
        if ($this->sub_task_id) {
            $query->where('sub_task_id', $this->sub_task_id);
        }
        
        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }
        
        if ($this->status) {
            $query->where('status', $this->status);
        }
        
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        
        return $query->get();
    }
    
    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Sub Task',
            'Assigned To',
            'Assigned By',
            'Description',
            'Status',
            'Deadline',
            'Completed At',
            'Created At',
        ];
    }
    
    public function map($activity): array
    {
        return [
            $activity->id,
            $activity->title,
            $activity->subTask ? $activity->subTask->name : 'N/A',
            $activity->user ? $activity->user->name : 'N/A',
            $activity->assignedBy ? $activity->assignedBy->name : 'N/A',
            $activity->description,
            $activity->status,
            $activity->deadline ? $activity->deadline->format('Y-m-d') : 'N/A',
            $activity->completed_at ? $activity->completed_at->format('Y-m-d H:i:s') : 'N/A',
            $activity->created_at->format('Y-m-d'),
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
