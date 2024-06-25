<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MoveFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $destinationPath;
    protected $fileName;

    public function __construct($filePath, $destinationPath, $fileName)
    {
        $this->filePath = $filePath;
        $this->destinationPath = $destinationPath;
        $this->fileName = $fileName;
    }

    public function handle()
    {
        // Ensure the destination directory exists
        if (!Storage::disk('local')->exists($this->destinationPath)) {
            Storage::disk('local')->makeDirectory($this->destinationPath);
        }

        // Move the file from the temporary path to the destination path
        $newPath = Storage::disk('local')->put($this->destinationPath . '/' . $this->fileName, file_get_contents($this->filePath));

        if (!$newPath) {
            Log::error("Failed to move the file: {$this->fileName}");
            return;
        }

        Log::info("File moved successfully: {$newPath}");
    }
}
