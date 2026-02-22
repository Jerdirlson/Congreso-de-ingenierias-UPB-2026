<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador de prueba para subida de archivos.
 * SOLO activo en APP_ENV=local — no usar en producción.
 */
class UploadTestController extends Controller
{
    /** GET /api/dev/files — Lista todos los archivos subidos */
    public function index(): JsonResponse
    {
        $files = collect(Storage::disk('public')->files('uploads'))
            ->map(fn ($path) => [
                'name'     => basename($path),
                'path'     => $path,
                'url'      => Storage::disk('public')->url($path),
                'size'     => Storage::disk('public')->size($path),
                'mime'     => Storage::disk('public')->mimeType($path),
                'modified' => Storage::disk('public')->lastModified($path),
            ])
            ->sortByDesc('modified')
            ->values();

        return response()->json($files);
    }

    /** POST /api/dev/upload — Sube cualquier tipo de archivo */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:2097152', // 2 GB en KB
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        return response()->json([
            'name'     => basename($path),
            'path'     => $path,
            'url'      => Storage::disk('public')->url($path),
            'size'     => $file->getSize(),
            'mime'     => $file->getMimeType(),
            'modified' => now()->timestamp,
        ], 201);
    }

    /** GET /api/dev/files/{filename}/download — Descarga un archivo con headers correctos */
    public function download(string $filename): mixed
    {
        $path = 'uploads/' . basename($filename);

        if (! Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return Storage::disk('public')->download($path, $filename);
    }

    /** DELETE /api/dev/files/{filename} — Elimina un archivo */
    public function destroy(string $filename): JsonResponse
    {
        $path = 'uploads/' . basename($filename);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(null, 204);
    }
}
