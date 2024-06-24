<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Events\ProgressUpdated;
use App\Events\FileEncryptedSuccessfully;
class EncryptFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
   protected $filePath;
   protected $fileLocation;
    protected $fileName;

    protected $chunkSize = 1048576; // 1MB per chunk

    public function __construct($filePath, $fileName,$fileLocation)
    {
        $this->filePath = $filePath;

        $this->fileName = $fileName;

        $this->fileLocation=$fileLocation;
    }

    public function handle()
    {

        $fileSize = Storage::size($this->filePath);
        $handle = fopen(storage_path('app/' . $this->filePath), 'rb');
        $key = base64_decode(env('ENCRYPTION_KEY'));
        $iv = base64_decode(env('ENCRYPTION_IV'));

        $processedSize = 0;
        $outputPath = $this->fileLocation . '/' . $this->fileName;
        $outputHandle = fopen($outputPath, 'wb');

        $data = Storage::get($this->filePath);
        $header = "FILE_TYPE:ENCRYPTED";

        // Check if the file already contains the encryption header
        if (substr($data, 0, strlen($header)) === $header) {
            // Log error or handle already encrypted case
            return;
        }
        fwrite($outputHandle, $header);
        while (!feof($handle)) {
            $chunk = fread($handle, $this->chunkSize);
            $encrypted = openssl_encrypt($chunk, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            fwrite($outputHandle, $encrypted);
            $processedSize += strlen($chunk);

            // Clear memory of no longer needed variables
            unset($chunk, $encrypted);

            // Force garbage collection occasionally


            $progress = ($processedSize / $fileSize) * 100;
            event(new ProgressUpdated($this->fileName, $progress));
            sleep(1);
        }
        fclose($handle);
        fclose($outputHandle);

        // Final progress update
        event(new ProgressUpdated($this->fileName, 100));
        return response()->download($outputPath);
        //event(new FileEncryptedSuccessfully($outputPath));
    }
}
