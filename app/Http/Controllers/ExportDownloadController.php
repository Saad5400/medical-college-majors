<?php

namespace App\Http\Controllers;

use Filament\Actions\Exports\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportDownloadController extends Controller
{
    public function download(Request $request, int $exportId): StreamedResponse
    {
        // Ensure user is authenticated and is an admin
        abort_unless(
            $request->user()?->hasRole('admin'),
            403,
            'Only administrators can download exports.'
        );

        // Find the export
        $export = Export::findOrFail($exportId);

        // Validate the format
        $format = $request->query('format', 'xlsx');
        abort_unless(
            in_array($format, ['xlsx', 'csv']),
            400,
            'Invalid export format.'
        );

        // Get the file path (file_name includes the directory path)
        $filePath = $export->file_name;

        // Check if file exists
        abort_unless(
            Storage::disk($export->file_disk)->exists($filePath),
            404,
            'Export file not found.'
        );

        // Return the file download response
        return Storage::disk($export->file_disk)->download(
            $filePath,
            basename($filePath)
        );
    }
}
