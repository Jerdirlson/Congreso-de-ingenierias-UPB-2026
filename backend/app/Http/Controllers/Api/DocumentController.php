<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $documents = Document::query()
            ->published()
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->event,    fn ($q, $e) => $q->where('event_id', $e))
            ->when($request->search,   fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('abstract', 'like', "%{$s}%");
            }))
            ->when($request->tags,     fn ($q, $t) => $q->whereHas('tags', fn ($q) => $q->whereIn('slug', (array) $t)))
            ->with(['category:id,name,slug,color', 'uploader:id,name', 'tags:id,name,slug', 'authors'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($documents);
    }

    public function show(Document $document): JsonResponse
    {
        $document->incrementViews();
        $document->load(['category', 'event', 'uploader', 'tags', 'authors', 'currentVersion', 'versions.uploader']);
        return response()->json($document);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'required|string|unique:documents',
            'abstract'         => 'nullable|string',
            'status'           => 'in:draft,under_review,published',
            'visibility'       => 'in:public,registered,private',
            'category_id'      => 'nullable|exists:document_categories,id',
            'event_id'         => 'nullable|exists:events,id',
            'language'         => 'string|max:5',
            'doi'              => 'nullable|string',
            'publication_year' => 'nullable|integer|min:1900|max:2100',
            'file'             => 'required|file|mimes:pdf,docx,pptx,xlsx,zip|max:102400',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $document = Document::create([
            ...$validated,
            'uploaded_by' => auth()->id(),
            'file_type'   => $file->getClientOriginalExtension(),
            'file_size'   => $file->getSize(),
        ]);

        // Create first version
        $document->versions()->create([
            'uploaded_by'    => auth()->id(),
            'version_number' => '1.0',
            'is_current'     => true,
            'file_path'      => $path,
            'file_size'      => $file->getSize(),
        ]);

        return response()->json($document->load('currentVersion'), 201);
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'abstract'    => 'nullable|string',
            'status'      => 'sometimes|in:draft,under_review,published,rejected,archived',
            'visibility'  => 'sometimes|in:public,registered,private',
            'category_id' => 'nullable|exists:document_categories,id',
            'event_id'    => 'nullable|exists:events,id',
            'language'    => 'sometimes|string|max:5',
            'doi'         => 'nullable|string',
        ]);

        $document->update($validated);

        return response()->json($document);
    }

    public function destroy(Document $document): JsonResponse
    {
        $document->delete();
        return response()->json(null, 204);
    }

    public function download(Document $document): mixed
    {
        $version = $document->currentVersion;

        if (! $version || ! Storage::disk('public')->exists($version->file_path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        $document->incrementDownloads();

        return Storage::disk('public')->download($version->file_path, $document->title . '.' . $document->file_type);
    }
}
