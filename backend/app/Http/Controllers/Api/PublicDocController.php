<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicDocController extends Controller
{
    private const ALLOWED = [
        'Call_for_papers_V1.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'Location.docx'           => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function download(string $filename): BinaryFileResponse
    {
        abort_unless(isset(self::ALLOWED[$filename]), 404, 'Documento no encontrado.');

        $path = public_path('docs/' . $filename);

        abort_unless(file_exists($path), 404, 'Archivo no disponible.');

        return response()->download($path, $filename, [
            'Content-Type' => self::ALLOWED[$filename],
        ]);
    }
}
