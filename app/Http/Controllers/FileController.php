<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Jobs\MoveFileJob;
class FileController extends Controller
{
    public function index()
    {
        return view('welcome');
    }


public function upload(Request $request)
{
    $request->validate([
        'file' => 'required|file',
    ]);

    $file = $request->file('file');
    $fileName = $file->getClientOriginalName();
    //$temporaryPath = $file->getPathname(); // Get the temporary path
    $destinationPath = public_path('fff');
    $filePath = $file->getRealPath();
    // Dispatch the job to move the file
    MoveFileJob::dispatch($filePath, $destinationPath, $fileName);

    // Prepare file details for the response
    $fileDetails = [
        'name' => $fileName,
        'size' => $file->getSize(),
        'extension' => $file->getClientOriginalExtension(),
        'path' => $destinationPath . '/' . $fileName
    ];

    return view('welcome', compact('fileDetails'));
}

        // Debugbar::addMessage('Uploading file...');

        // //prevent sql injection , XSS attacks like <script>alert('Hi')</script>
        // $request->validate([
        //     'file' => 'required|file'
        // ]);

        // $file = $request->file('file');
        // $filePath = $file->store('uploads');

        // $fileDetails = [
        //     'name' => $file->getClientOriginalName(),
        //     'size' => $file->getSize(),
        //     'extension' => $file->getClientOriginalExtension(),
        //     'path' => $filePath
        // ];

        // return view('welcome', compact('fileDetails'));


    public function set_env($key, $value)
    {
        $path = app()->environmentFilePath();

        $escaped = preg_quote('=' . env($key), '/');
       file_put_contents($path, preg_replace(
            "/^{$key}{$escaped}/m",
            "{$key}={$value}",
            file_get_contents($path)
     ));
    }

    function clear_env($key)
{
    $path = app()->environmentFilePath();
    // Read the whole .env file into an array of lines
    $lines = file($path, FILE_IGNORE_NEW_LINES);

    foreach ($lines as $index => $line) {
        // Check if the current line contains the key
        if (strstr($line, $key) !== false) {
            $lines[$index] = $key . '='; // Set the key with an empty value
        }
    }

    // Write the updated array back to the file
    file_put_contents($path, implode(PHP_EOL, $lines));
}
public function encrypt(Request $request)
{

    $uploadedFileName = $request->input('uploadedFileName'); // Assuming this is provided correctly


 self::clear_env('ENCRYPTION_KEY');
    self::clear_env('ENCRYPTION_IV');

  $key = openssl_random_pseudo_bytes(32);  // 256-bit key
    $iv = openssl_random_pseudo_bytes(16);   // 128-bit IV

    self::set_env('ENCRYPTION_KEY', base64_encode($key)); // Encode to Base64 to save as string
    self::set_env('ENCRYPTION_IV', base64_encode($iv));

         $chunkSize = 1048576; // 1MB per chunk

        // Construct file path and validate
        // $filePath = storage_path('app' . DIRECTORY_SEPARATOR . 'merged' . DIRECTORY_SEPARATOR . $uploadedFileName);
        $filePath = 'D:\eee.txt';


        $handle = fopen($filePath, 'rb');

        // Prepare the response for a download
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $uploadedFileName . '"'
        ];



        return response()->stream(function() use ($handle, $key, $iv, $chunkSize) {
            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                $encryptedChunk = openssl_encrypt($chunk, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
                echo $encryptedChunk;
            }
            fclose($handle);
        }, 200, $headers);


}


//     {
//         $uploadedFileName = $request->input('uploadedFileName'); // Assume this comes as '123_filename.ext'
//         $newFilename = $request->input('fileName');
//         $fileLocation = $request->input('fileLocation');

//         // Construct file path and validate
//         $filePath = 'storage/app/merged/' . $uploadedFileName. '.pdf'; // Ensure 'merged' is the correct directory under 'storage/app'
//         if (!Storage::exists($uploadedFileName)) {
//          //      return $filePath;
//         return response()->json(['error' => 'File not found.'], 404);
//         }

//         $fileSize = Storage::size($filePath);
//         $handle = fopen(storage_path('app/' . $filePath), 'rb');

//         // Encryption keys
//         $key = base64_decode(env('ENCRYPTION_KEY'));
//         $iv = base64_decode(env('ENCRYPTION_IV'));

//         $chunkSize = 1048576; // 1MB per chunk
//         $processedSize = 0;

//         // Output filename handling
//         $actualFileName = preg_replace('/^\d+_/', '', $newFilename); // Remove digits and underscore from new filename
//         $outputPath = $fileLocation . '/' . $actualFileName;
//         if (!file_exists(dirname($outputPath))) {
//             mkdir(dirname($outputPath), 0777, true); // Ensure directory exists
//         }
//         $outputHandle = fopen($outputPath, 'wb');

