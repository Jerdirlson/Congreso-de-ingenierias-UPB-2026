<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicDocController extends Controller
{
    private const ALLOWED = [
        'Call_for_papers_V1.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'Location.docx'           => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function download(string $filename): StreamedResponse
    {
        abort_unless(isset(self::ALLOWED[$filename]), 404, 'Documento no encontrado.');

        $path = 'docs/' . $filename;

        abort_unless(Storage::disk('public')->exists($path), 404, 'Archivo no disponible.');

        return Storage::disk('public')->download($path, $filename, [
            'Content-Type' => self::ALLOWED[$filename],
        ]);
    }
}
