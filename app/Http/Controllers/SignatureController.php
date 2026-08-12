<?php

namespace App\Http\Controllers;

use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureController extends Controller
{
    public function index() 
    {
        $signatures = Signature::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'type' => $s->type,
                'url' => Storage::url($s->image_path),
            ]);

        return view('pages.signatures', [
            'title' => 'Studio Tanda Tangan & e-Sign',
            'signatures' => $signatures, 
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:draw,upload'],
            'image' => ['required', 'string'],
        ]);

        [$meta, $base64] = explode(',', $data['image'], 2);
        $imageData = base64_decode($base64);

        $filename = 'signatures/'.Auth::id(). '_' .Str::random(10). '.png';
        Storage::disk('public')->put($filename, $imageData);

        $signature = Signature::create([
            'user_id' => Auth::id(),
            'name' => $data['name'] ?? 'Tanda Tangan',
            'type' => $data['type'],
            'image_path' => $filename,
        ]);

        return response()->json([
            'message' => 'Tanda Tangan Tersimpan.',
            'signature' => [
                'id' => $signature->id,
                'name' => $signature->name,
                'url' => Storage::url($signature->image_path),
            ],
        ]);
    }

    public function destroy(Signature $signature) 
    {
        abort_unless($signature->user_id === Auth::id(), 403);
        Storage::disk('public')->delete($signature->image_path);
        $signature->delete();
        return response()->json(['message' => 'Tanda Tangan dihapus.']);
    }
}
