<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController  extends Controller
{
    public function handleChunk(Request $request)
    {
        $file = $request->file('file');
        $identifier = $request->input('resumableIdentifier');
        $filename = $identifier . '.' . $file->getClientOriginalExtension();
        $chunkNumber = $request->input('resumableChunkNumber');

        // Build the storage path
        $chunkPath = "chunks/{$identifier}/chunk{$chunkNumber}";
        // Store the chunk
        //Storage::disk('local')->put($chunkPath, file_get_contents($file->getRealPath()));
        $stream = fopen($file->getRealPath(), 'rb'); // Open the file as a binary read-only stream
        Storage::disk('local')->writeStream($chunkPath, $stream); // Save it directly from the stream
        fclose($stream); // Close the stream to free up memory

        // Check if all chunks are uploaded
        if ($this->areAllChunksUploaded($identifier, $request->input('resumableTotalChunks'), $file->getClientOriginalExtension())) {
            $filename = $file->getClientOriginalName();
            $this->createFileFromChunks($identifier, $filename);
        }

        return response()->json(['status' => 'success']);
    }

    private function areAllChunksUploaded($identifier, $totalChunks, $extension)
    {
        for ($i = 1; $i <= $totalChunks; $i++) {
            if (!Storage::disk('local')->exists("chunks/{$identifier}/chunk{$i}")) {
                return false;
            }
        }
        return true;
    }

    private function createFileFromChunks($identifier, $filename)
    {
        $directory = storage_path("app/merged");
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true); // Ensure the directory exists
        }

        $filePath = $directory . '/' . $filename;
        $handle = fopen($filePath, 'a');
        if ($handle === false) {
            Log::error("Failed to open file for writing: {$filePath}");
            return;
        }

        $chunkFiles = Storage::disk('local')->files("chunks/{$identifier}");
        foreach ($chunkFiles as $chunkFile) {
            fwrite($handle, Storage::disk('local')->get($chunkFile));
            Storage::disk('local')->delete($chunkFile);
        }
        fclose($handle);
        Storage::disk('local')->deleteDirectory("chunks/{$identifier}");

    }
}
