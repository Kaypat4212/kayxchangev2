<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Requirements - KayXchange Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .install-container {
            max-width: 900px;
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
        
        .requirement-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .requirement-item:last-child {
            border-bottom: none;
        }
        
        .status-icon {
            font-size: 1.2em;
        }
        
        .status-success {
            color: #28a745;
        }
        
        .status-error {
            color: #dc3545;
        }
        
        .status-warning {
            color: #ffc107;
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
        
        .btn-install:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <i class="fas fa-server fa-2x mb-3"></i>
                <h2>System Requirements Check</h2>
                <p class="mb-0">Verifying your server meets all requirements</p>
            </div>
            
            <div class="install-body">
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step active">2</div>
                    <div class="step">3</div>
                    <div class="step">4</div>
                    <div class="step">5</div>
                </div>
                
                <!-- PHP Version Check -->
                <div class="requirement-section">
                    <h5><i class="fab fa-php text-primary"></i> PHP Version</h5>
                    <div class="requirement-item">
                        <div>
                            <strong>Required: {{ $checks['php_version']['required'] }} or higher</strong>
                            <br>
                            <small class="text-muted">Current: {{ $checks['php_version']['current'] }}</small>
                        </div>
                        <div>
                            @if($checks['php_version']['status'])
                                <i class="fas fa-check-circle status-icon status-success"></i>
                            @else
                                <i class="fas fa-times-circle status-icon status-error"></i>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- PHP Extensions -->
                <div class="requirement-section">
                    <h5><i class="fas fa-puzzle-piece text-primary"></i> PHP Extensions</h5>
                    @foreach($checks['extensions']['extensions'] as $extension => $status)
                    <div class="requirement-item">
                        <div>
                            <strong>{{ $extension }}</strong>
                        </div>
                        <div>
                            @if($status)
                                <i class="fas fa-check-circle status-icon status-success"></i>
                            @else
                                <i class="fas fa-times-circle status-icon status-error"></i>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- File Permissions -->
                <div class="requirement-section">
                    <h5><i class="fas fa-lock text-primary"></i> Directory Permissions</h5>
                    @foreach($checks['permissions']['permissions'] as $path => $permission)
                    <div class="requirement-item">
                        <div>
                            <strong>{{ $path }}</strong>
                            <br>
                            <small class="text-muted">
                                Required: {{ $permission['required'] }} | 
                                Current: {{ $permission['current'] }}
                            </small>
                        </div>
                        <div>
                            @if($permission['status'])
                                <i class="fas fa-check-circle status-icon status-success"></i>
                            @else
                                <i class="fas fa-times-circle status-icon status-error"></i>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Additional Checks -->
                <div class="requirement-section">
                    <h5><i class="fas fa-tools text-primary"></i> Additional Requirements</h5>
                    
                    <div class="requirement-item">
                        <div>
                            <strong>Composer Dependencies</strong>
                            <br>
                            <small class="text-muted">Vendor directory with autoloader</small>
                        </div>
                        <div>
                            @if($checks['composer']['status'])
                                <i class="fas fa-check-circle status-icon status-success"></i>
                            @else
                                <i class="fas fa-times-circle status-icon status-error"></i>
                            @endif
                        </div>
                    </div>
                    
                    <div class="requirement-item">
                        <div>
                            <strong>Node.js (Optional)</strong>
                            <br>
                            <small class="text-muted">{{ $checks['node']['version'] }}</small>
                        </div>
                        <div>
                            @if($checks['node']['status'])
                                <i class="fas fa-check-circle status-icon status-success"></i>
                            @else
                                <i class="fas fa-exclamation-triangle status-icon status-warning"></i>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if(!$canProceed)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Requirements Not Met!</strong>
                    <p class="mb-0 mt-2">Please fix the issues above before proceeding with the installation.</p>
                    <ul class="mt-2 mb-0">
                        @if(!$checks['php_version']['status'])
                            <li>Upgrade PHP to version {{ $checks['php_version']['required'] }} or higher</li>
                        @endif
                        @if(!$checks['extensions']['all_passed'])
                            <li>Install missing PHP extensions</li>
                        @endif
                        @if(!$checks['permissions']['all_passed'])
                            <li>Fix directory permissions (chmod 775)</li>
                        @endif
                        @if(!$checks['composer']['status'])
                            <li>Run "composer install" to install dependencies</li>
                        @endif
                    </ul>
                </div>
                @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>All Requirements Met!</strong>
                    <p class="mb-0 mt-2">Your server meets all the requirements. You can proceed with the installation.</p>
                </div>
                @endif
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('install.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    
                    <div>
                        <button type="button" class="btn btn-outline-primary me-2" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Recheck
                        </button>
                        
                        <a href="{{ route('install.database') }}" 
                           class="btn btn-install {{ !$canProceed ? 'disabled' : '' }}"
                           @if(!$canProceed) onclick="return false;" @endif>
                            Continue <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-white">
                <i class="fas fa-shield-alt"></i>
                KayXchange Installation Step 2 of 5
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>