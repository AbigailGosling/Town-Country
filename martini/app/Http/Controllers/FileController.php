<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
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
    public static function PROCESS_REQUEST(Request $request,string $key):File
    {
        return static::PROCESS_ACTUAL_FILE($request->file($key));
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
    public static function PROCESS_BASE64_IMAGE_FILE(string $base64Data):File
    {
        $uuid = (string) Str::uuid();
        $data = base64_decode(str_replace('data:image/png;base64,', '', $base64Data));
        $path = "uploads/{$uuid}";
        Storage::disk('public')->put($path, $data);

        $file = File::create([
            'uuid' => $uuid,
            'original_name' => "file_{$uuid}.png",
            'mime_type' => 'image/png',
            'size' => strlen($data),
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
     * Display the file as an image by uuid.
     */
    public function showImage(string $uuid): StreamedResponse
    {
        /** @var FilesystemAdapter $fs */
        $fs = Storage::disk('public');
        $file = File::where('uuid', $uuid)->firstOrFail();
        return $fs->response(
            'uploads/' . $file->uuid,
            $file->original_name
        );
    }
    /**
     * Serve the file back to the user with original filename as download.
     */
    public function download(File $file): StreamedResponse
    {
        /** @var FilesystemAdapter $fs */
        $fs = Storage::disk('public');
        return $fs->download(
            'uploads/' . $file->uuid,
            $file->original_name
        );
    }
    /**
     * Display the file in a new tab (inline view).
     */
    public function view(File $file): StreamedResponse
    {
        /** @var FilesystemAdapter $fs */
        $fs = Storage::disk('public');
        return $fs->response(
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
