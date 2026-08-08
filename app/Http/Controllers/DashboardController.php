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

        //total semua document
        $totalDocuments = Document::where('user_id', $userId)->count();

        //jumlah draft document
        $draftDocuments = Document::where('user_id', $userId)
            ->where('status', 'draft')
            ->count();

        //jumlah document final
        $finalDocuments = Document::where('user_id', $userId)
            ->where('status', 'final')
            ->count();

        //5 dokumen terbaru
        $recentDocuments = Document::where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        return view('pages.dashboard', compact(
            'totalDocuments',
            'draftDocuments',
            'finalDocuments',
            'recentDocuments'
        ));
    }
}
