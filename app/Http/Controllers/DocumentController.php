<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index()
    {
       $documents = Document::where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('pages.documents', [
            'title' => 'Dokumen Saya & Arsip',
            'documents' => $documents,
        ]);
    }

    public function create()
    {
        return view('pages.editor', [
            'title'    => 'Studio Composer Dokumen',
            'document' => null,
        ]);
    }

    public function edit(Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);

        return view('pages.editor', [
            'title'    => 'Edit: '.$document->title,
            'document' => $document,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'type'           => ['nullable', 'string'],
            'header_data'    => ['required', 'array'],
            'body_content'   => ['nullable', 'array'],
            'footer_data'    => ['required', 'array'],
            'signature_data' => ['nullable', 'array'],
            'status'         => ['nullable', 'in:draft,final,archived'],
        ]);

        $document = Document::create([
            'user_id'        => Auth::id(),
            'title'          => $data['title'],
            'type'           => $data['type'] ?? 'umum',
            'header_data'    => $data['header_data'],
            'body_content'   => $data['body_content'] ?? [],
            'footer_data'    => $data['footer_data'],
            'signature_data' => $data['signature_data'] ?? null,
            'status'         => $data['status'] ?? 'draft',
        ]);

        return response()->json([
            'message' => 'Dokumen berhasil disimpan.',
            'id'      => $document->id,
        ]);
    }

    public function update(Request $request, Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);

        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'header_data'    => ['required', 'array'],
            'body_content'   => ['nullable', 'array'],
            'footer_data'    => ['required', 'array'],
            'signature_data' => ['nullable', 'array'],
            'status'         => ['nullable', 'in:draft,final,archived'],
        ]);

        $document->update($data);

        return response()->json(['message' => 'Perubahan tersimpan.']);
    }

    public function destroy(Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);
        $document->delete();
        return response()->json(['message' => 'Dokumen dipindah ke trash.']);
    }
}