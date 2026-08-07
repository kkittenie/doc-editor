<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function create()
    {
        return view('pages.editor', [
            'title'    => 'Studio Composer Dokumen',
            'document' => null,
        ]);
    }

    // Buka editor dengan dokumen lama yang mau di-edit
    public function edit(Document $document)
    {
        // pastikan dokumen ini punya user yang login, bukan punya orang lain
        abort_unless($document->user_id === Auth::id(), 403);

        return view('pages.editor', [
            'title'    => 'Edit: '.$document->title,
            'document' => $document,
        ]);
    }

    // Simpan dokumen baru
    public function store(Request $request)
    {
        return response()->json(['message' => 'Dokumen masuk controller']);

        // $document = Document::create([


        //     'user_id' => Auth::id(),


        //     'title' => 'Dokumen Baru',


        //     'type' => 'kontrak',


        //     'header_data' => [

        //         'kopInstansi' => $request->kopInstansi,

        //         'kopAlamat' => $request->kopAlamat,

        //     ],



        //     'body_content' => $request->isiDokumen,



        //     'footer_data' => [

        //         'kota' => $request->kota,

        //         'jabatan' => $request->jabatan,

        //     ],



        //     'signature_data' => [

        //         'nama' => $request->nama,

        //     ],



        //     'status' => 'draft'


        // ]);



        // dd($document);

    }
}