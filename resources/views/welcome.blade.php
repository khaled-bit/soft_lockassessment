<!DOCTYPE html>
<html>
<head>
    <title>File Encryption App</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .alert {
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
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h2>Decrypt File</h2>
                <div id="decryptMessages"></div>
                <div id="flashMessages">
                    @if (Session::has('success'))
                        <div class="alert alert-success mt-3">
                            {{ Session::get('success') }}
                        </div>
                    @endif

                    @if (Session::has('error'))
                        <div class="alert alert-danger mt-3">
                            {{ Session::get('error') }}
                        </div>
                    @endif
                </div>
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
                </form>
            </div>
        </div>
    @endif

</div>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#encryptForm, #decryptForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                success: function(response) {
                    var messageHtml = '<div class="alert alert-success">' + response.message + '</div>';
                    displayFlashMessage(messageHtml, response.type);
                },
                error: function(response) {
                    var errorMessage = response.responseJSON ? response.responseJSON.message : 'An error occurred';
                    var messageHtml = '<div class="alert alert-danger">' + errorMessage + '</div>';
                    displayFlashMessage(messageHtml, response.responseJSON.type);
                }
            });
        });

        function displayFlashMessage(messageHtml, action) {
            var messageContainer = (action === 'encrypt') ? '#encryptMessages' : '#decryptMessages';
            $(messageContainer).html(messageHtml);
            $(messageContainer + ' .alert').fadeIn().delay(5000).fadeOut();
        }
    });
    </script>


</body>
</html>
