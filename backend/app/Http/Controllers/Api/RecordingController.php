<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stream;
use App\Models\StreamRecording;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecordingController extends Controller
{
    /**
     * POST /api/streams/{stream}/recordings
     * Sube un video y lo asocia al stream.
     */
    public function store(Request $request, Stream $stream): JsonResponse
    {
        $validated = $request->validate([
            'title'      => 'nullable|string|max:255',
            'visibility' => 'in:public,registered,private',
            'file'       => 'required|file|mimes:mp4,webm,mov,avi,mkv|max:2097152', // 2 GB en KB
        ]);

        $file = $request->file('file');
        $path = $file->store('videos', 'public');

        $recording = $stream->recordings()->create([
            'title'      => $validated['title'] ?? $stream->title,
            'file_path'  => $path,
            'file_size'  => $file->getSize(),
            'format'     => $file->getClientOriginalExtension(),
            'visibility' => $validated['visibility'] ?? 'public',
            'status'     => 'ready',
        ]);

        $recording->url = Storage::disk('public')->url($path);

        return response()->json($recording, 201);
    }

    /**
     * GET /api/recordings/{recording}
     * Devuelve metadatos + URL directa de Nginx para reproducir.
     */
    public function show(StreamRecording $recording): JsonResponse
    {
        $recording->increment('view_count');
        $recording->url = Storage::disk('public')->url($recording->file_path);
        $recording->load('stream:id,title,slug');

        return response()->json($recording);
    }

    /**
     * DELETE /api/recordings/{recording}
     * Elimina el archivo del disco y el registro de la DB.
     */
    public function destroy(StreamRecording $recording): JsonResponse
    {
        if (Storage::disk('public')->exists($recording->file_path)) {
            Storage::disk('public')->delete($recording->file_path);
        }

        $recording->delete();

        return response()->json(null, 204);
    }
}
