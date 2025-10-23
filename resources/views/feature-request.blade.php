<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feature Request</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md animate__animated fade-in">
        <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Submit a Feature Request</h1>

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded animate__animated animate__shakeX">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded animate__animated animate__fadeIn">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('feature.request.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-medium">Title</label>
                <input type="text" name="title" id="title" required class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500" placeholder="Feature title">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium">Description</label>
                <textarea name="description" id="description" required class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500" rows="5" placeholder="Describe the feature..."></textarea>
            </div>
            <div class="mb-4">
                <label for="attachment" class="block text-gray-700 font-medium">Attachment (Optional)</label>
                <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.pdf" class="w-full p-2 border rounded">
            </div>
            <button type="submit" id="submit_button" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition">Submit Request</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const submitButton = document.getElementById('submit_button');

            form.addEventListener('submit', function () {
                submitButton.disabled = true;
                submitButton.classList.add('btn-loading');
                Toastify({
                    text: "Submitting feature request...",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#3b82f6",
                }).showToast();
            });
        });
    </script>
</body>
</html>