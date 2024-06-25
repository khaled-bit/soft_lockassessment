<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload and Encrypt File</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/resumable.js/1.1.0/resumable.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Upload and Encrypt File</h2>
        <input type="file" id="resumable-browse" multiple class="form-control mb-3" />
        <button id="resumable-upload-start" class="btn btn-primary mb-3">Start Upload</button>
        <div class="progress mt-3" style="display: none;">
            <div id="upload-progress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div id="file-details" class="mt-3" style="display:none;">
            <h4>File Details</h4>
            <ul class="list-group">
                <li class="list-group-item">Name: <span id="file-name"></span></li>
                <li class="list-group-item">Size: <span id="file-size"></span></li>
                <li class="list-group-item">Extension: <span id="file-extension"></span></li>
            </ul>
        </div>
        @if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

        <form action="/encrypt" method="post" id="encryptForm" style="display: none;">
            @csrf
            <input type="hidden" id="uploadedFileName" name="uploadedFileName">


            <button type="submit" class="btn btn-success">Encrypt</button>
        </form>

        <form action="/decrypt" method="post" id="decryptForm" style="display: none;">
            @csrf
            <input type="hidden" id="uploadedFileName" name="uploadedFileName">

            <button type="submit" class="btn btn-success">Decrypt</button>
        </form>
    </div>

    <script>
        var r = new Resumable({
            target: '/upload_chunk',
            query: {_token: '{{ csrf_token() }}'},
            chunkSize: 10 * 1024 * 1024,
            simultaneousUploads: 3,
            testChunks: false,
            throttleProgressCallbacks: 1,
            fileType: ['jpg', 'iso', 'pdf', 'txt']
        });

        r.assignBrowse(document.getElementById('resumable-browse'));
        document.getElementById('resumable-upload-start').addEventListener('click', function(){
            r.upload();
        });

        r.on('fileAdded', function(file) {
            document.getElementById('resumable-upload-start').style.display = 'block';
            document.getElementById('file-details').style.display = 'block';
            document.getElementById('file-name').textContent = file.fileName.replace(/\.[^/.]+$/, "");
            document.getElementById('file-size').textContent = formatFileSize(file.size);
            document.getElementById('file-extension').textContent = file.fileName.split('.').pop();
        });

        r.on('fileProgress', function(file) {
            var progress = Math.floor(file.progress() * 100);
            var progressBar = document.getElementById('upload-progress');
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            progressBar.textContent = progress + '%';
            document.querySelector('.progress').style.display = 'block';
        });

        r.on('fileSuccess', function(file, message) {
            document.getElementById('upload-progress').textContent = 'Upload Complete';
            document.getElementById('upload-progress').style.width = '100%';
            document.getElementById('encryptForm').style.display = 'block';
            document.getElementById('decryptForm').style.display = 'block';
            document.getElementById('uploadedFileName').value = file.fileName;
        });

        r.on('fileError', function(file, message) {
            console.log('Error uploading', message);
        });



        function formatFileSize(bytes) {
            if (bytes >= 1073741824) {
                return (bytes / 1073741824).toFixed(2) + ' GB';
            } else if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            } else {
                return bytes + ' bytes';
            }
        }
    </script>
</body>
</html>
