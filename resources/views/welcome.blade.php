<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Encryption App</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
          .alert, #progressBar, #downloadLink {
            display: none;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">File Encryption App</h1>
    <div class="card">
        <div class="card-body">
            <form action="/upload" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file">Choose a file to upload:</label>
                    <input type="file" class="form-control-file" id="file" name="file" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>
    </div>

    @if (isset($fileDetails))
    <div class="card mt-3">
        <div class="card-body">
            <h2>File Details</h2>
            <ul class="list-group">
                <li class="list-group-item">Name: {{ $fileDetails['name'] }}</li>
                <li class="list-group-item">Size: {{ number_format($fileDetails['size'] / 1024, 2) }} KB</li>
                <li class="list-group-item">Extension: {{ $fileDetails['extension'] }}</li>
            </ul>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h2>Encrypt File</h2>
            <div id="encryptMessages"></div>
            <form action="/encrypt" method="post" id="encryptForm">
                @csrf
                <input type="hidden" name="filePath" value="{{ $fileDetails['path'] }}">
                <div class="form-group">
                    <label for="fileName">Save as (encrypted):</label>
                    <input type="text" class="form-control" id="fileName" name="fileName" required>
                </div>
                <div class="form-group">
                    <label for="fileLocation">Save to (location):</label>
                    <input type="text" class="form-control" id="fileLocation" name="fileLocation" placeholder="Enter folder path">
                </div>
                <button type="submit" class="btn btn-success">Encrypt</button>
                <div id="progressBar" class="progress mt-2">
                    <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h2>Decrypt File</h2>
            <div id="decryptMessages"></div>
            <form action="/decrypt" method="post" id="decryptForm">
                @csrf
                <input type="hidden" name="filePath" value="{{ $fileDetails['path'] }}">
                <div class="form-group">
                    <label for="fileName">Save as (decrypted):</label>
                    <input type="text" class="form-control" id="fileName" name="fileName" required>
                </div>
                <div class="form-group">
                    <label for="fileLocation">Save to (location):</label>
                    <input type="text" class="form-control" id="fileLocation" name="fileLocation" placeholder="Enter folder path">
                </div>
                <button type="submit" class="btn btn-danger">Decrypt</button>
                <div id="progressBar" class="progress mt-2">
                    <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script src="{{ mix('/js/app.js') }}"></script>

<script>
    // Setup Echo for listening to server events
    Echo.channel('file-progress')
    .listen('FileEncryptedSuccessfully', (event) => {
        console.log('Encrypted file is ready at:', event.filePath);

    });
</script>

<script>
  $(document).ready(function() {
    $('#encryptForm, #decryptForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this); // Create a FormData object
        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: formData,
            processData: false, // tell jQuery not to process the data
            contentType: false, // tell jQuery not to set contentType
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = parseInt((evt.loaded / evt.total) * 100);
                        $('#progressBar .progress-bar').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            beforeSend: function() {
                $('#progressBar').show();
                $('#progressBar .progress-bar').width('0%').text('0%');
            },
            success: function(response) {
    console.log(response);  // Check what is being returned
    var messageHtml = '<div class="alert alert-success">File processed successfully</div>';
    $('#encryptMessages').html(messageHtml).fadeIn().delay(5000).fadeOut();
    alert('File has been processed! Click OK to download.');
    window.location.href = `/download?path=${encodeURIComponent(response.filePath)}`;
},

            error: function(xhr) {
                $('#progressBar').hide();
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                var messageHtml = '<div class="alert alert-danger">' + errorMessage + '</div>';
                $('#encryptMessages').html(messageHtml).fadeIn().delay(5000).fadeOut();
            }
        });
    });
});


</script>

</body>
</html>
