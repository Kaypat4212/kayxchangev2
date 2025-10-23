<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Configuration - KayXchange Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .install-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .install-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        
        .install-body {
            padding: 40px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            position: relative;
        }
        
        .step.active {
            background: #667eea;
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            color: white;
        }
        
        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 20px;
            height: 2px;
            background: #e9ecef;
            transform: translateY(-50%);
        }
        
        .step:last-child::after {
            display: none;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .btn-install {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .test-connection-btn {
            background: #28a745;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .test-connection-btn:hover {
            background: #218838;
            color: white;
        }
        
        .connection-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }
        
        .connection-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .connection-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <i class="fas fa-database fa-2x mb-3"></i>
                <h2>Database Configuration</h2>
                <p class="mb-0">Configure your database connection settings</p>
            </div>
            
            <div class="install-body">
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step completed">2</div>
                    <div class="step active">3</div>
                    <div class="step">4</div>
                    <div class="step">5</div>
                </div>
                
                @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <form method="POST" action="{{ route('install.database.store') }}" id="databaseForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="db_host" name="db_host" 
                                       value="{{ old('db_host', 'localhost') }}" required>
                                <label for="db_host">Database Host</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="db_port" name="db_port" 
                                       value="{{ old('db_port', '3306') }}" required>
                                <label for="db_port">Port</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="db_name" name="db_name" 
                               value="{{ old('db_name') }}" required>
                        <label for="db_name">Database Name</label>
                        <div class="form-text">
                            Make sure this database exists on your server
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" id="db_username" name="db_username" 
                               value="{{ old('db_username') }}" required>
                        <label for="db_username">Database Username</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="db_password" name="db_password" 
                               value="{{ old('db_password') }}">
                        <label for="db_password">Database Password</label>
                        <div class="form-text">
                            Leave empty if no password is required
                        </div>
                    </div>
                    
                    <div class="text-center mb-4">
                        <button type="button" class="btn test-connection-btn" onclick="testConnection()">
                            <i class="fas fa-plug"></i> Test Connection
                        </button>
                        
                        <div id="connectionStatus" class="connection-status">
                            <i class="fas fa-spinner fa-spin"></i> Testing connection...
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Important Notes:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Make sure the database exists before proceeding</li>
                            <li>The database user must have full privileges (CREATE, ALTER, DROP, etc.)</li>
                            <li>We recommend creating a dedicated database for KayXchange</li>
                            <li>Backup any existing data before proceeding</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('install.requirements') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        
                        <button type="submit" class="btn btn-install">
                            Continue <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-white">
                <i class="fas fa-shield-alt"></i>
                KayXchange Installation Step 3 of 5
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testConnection() {
            const status = document.getElementById('connectionStatus');
            const testBtn = document.querySelector('.test-connection-btn');
            
            // Show status and disable button
            status.style.display = 'block';
            status.className = 'connection-status';
            status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing connection...';
            testBtn.disabled = true;
            
            // Get form data
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('db_host', document.getElementById('db_host').value);
            formData.append('db_port', document.getElementById('db_port').value);
            formData.append('db_name', document.getElementById('db_name').value);
            formData.append('db_username', document.getElementById('db_username').value);
            formData.append('db_password', document.getElementById('db_password').value);
            
            // Test connection via AJAX
            fetch('{{ route("install.database.test") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    status.className = 'connection-status connection-success';
                    status.innerHTML = '<i class="fas fa-check-circle"></i> Connection successful! You can proceed.';
                } else {
                    status.className = 'connection-status connection-error';
                    status.innerHTML = '<i class="fas fa-times-circle"></i> Connection failed: ' + data.message;
                }
            })
            .catch(error => {
                status.className = 'connection-status connection-error';
                status.innerHTML = '<i class="fas fa-times-circle"></i> Connection test failed. Please check your settings.';
            })
            .finally(() => {
                testBtn.disabled = false;
            });
        }
        
        // Auto-hide status when form fields change
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                const status = document.getElementById('connectionStatus');
                status.style.display = 'none';
            });
        });
    </script>
</body>
</html>