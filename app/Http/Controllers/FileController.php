<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $filePath = $file->store('uploads');

        $fileDetails = [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
            'path' => $filePath
        ];

        return view('welcome', compact('fileDetails'));
    }

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
    $request->validate([
        'filePath' => 'required|string',
        'fileName' => 'required|string',
        'fileLocation' => 'nullable|string'
    ]);

    // Adjusting the file location to point to an 'encrypted_files' directory
    $fileLocation = $request->input('fileLocation') ? rtrim($request->input('fileLocation'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'encrypted_files' : base_path('encrypted_files');

    $fileName = $request->input('fileName');

    // Generating encryption key and IV
    $key = openssl_random_pseudo_bytes(32);  // 256-bit key
    $iv = openssl_random_pseudo_bytes(16);   // 128-bit IV

    // Environment variable handling (although storing these in .env is not recommended)
    // self::clear_env('ENCRYPTION_KEY');
    // self::clear_env('ENCRYPTION_IV');
    // self::set_env('ENCRYPTION_KEY', base64_encode($key)); // Encode to Base64 to save as string
    // self::set_env('ENCRYPTION_IV', base64_encode($iv));

    // Constructing the full file path from the provided path and file name
    $fullFilePath = $request->input('filePath');
    if (!str_starts_with($fullFilePath, '/')) {
        $fullFilePath = storage_path('app/' . $fullFilePath); // Ensure this path is correct as per your file structure
    }

    if (file_exists($fullFilePath)) {
        $data = file_get_contents($fullFilePath);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);

        // Ensuring the encrypted directory exists
        if (!is_dir($fileLocation)) {
            mkdir($fileLocation, 0755, true);
        }

        $encryptedFilePath = $fileLocation . DIRECTORY_SEPARATOR . $fileName . '.enc';
        file_put_contents($encryptedFilePath, $encrypted);

        return response()->json(['message' => 'File encrypted successfully!', 'type' => 'encrypt']);
    } else {
        return response()->json(['error' => 'File does not exist.', 'type' => 'encrypt'], 404);
    }
}


    public function decrypt(Request $request)
{
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

    try {
        $encryptedData = file_get_contents(storage_path('app/' . $filePath));
        $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);

        if ($decrypted === false) {
            return response()->json([
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
