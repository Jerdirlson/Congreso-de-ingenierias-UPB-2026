<?php

namespace App\Services;

use App\Models\ThematicAxis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class LlmClassificationService
{
    private string $apiKey;
    private string $model;
    private float  $confidenceThreshold;

    public function __construct()
    {
        $this->apiKey             = config('services.gemini.key', '');
        $this->model              = config('services.gemini.model', 'gemini-1.5-flash');
        $this->confidenceThreshold = (float) config('services.gemini.confidence_threshold', 70);
    }

    /**
     * Classify an abstract against thematic axes using Google Gemini.
     *
     * @return array{axis_id: int|null, confidence_score: float, justification: string, raw_response: array}
     * @throws RuntimeException on API or configuration errors
     */
    public function classify(string $abstractContent, \Illuminate\Support\Collection $axes): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('La clave GEMINI_API_KEY no está configurada en el servidor.');
        }

        $axesContext = $axes->map(fn (ThematicAxis $a) => [
            'id'          => $a->id,
            'name'        => $a->name,
            'description' => $a->description,
            'keywords'    => $a->keywords,
        ])->toJson(JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
Eres un clasificador de resúmenes académicos para un congreso de ingenierías.

Tienes los siguientes ejes temáticos disponibles (en JSON):

{$axesContext}

Resumen a clasificar:

---
{$abstractContent}
---

Responde ÚNICAMENTE con un JSON válido, sin texto adicional ni bloques de código, con esta estructura exacta:
{
  "axis_id": <id del eje que mejor encaja, o null si ninguno encaja>,
  "confidence_score": <número entre 0 y 100 indicando tu confianza>,
  "justification": "<breve explicación en español de por qué asignaste o no este eje>"
}

Si el resumen no encaja con ningún eje o la confianza es menor a 70, usa axis_id: null.
PROMPT;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        try {
            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.1,
                        'maxOutputTokens' => 2048,
                    ],
                ]);

            $body = $response->json();

            // Gemini devuelve errores en body con clave "error"
            if (isset($body['error'])) {
                $errMsg = $body['error']['message'] ?? 'Error desconocido de Gemini';
                Log::error('Gemini API error', ['body' => $body]);
                throw new RuntimeException($this->friendlyGeminiError($body['error']['code'] ?? 0, $errMsg));
            }

            if ($response->failed()) {
                Log::error('Gemini HTTP error', ['status' => $response->status(), 'body' => $body]);
                throw new RuntimeException('Error de comunicación con Gemini (HTTP ' . $response->status() . ').');
            }

            $text   = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $parsed = $this->parseResponse($text);
            $parsed['raw_response'] = $body;

            return $parsed;

        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('LlmClassificationService unexpected error', ['error' => $e->getMessage()]);
            throw new RuntimeException('Error inesperado al contactar Gemini: ' . $e->getMessage());
        }
    }

    private function friendlyGeminiError(int $code, string $original): string
    {
        return match ($code) {
            400 => 'Solicitud inválida a Gemini: ' . $original,
            401, 403 => 'Clave GEMINI_API_KEY inválida o sin permisos.',
            429 => 'Límite de solicitudes de Gemini alcanzado. Intenta en unos segundos.',
            500, 503 => 'Los servidores de Gemini no están disponibles. Intenta de nuevo.',
            default  => 'Error de Gemini (' . $code . '): ' . $original,
        };
    }

    private function parseResponse(string $text): array
    {
        $default = [
            'axis_id'          => null,
            'confidence_score' => 0,
            'justification'    => $text,
        ];

        // Quitar posibles bloques de código markdown ```json ... ```
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $clean = preg_replace('/```\s*$/m', '', $clean ?? $text);

        $json = preg_replace('/^.*?(\{[\s\S]*\}).*$/s', '$1', $clean ?? $text);
        if ($json === null) {
            return $default;
        }

        $data = json_decode($json, true);
        if (! is_array($data)) {
            return $default;
        }

        return [
            'axis_id'          => isset($data['axis_id']) ? (int) $data['axis_id'] : null,
            'confidence_score' => (float) ($data['confidence_score'] ?? 0),
            'justification'    => (string) ($data['justification'] ?? ''),
        ];
    }

    public function getConfidenceThreshold(): float
    {
        return $this->confidenceThreshold;
    }

    public function isApproved(float $score): bool
    {
        return $score >= $this->confidenceThreshold;
    }
}
