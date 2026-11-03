<?php

namespace App\Http\Controllers\Client;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\DocumentRequest;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private const HEADERS = ['X-Content-Type-Options' => 'nosniff'];

    public function index(Request $request)
    {
        $user = $request->user();

        $documents = $user->documents()
            ->with('vehicle')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('vehicle'), fn ($q) => $q->where('vehicle_id', $request->integer('vehicle')))
            ->latest()
            ->get();

        return view('client.documents.index', [
            'documents' => $documents,
            'vehicles' => $user->vehicles()->orderBy('plate')->get(),
            'types' => DocumentType::cases(),
        ]);
    }

    public function create(Request $request)
    {
        return view('client.documents.create', [
            'vehicles' => $request->user()->vehicles()->orderBy('plate')->get(),
            'types' => DocumentType::cases(),
            'selectedVehicle' => $request->integer('vehicle') ?: null,
        ]);
    }

    public function store(DocumentRequest $request)
    {
        $user = $request->user();
        $file = $request->file('file');

        $path = $file->store('documents/' . $user->id, Document::DISK);

        $user->documents()->create([
            'vehicle_id' => $request->validated('vehicle_id'),
            'type' => $request->validated('type'),
            'title' => $request->validated('title'),
            'expires_at' => $request->validated('expires_at'),
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
        ]);

        return redirect()->route('client.documents.index')->with('status', 'Document ajouté au coffre-fort.');
    }

    /** Consultation dans le navigateur (PDF, image). */
    public function show(Document $document)
    {
        Gate::authorize('view', $document);

        return response()->file(
            Storage::disk(Document::DISK)->path($document->path),
            self::HEADERS + [
                'Content-Type' => $document->mime,
                'Content-Disposition' => 'inline; filename="' . $document->downloadName() . '"',
            ]
        );
    }

    public function download(Document $document)
    {
        Gate::authorize('view', $document);

        return Storage::disk(Document::DISK)->download($document->path, $document->downloadName(), self::HEADERS);
    }

    public function destroy(Document $document)
    {
        Gate::authorize('delete', $document);

        $document->delete();

        return back()->with('status', 'Document supprimé.');
    }
}