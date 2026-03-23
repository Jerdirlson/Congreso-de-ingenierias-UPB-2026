<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicDocController extends Controller
{
    private const DOCX_MIME = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    private const ALLOWED = [
        'Call_for_papers_V1.docx' => self::DOCX_MIME,
        'Location.docx'           => self::DOCX_MIME,
    ];

    public function download(string $filename): BinaryFileResponse
    {
        abort_unless(isset(self::ALLOWED[$filename]), 404, 'Documento no encontrado.');

        return response()->download(public_path('docs/' . $filename), $filename, [
            'Content-Type' => self::ALLOWED[$filename],
        ]);
    }
}
