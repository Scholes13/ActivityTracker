<?php

namespace App\Helpers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class PdfExport
{
    /**
     * Generate PDF report for a collection of models
     *
     * @param Collection $data Collection of models
     * @param string $view View template to use
     * @param array $extraData Additional data to pass to the view
     * @param string $fileName Filename for the downloaded PDF
     * @return Response
     */
    public static function export(Collection $data, string $view, array $extraData = [], string $fileName = 'report.pdf'): Response
    {
        $pdf = PDF::loadView($view, array_merge([
            'data' => $data,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ], $extraData));

        return $pdf->download($fileName);
    }

    /**
     * Generate PDF report for a single model
     *
     * @param mixed $model The model to export
     * @param string $view View template to use
     * @param array $extraData Additional data to pass to the view
     * @param string $fileName Filename for the downloaded PDF
     * @return Response
     */
    public static function exportSingle($model, string $view, array $extraData = [], string $fileName = 'report.pdf'): Response
    {
        $pdf = PDF::loadView($view, array_merge([
            'model' => $model,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ], $extraData));

        return $pdf->download($fileName);
    }
} 