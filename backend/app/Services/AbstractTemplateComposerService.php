<?php

namespace App\Services;

use App\Models\SubmissionAbstract;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Reconstruye el documento de un resumen sobre la plantilla oficial del
 * congreso a partir del texto guardado en la base de datos. Se usa SOLO para
 * resúmenes históricos sin archivo original (stored_path null): el resultado
 * queda en generated_path y siempre se presenta como "reconstruido", nunca
 * como el archivo que subió el autor. El contenido no se modifica: si el
 * texto no encaja en la estructura de la plantilla, se monta tal cual y los
 * problemas quedan en template_problems para mostrarlos como advertencia.
 */
class AbstractTemplateComposerService
{
    /** Mismos rótulos que valida AbstractTemplateValidatorService, con offsets. */
    private const SECTION_LABELS = [
        '/(?i:\bresumen\s*:)|\bRESUMEN\b/u',
        '/(?i:palabras\s+claves?\s*:)|\bPALABRAS\s+CLAVES?\b/u',
        '/(?i:\babstract\s*:)|\bABSTRACT\b/u',
        '/(?i:key\s*-?\s*words?\s*:)|\bKEY\s*WORDS?\b/u',
        '/referencias(\s*\/\s*references)?\s*:?/iu',
    ];

    public function __construct(private AbstractTemplateValidatorService $validator)
    {
    }

    /** Genera el docx sobre la plantilla y actualiza el registro. Devuelve la ruta relativa. */
    public function compose(SubmissionAbstract $abstract): string
    {
        $templatePath = public_path('docs/Plantilla_Resumen.docx');
        if (! is_file($templatePath)) {
            throw new RuntimeException('No se encontró la plantilla oficial en public/docs.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'abs') . '.docx';
        if (! copy($templatePath, $tmp)) {
            throw new RuntimeException('No se pudo copiar la plantilla a un archivo temporal.');
        }

        try {
            $zip = new ZipArchive();
            if ($zip->open($tmp) !== true) {
                throw new RuntimeException('No se pudo abrir la copia de la plantilla.');
            }

            $xml = $zip->getFromName('word/document.xml');
            if ($xml === false) {
                $zip->close();
                throw new RuntimeException('La plantilla no contiene word/document.xml.');
            }

            // Conservar todo excepto los párrafos del cuerpo: el sectPr referencia
            // los encabezados con el logo oficial y no debe tocarse.
            $bodyStart = strpos($xml, '<w:body>');
            $sectStart = strpos($xml, '<w:sectPr');
            if ($bodyStart === false || $sectStart === false) {
                $zip->close();
                throw new RuntimeException('La plantilla tiene una estructura de documento inesperada.');
            }

            $bodyStart += strlen('<w:body>');
            $newXml = substr($xml, 0, $bodyStart)
                . $this->buildBodyXml($abstract->content)
                . substr($xml, $sectStart);

            $zip->addFromString('word/document.xml', $newXml);
            $zip->close();

            $relative = 'generated_abstracts/' . $abstract->submission_id
                . '/resumen_v' . $abstract->version . '_plantilla.docx';
            Storage::disk('local')->put($relative, file_get_contents($tmp));
        } finally {
            @unlink($tmp);
        }

        $abstract->update([
            'generated_path'    => $relative,
            'template_problems' => $this->validator->problems($abstract->content),
        ]);

        return $relative;
    }

    /** Cuerpo del documento: secciones con rótulo en negrita si el texto encaja; texto corrido si no. */
    private function buildBodyXml(string $text): string
    {
        $flat = trim(preg_replace('/\s+/u', ' ', $this->stripInvalidXmlChars($text)) ?? $text);

        $found = [];
        foreach (self::SECTION_LABELS as $pattern) {
            if (preg_match($pattern, $flat, $m, PREG_OFFSET_CAPTURE)) {
                $found[] = ['start' => $m[0][1], 'len' => strlen($m[0][0]), 'label' => $m[0][0]];
            }
        }
        usort($found, fn ($a, $b) => $a['start'] <=> $b['start']);

        // Sin al menos Resumen + 2 secciones más ubicables, se monta el texto tal cual
        if (count($found) < 3) {
            return $this->paragraph([[$flat, false]]);
        }

        $xml = '';

        // Encabezado (título, autores, correos): lo que precede a la primera sección
        $head = trim(substr($flat, 0, $found[0]['start']));
        if ($head !== '') {
            $xml .= $this->paragraph([[$head, true]], 'center');
        }

        foreach ($found as $i => $section) {
            $from    = $section['start'] + $section['len'];
            $to      = isset($found[$i + 1]) ? $found[$i + 1]['start'] : strlen($flat);
            $content = trim(substr($flat, $from, $to - $from));

            $xml .= $this->paragraph([
                [rtrim($section['label']) . ' ', true],
                [$content, false],
            ]);
        }

        return $xml;
    }

    /** @param array<array{0: string, 1: bool}> $runs pares [texto, negrita] */
    private function paragraph(array $runs, string $align = 'both'): string
    {
        $xml = '<w:p><w:pPr><w:spacing w:after="160"/><w:jc w:val="' . $align . '"/></w:pPr>';

        foreach ($runs as [$run, $bold]) {
            if ($run === '') {
                continue;
            }
            $xml .= '<w:r><w:rPr><w:rFonts w:ascii="Spranq eco sans" w:hAnsi="Spranq eco sans"/>'
                . ($bold ? '<w:b/>' : '')
                . '</w:rPr><w:t xml:space="preserve">'
                . htmlspecialchars($run, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                . '</w:t></w:r>';
        }

        return $xml . '</w:p>';
    }

    private function stripInvalidXmlChars(string $text): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', ' ', $text) ?? $text;
    }
}
