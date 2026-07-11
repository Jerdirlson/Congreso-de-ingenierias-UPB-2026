<?php

namespace App\Services;

use RuntimeException;

/**
 * Valida que el texto extraído de un resumen siga la estructura de la
 * plantilla oficial del congreso (public/docs/Plantilla_Resumen.docx):
 * secciones Resumen, Palabras claves, Abstract, Key Words y Referencias,
 * correos de los autores y extensión del resumen.
 *
 * Cada sección se acepta con su rótulo seguido de dos puntos (en cualquier
 * combinación de mayúsculas) o como encabezado en MAYÚSCULAS sin dos puntos,
 * para no rechazar variaciones legítimas de formato al exportar a PDF.
 */
class AbstractTemplateValidatorService
{
    /** Rango tolerado para la sección Resumen (la regla oficial es 300–500). */
    private const MIN_RESUMEN_WORDS = 250;
    private const MAX_RESUMEN_WORDS = 650;

    private const SECTIONS = [
        'la sección "Resumen:"'         => '/(?i:\bresumen\s*:)|\bRESUMEN\b/u',
        'la sección "Palabras claves:"' => '/(?i:palabras\s+claves?\s*:)|\bPALABRAS\s+CLAVES?\b/u',
        'la sección "Abstract:"'        => '/(?i:\babstract\s*:)|\bABSTRACT\b/u',
        'la sección "Key Words:"'       => '/(?i:key\s*-?\s*words?\s*:)|\bKEY\s*WORDS?\b/u',
        'la sección "Referencias"'      => '/referencias/iu',
    ];

    /** Texto instructivo de la plantilla que el ponente debía reemplazar. */
    private const PLACEHOLDER_PATTERN = '/insertar el t[ií]tulo de la ponencia|deben evidenciarse\s*:?\s*objetivos|identifican el tema central de la ponencia/iu';

    /**
     * Devuelve la lista de problemas de estructura; vacía si cumple la plantilla.
     *
     * @return string[]
     */
    public function problems(string $text): array
    {
        $t = preg_replace('/\s+/u', ' ', trim($text)) ?? $text;

        $problems = [];
        $missing  = [];

        foreach (self::SECTIONS as $label => $pattern) {
            if (! preg_match($pattern, $t)) {
                $missing[] = $label;
            }
        }
        if ($missing !== []) {
            $problems[] = 'falta ' . implode(', ', $missing);
        }

        if (! preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $t)) {
            $problems[] = 'no se encontraron los correos electrónicos de los autores';
        }

        if (preg_match(self::PLACEHOLDER_PATTERN, $t)) {
            $problems[] = 'contiene texto de instrucciones de la plantilla sin reemplazar';
        }

        if (preg_match('/\bresumen\s*:(.*?)(?:palabras\s+claves?\s*:|\babstract\s*:|$)/isu', $t, $m)) {
            $words = count(preg_split('/\s+/u', trim($m[1]), -1, PREG_SPLIT_NO_EMPTY));
            if ($words < self::MIN_RESUMEN_WORDS || $words > self::MAX_RESUMEN_WORDS) {
                $problems[] = "la sección Resumen tiene {$words} palabras y debe tener entre 300 y 500";
            }
        }

        return $problems;
    }

    /**
     * Lanza RuntimeException con un mensaje apto para el ponente si el texto
     * no cumple la estructura de la plantilla oficial.
     */
    public function validateOrFail(string $text): void
    {
        $problems = $this->problems($text);

        if ($problems !== []) {
            throw new RuntimeException(
                'El archivo no cumple la plantilla oficial del congreso: '
                . implode('; ', $problems)
                . '. Descarga la plantilla oficial (Plantilla_Resumen.docx) desde la convocatoria y conserva su estructura: solo debe cambiar el contenido.'
            );
        }
    }
}
