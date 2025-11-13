<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Display a listing of files.
     */
    public function index()
    {
        $files = File::latest()->paginate(20);
        return view('files.index', compact('files'));
    }

    /**
     * Show the form for uploading a new file.
     */
    public function create()
    {
        return view('files.create');
    }

    /**
     * Store a newly uploaded file.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:20120', // 20MB ish
        ]);

        static::PROCESS_ACTUAL_FILE($request->file('file'));

        return redirect()->route('files.index')
            ->with('success', 'File uploaded successfully!');
    }
    public static function PROCESS_ACTUAL_FILE(UploadedFile $uploaded):File
    {
        $uuid = (string) Str::uuid();
        $uploaded->storeAs('uploads', $uuid, 'public');

        $file = File::create([
            'uuid'          => $uuid,
            'original_name' => $uploaded->getClientOriginalName(),
            'mime_type'     => $uploaded->getClientMimeType(),
            'size'          => $uploaded->getSize(),
        ]);
        $file->save();
        return $file;
    }
    /**
     * Display a specific file's metadata.
     */
    public function show(File $file)
    {
        return view('files.show', compact('file'));
    }

    /**
     * Serve the file back to the user with original filename.
     */
    public function download(File $file): StreamedResponse
    {
        return Storage::disk('public')->download(
            'uploads/' . $file->uuid,
            $file->original_name
        );
    }

    /**
     * Remove the file from storage & database.
     */
    public function destroy(File $file)
    {
        Storage::disk('public')->delete('uploads/' . $file->uuid);
        $file->delete();

        return redirect()->route('files.index')
            ->with('success', 'File deleted successfully.');
    }
}
