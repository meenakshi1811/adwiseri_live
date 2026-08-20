<?php

namespace App\Exports;

use App\Models\ErrorLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportErrorLogs implements FromCollection, WithHeadings
{
    public function collection()
    {
        return ErrorLog::orderByDesc('created_at')->get()->map(function (ErrorLog $log, int $index) {
            return [
                'ID' => $log->id,
                'Error Type' => $log->error_type,
                'Page/Screen' => $log->page_screen,
                'Message/Description' => $log->message,
                'DateTime' => $log->created_at ? $log->created_at->format('d-m-Y H:i:s') : '',
                'Status Code' => $log->status_code,
                'User ID' => $log->user_id,
                'IP Address' => $log->ip_address,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Error Type',
            'Page/Screen',
            'Message/Description',
            'DateTime',
            'Status Code',
            'User ID',
            'IP Address',
        ];
    }
}
