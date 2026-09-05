<?php

namespace App\Http\Controllers;

use App\Helpers\MenuHelper;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Semua dokumen milik user (dasar penghitungan kartu ringkasan).
        $documents = Document::where('user_id', $userId)->get();

        $totalDocuments = $documents->count();

        // Jumlah dokumen per status — mengikuti enum alur kerja baru
        // (draft, review_marketing, revisi, disetujui).
        $draftDocuments = $documents->where('status', 'draft')->count();
        $revisiDocuments = $documents->where('status', 'revisi')->count();
        $reviewDocuments = $documents->where('status', 'review_marketing')->count();
        $disetujuiDocuments = $documents->where('status', 'disetujui')->count();

        // 5 dokumen terbaru
        $recentDocuments = $documents->sortByDesc('created_at')->take(5)->values();

        return view('pages.dashboard', compact(
            'documents',
            'totalDocuments',
            'draftDocuments',
            'revisiDocuments',
            'reviewDocuments',
            'disetujuiDocuments',
            'recentDocuments'
        ));
    }
}
