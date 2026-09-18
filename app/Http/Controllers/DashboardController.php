<?php

namespace App\Http\Controllers;

use App\Helpers\MenuHelper;
use App\Models\Customer;
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

        // Jumlah dokumen per status — mengikuti enum kontrak terbaru
        // (draft, on_progress, on_review, revisi, disetujui, archived).
        $draftDocuments = $documents->where('status', 'draft')->count();
        $onProgressDocuments = $documents->where('status', 'on_progress')->count();
        $reviewDocuments = $documents->where('status', 'on_review')->count();
        $revisiDocuments = $documents->where('status', 'revisi')->count();
        $disetujuiDocuments = $documents->where('status', 'disetujui')->count();

        // Ringkasan pelanggan/kontrak (Tabel Pelanggan di halaman Dokumen Saya).
        $totalCustomers = Customer::where('user_id', $userId)->count();

        // 5 dokumen terbaru
        $recentDocuments = $documents->sortByDesc('created_at')->take(5)->values();

        return view('pages.dashboard', compact(
            'documents',
            'totalDocuments',
            'draftDocuments',
            'onProgressDocuments',
            'reviewDocuments',
            'revisiDocuments',
            'disetujuiDocuments',
            'totalCustomers',
            'recentDocuments'
        ));
    }
}
