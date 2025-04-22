<?php

namespace App\Exports;

use App\Models\SubTask;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubTaskExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $leader_id;
    protected $status;
    
    public function __construct($leader_id = null, $status = null)
    {
        $this->leader_id = $leader_id;
        $this->status = $status;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = SubTask::with('leader');
        
        if ($this->leader_id) {
            $query->where('leader_id', $this->leader_id);
        }
        
        if ($this->status) {
            $query->where('status', $this->status);
        }
        
        return $query->get();
    }
    
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Type',
            'Leader',
            'Status',
            'Deadline',
            'Created At',
        ];
    }
    
    public function map($subTask): array
    {
        return [
            $subTask->id,
            $subTask->name,
            $subTask->description,
            $subTask->type,
            $subTask->leader ? $subTask->leader->name : 'N/A',
            $subTask->status,
            $subTask->deadline ? $subTask->deadline->format('Y-m-d') : 'N/A',
            $subTask->created_at->format('Y-m-d'),
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