//         // Check for already encrypted file
//         $data = fread($handle, 8192); // Check initial data for header
//         $header = "FILE_TYPE:ENCRYPTED";
//         if (substr($data, 0, strlen($header)) === $header) {
//             fclose($handle);
//             fclose($outputHandle);
//             return response()->json(['error' => 'File is already encrypted.'], 400);
//         }

//         // Write the encryption header
//         fwrite($outputHandle, $header);
//         rewind($handle); // Rewind after reading for header check

//         // Encryption loop
//         while (!feof($handle)) {
//             $chunk = fread($handle, $chunkSize);
//             $encrypted = openssl_encrypt($chunk, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
//             fwrite($outputHandle, $encrypted);
//             $processedSize += strlen($chunk);

//             // Optionally clear memory
//             unset($chunk, $encrypted);

//             // Throttle the encryption to prevent server overload
//             usleep(100000); // Sleep for 100 milliseconds
//         }

//         fclose($handle);
//         fclose($outputHandle);

//         // Optionally log that encryption was successful
//         Log::info("File encrypted successfully: " . $outputPath);

//         return response()->download($outputPath);

// }
        // $request->validate([
        //     'filename' => 'required|string', // Ensure this matches your expected request parameter
        // ]);

        // $filename = $request->input('filename');
        // $sourcePath = storage_path('app/merged/' . $filename);
        // $destPath = storage_path('app/chunks/' . $filename . '.enc'); // Destination path for the encrypted file

        // if (!$handle = fopen($sourcePath, 'rb')) {
        //     return response()->json(['error' => 'Failed to open source file.'], 404);
        // }

        // if (!$outputHandle = fopen($destPath, 'wb')) {
        //     fclose($handle);
        //     return response()->json(['error' => 'Failed to open destination file.'], 500);
        // }

        // $key = base64_decode(env('ENCRYPTION_KEY'));
        // $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        // $header = "FILE_TYPE:ENCRYPTED";
        // $buffer = fread($handle, strlen($header));
        // if ($buffer === $header) {
        //     fclose($handle);
        //     fclose($outputHandle);
        //     return response()->json(['error' => 'File is already encrypted.'], 400);
        // }

        // rewind($handle);
        // fwrite($outputHandle, $header);

        // while (!feof($handle)) {
        //     $chunk = fread($handle, 4096);
        //     $encrypted = openssl_encrypt($chunk, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        //     fwrite($outputHandle, $encrypted);
        //     $iv = substr($encrypted, -openssl_cipher_iv_length('aes-256-cbc'));
        // }

        // fclose($handle);
        // fclose($outputHandle);

        // return response()->download($destPath, basename($destPath));
    //     $uploadedFilename = $request->input('uploadedFilename');
    // $newFilename = $request->input('newFilename');
    // $storageLocation = $request->input('storageLocation');

    // $tempFilePath = storage_path('app/temp/' . $uploadedFilename);
    // $content = file_get_contents($tempFilePath);
    // $encryptedContent =($content);

    // Storage::put($storageLocation . '/' . $newFilename, $encryptedContent);

    // // Delete temporary file
    // unlink($tempFilePath);

    // return response()->json([
    //     'success' => true,
    //     'filePath' => $storageLocation . '/' . $newFilename
    // ]);
    // }






    // // Adjusting the file location to point to an 'encrypted_files' directory
    // $defaultFileLocation = base_path('encrypted_files');
    // $fileLocation = $request->input('fileLocation') ? rtrim($request->input('fileLocation'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'encrypted_files' : $defaultFileLocation;


    // if (!is_dir($fileLocation) && !mkdir($fileLocation, 0755, true) && !is_dir($fileLocation)) {
    //     return response()->json(['error' => 'Failed to create directory for encrypted files.', 'type' => 'encrypt'], 500);
    // }

    // $fileName = $request->input('fileName');

    // // Generating encryption key and IV
    // $key = openssl_random_pseudo_bytes(32);  // 256-bit key
    // $iv = openssl_random_pseudo_bytes(16);   // 128-bit IV

    // // //     //Environment variable handling (although storing these in .env is not recommended)
    // // self::clear_env('ENCRYPTION_KEY');
    // // self::clear_env('ENCRYPTION_IV');
    // // self::set_env('ENCRYPTION_KEY', base64_encode($key)); // Encode to Base64 to save as string
    // // self::set_env('ENCRYPTION_IV', base64_encode($iv));
    // // $key = base64_decode(env('ENCRYPTION_KEY'));
    // // $iv = base64_decode(env('ENCRYPTION_IV'));


    // // Constructing the full file path from the provided path and file name
    // $fullFilePath = $request->input('filePath');
    // if (!str_starts_with($fullFilePath, '/')) {
    //     $fullFilePath = storage_path('app/' . $fullFilePath);
    // }

    // // Check if the file exists before attempting to encrypt
    // if (!file_exists($fullFilePath)) {
    //     return response()->json(['error' => 'File does not exist.', 'type' => 'encrypt'], 404);
    // }

    // $data = file_get_contents($fullFilePath);
    // $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    // $encryptedDataWithKeyIV = base64_encode($key) . '::' . base64_encode($iv) . '::' . $encrypted;

    // $encryptedFilePath = $fileLocation . DIRECTORY_SEPARATOR . $fileName . '.enc';
    // if (file_put_contents($encryptedFilePath, $encryptedDataWithKeyIV) === false) {
    //     return response()->json(['error' => 'Failed to write encrypted file.', 'type' => 'encrypt'], 500);
    // }

    // return response()->json(['message' => 'File encrypted successfully!', 'type' => 'encrypt']);
