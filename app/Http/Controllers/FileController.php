<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Jobs\EncryptFile;
use App\Jobs\DecryptFile;
class FileController extends Controller
{
    public function index()
    {
        return view('welcome');
    }


// FileController.php

public function upload(Request $request)
    {
        Debugbar::addMessage('Uploading file...');
        $request->validate(['file' => 'required|file']);

        $file = $request->file('file');
        $filePath = $file->store('uploads');

        Debugbar::info('File uploaded to: ' . $filePath);
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
    Debugbar::addMessage('encrypt file...');
    $request->validate([
        'filePath' => 'required|string',
        'fileName' => 'required|string',
        'fileLocation' => 'nullable|string'
    ]);

    // Adjusting the file location to point to an 'encrypted_files' directory
    // $defaultFileLocation = base_path('encrypted_files');
    // $fileLocation = $request->input('fileLocation') ? rtrim($request->input('fileLocation'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'encrypted_files' : $defaultFileLocation;


    // if (!is_dir($fileLocation) && !mkdir($fileLocation, 0755, true) && !is_dir($fileLocation)) {
    //     return response()->json(['error' => 'Failed to create directory for encrypted files.', 'type' => 'encrypt'], 500);
    // }

    // $fileName = $request->input('fileName');

    // Generating encryption key and IV
    // $key = openssl_random_pseudo_bytes(32);  // 256-bit key
    // $iv = openssl_random_pseudo_bytes(16);   // 128-bit IV

    // $fullFilePath = $request->input('filePath');
    // if (!str_starts_with($fullFilePath, '/')) {
    //     $fullFilePath = storage_path('app/' . $fullFilePath);
    // }

    // Check if the file exists before attempting to encrypt
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


    $filePath = $request->input('filePath');
    $newFileName = $request->input('fileName');
    $fileLocation = $request->input('fileLocation');
    if (!Storage::exists($filePath)) {
        return response()->json(['error' => 'File not found.'], 404);
    }

    Debugbar::addMessage($fileLocation);
    Debugbar::addMessage($newFileName);
    Debugbar::addMessage($filePath);
    // Dispatch the encryption job
   EncryptFile::dispatch($filePath, $newFileName,$fileLocation);

    return response()->json(['message' => 'Encryption job dispatched.', 'filePath' => 'encrypted/' . $newFileName]);
}

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
