<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;

class AbstractFileExtractorService
{
    public function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'pdf'  => $this->extractFromPdf($file),
            'docx' => $this->extractFromDocx($file),
            default => throw new RuntimeException("Formato no soportado: {$extension}. Solo se aceptan PDF y DOCX."),
        };
    }

    private function extractFromPdf(UploadedFile $file): string
    {
        try {
            $parser = new PdfParser();
            $pdf    = $parser->parseFile($file->getRealPath());
            $text   = $pdf->getText();

            if (empty(trim($text))) {
                throw new RuntimeException('El PDF no contiene texto extraíble. Asegúrate de que no sea una imagen escaneada.');
            }

            return trim($text);
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RuntimeException('No se pudo leer el archivo PDF: ' . $e->getMessage());
        }
    }

    private function extractFromDocx(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException('La extensión ZipArchive de PHP no está disponible en el servidor.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo DOCX. Puede estar corrupto.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException('El archivo DOCX no contiene el documento principal esperado.');
        }

        // Insertar espacio en los límites de párrafo y celda de tabla antes de
        // eliminar los tags, evitando que palabras de párrafos distintos se peguen.
        $xml = preg_replace('/<\/w:p>|<\/w:tc>/', ' ', $xml) ?? $xml;
        // Saltos de línea explícitos del documento también se convierten en espacio
        $xml = preg_replace('/<w:br[^>]*\/>/', ' ', $xml) ?? $xml;

        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }
}