//}
// public function encrypt(Request $request)
// {
//     Debugbar::addMessage('encrpt file...');

//     $request->validate([
//         'filePath' => 'required|string',
//         'fileName' => 'required|string',
//         'fileLocation' => 'nullable|string'
//     ]);

//     // Adjusting the file location to point to an 'encrypted_files' directory
//     $fileLocation = $request->input('fileLocation') ? rtrim($request->input('fileLocation'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'encrypted_files' : base_path('encrypted_files');

//     $fileName = $request->input('fileName');

//     // Generating encryption key and IV
//     $key = openssl_random_pseudo_bytes(32);  // 256-bit key
//     $iv = openssl_random_pseudo_bytes(16);   // 128-bit IV

//     //Environment variable handling (although storing these in .env is not recommended)
//     self::clear_env('ENCRYPTION_KEY');
//     self::clear_env('ENCRYPTION_IV');
//     self::set_env('ENCRYPTION_KEY', base64_encode($key)); // Encode to Base64 to save as string
//     self::set_env('ENCRYPTION_IV', base64_encode($iv));

//     // Constructing the full file path from the provided path and file name
//     $fullFilePath = $request->input('filePath');
//     if (!str_starts_with($fullFilePath, '/')) {
//         $fullFilePath = storage_path('app/' . $fullFilePath); // Ensure this path is correct as per your file structure
//     }

//     if (file_exists($fullFilePath)) {
//         $data = file_get_contents($fullFilePath);
//         $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);

//         // Ensuring the encrypted directory exists
//         if (!is_dir($fileLocation)) {
//             mkdir($fileLocation, 0755, true);
//         }

//         $encryptedFilePath = $fileLocation . DIRECTORY_SEPARATOR . $fileName . '.enc';
//         file_put_contents($encryptedFilePath, $encrypted);

//         return response()->json(['message' => 'File encrypted successfully!', 'type' => 'encrypt']);
//     } else {
//         return response()->json(['error' => 'File does not exist.', 'type' => 'encrypt'], 404);
//     }
// }


    public function decrypt(Request $request)
{
    Debugbar::addMessage('decrpt file...');
    try {
    $request->validate([
        'filePath' => 'required|string',
        'fileName' => 'required|string',
        'fileLocation' => 'nullable|string'
    ]);


    $filePath = $request->input('filePath');
    $key = base64_decode(env('ENCRYPTION_KEY'));
    $iv = base64_decode(env('ENCRYPTION_IV'));


    $fileName = $request->input('fileName');
    $fileLocation = $request->input('fileLocation') ? rtrim($request->input('fileLocation'), DIRECTORY_SEPARATOR) : base_path('decrypted_files');
    $encryptedData = file_get_contents(storage_path('app/' . $filePath));
    list($encodedKey, $encodedIv, $encryptedData) = explode('::', $encryptedData, 3);
    $key = base64_decode($encodedKey);
    $iv = base64_decode($encodedIv);

    // Proceed with decryption



    if (strlen($key) !== 32) {
        return response()->json([
            'error' => 'The key must be exactly 32 bytes long.',
            'type' => 'decrypt'
        ], 400);
    }

    if (strlen($iv) !== 16) {
        return response()->json([
            'error' => 'The IV must be exactly 16 bytes long.',
            'type' => 'decrypt'
        ], 400);
    }



    $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);

        if ($decrypted === false) {
            return response()->json([
                'key'=>  $key,
                'iv'=>$iv,
                'error' => 'Decryption failed. Please check your key and IV.',
                'type' => 'decrypt'
            ], 400);
        }

        $decryptedFilePath = $fileLocation . DIRECTORY_SEPARATOR . $fileName . '.dec';

        if (!is_dir(dirname($decryptedFilePath))) {
            mkdir(dirname($decryptedFilePath), 0755, true);
        }

        file_put_contents($decryptedFilePath, $decrypted);

        return response()->json(['message' => 'File decrypted successfully!', 'type' => 'decrypt']);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'An error occurred during decryption: ' . $e->getMessage(),
            'type' => 'decrypt'
        ], 500);
    }
}

}
