<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Configuration - KayXchange Installation</title>
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
        
        .config-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .config-section h5 {
            color: #495057;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
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
        
        .toggle-section {
            cursor: pointer;
            user-select: none;
        }
        
        .toggle-section:hover {
            background: #e9ecef;
        }
        
        .collapsible-content {
            display: none;
            padding-top: 20px;
        }
        
        .collapsible-content.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <i class="fas fa-cog fa-2x mb-3"></i>
                <h2>Application Configuration</h2>
                <p class="mb-0">Configure your KayXchange platform settings</p>
            </div>
            
            <div class="install-body">
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step completed">2</div>
                    <div class="step completed">3</div>
                    <div class="step active">4</div>
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
                
                <form method="POST" action="{{ route('install.application.store') }}">
                    @csrf
                    
                    <!-- Basic Application Settings -->
                    <div class="config-section">
                        <h5><i class="fas fa-info-circle text-primary"></i> Basic Application Settings</h5>
                        
                        <div class="form-floating">
                            <input type="text" class="form-control" id="app_name" name="app_name" 
                                   value="{{ old('app_name', 'KayXchange') }}" required>
                            <label for="app_name">Application Name</label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="url" class="form-control" id="app_url" name="app_url" 
                                   value="{{ old('app_url', 'http://localhost') }}" required>
                            <label for="app_url">Application URL</label>
                            <div class="form-text">
                                The full URL where your application will be accessible (e.g., https://yourdomain.com)
                            </div>
                        </div>
                    </div>
                    
                    <!-- Admin Account -->
                    <div class="config-section">
                        <h5><i class="fas fa-user-shield text-primary"></i> Administrator Account</h5>
                        
                        <div class="form-floating">
                            <input type="text" class="form-control" id="admin_name" name="admin_name" 
                                   value="{{ old('admin_name') }}" required>
                            <label for="admin_name">Admin Name</label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                   value="{{ old('admin_email') }}" required>
                            <label for="admin_email">Admin Email</label>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                                    <label for="admin_password">Admin Password</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="admin_password_confirmation" 
                                           name="admin_password_confirmation" required>
                                    <label for="admin_password_confirmation">Confirm Password</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Configuration -->
                    <div class="config-section">
                        <div class="toggle-section" onclick="toggleSection('email-config')">
                            <h5><i class="fas fa-envelope text-primary"></i> Email Configuration <i class="fas fa-chevron-down float-end"></i></h5>
                        </div>
                        
                        <div id="email-config" class="collapsible-content">
                            <div class="form-floating">
                                <select class="form-select" id="mail_driver" name="mail_driver" required onchange="toggleMailFields()">
                                    <option value="smtp" {{ old('mail_driver') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="mailgun" {{ old('mail_driver') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                    <option value="ses" {{ old('mail_driver') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                    <option value="sendmail" {{ old('mail_driver') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    <option value="log" {{ old('mail_driver') == 'log' ? 'selected' : '' }}>Log (Testing)</option>
                                </select>
                                <label for="mail_driver">Mail Driver</label>
                            </div>
                            
                            <div id="smtp-fields">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="mail_host" name="mail_host" 
                                                   value="{{ old('mail_host') }}">
                                            <label for="mail_host">SMTP Host</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="mail_port" name="mail_port" 
                                                   value="{{ old('mail_port', '587') }}">
                                            <label for="mail_port">Port</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="mail_username" name="mail_username" 
                                           value="{{ old('mail_username') }}">
                                    <label for="mail_username">SMTP Username</label>
                                </div>
                                
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="mail_password" name="mail_password" 
                                           value="{{ old('mail_password') }}">
                                    <label for="mail_password">SMTP Password</label>
                                </div>
                                
                                <div class="form-floating">
                                    <select class="form-select" id="mail_encryption" name="mail_encryption">
                                        <option value="tls" {{ old('mail_encryption') == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ old('mail_encryption') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="null" {{ old('mail_encryption') == 'null' ? 'selected' : '' }}>None</option>
                                    </select>
                                    <label for="mail_encryption">Encryption</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Telegram Integration -->
                    <div class="config-section">
                        <div class="toggle-section" onclick="toggleSection('telegram-config')">
                            <h5><i class="fab fa-telegram text-primary"></i> Telegram Integration (Optional) <i class="fas fa-chevron-down float-end"></i></h5>
                        </div>
                        
                        <div id="telegram-config" class="collapsible-content">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="telegram_bot_token" name="telegram_bot_token" 
                                       value="{{ old('telegram_bot_token') }}" placeholder="123456789:ABCdefGhIJKlmNoPQRstUVwxyz">
                                <label for="telegram_bot_token">Telegram Bot Token</label>
                                <div class="form-text">
                                    Get your bot token from <a href="https://t.me/BotFather" target="_blank">@BotFather</a> on Telegram
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Gateway -->
                    <div class="config-section">
                        <div class="toggle-section" onclick="toggleSection('payment-config')">
                            <h5><i class="fas fa-credit-card text-primary"></i> Payment Gateway (Optional) <i class="fas fa-chevron-down float-end"></i></h5>
                        </div>
                        
                        <div id="payment-config" class="collapsible-content">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="paystack_public_key" name="paystack_public_key" 
                                       value="{{ old('paystack_public_key') }}" placeholder="pk_test_...">
                                <label for="paystack_public_key">Paystack Public Key</label>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="paystack_secret_key" name="paystack_secret_key" 
                                       value="{{ old('paystack_secret_key') }}" placeholder="sk_test_...">
                                <label for="paystack_secret_key">Paystack Secret Key</label>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Get your Paystack keys from your <a href="https://dashboard.paystack.com" target="_blank">Paystack Dashboard</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> 
                        <ul class="mb-0 mt-2">
                            <li>Make sure all URLs use HTTPS in production</li>
                            <li>Use strong passwords for security</li>
                            <li>Optional configurations can be set up later in admin panel</li>
                            <li>Test email configuration to ensure notifications work</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('install.database') }}" class="btn btn-outline-secondary">
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
                KayXchange Installation Step 4 of 5
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSection(sectionId) {
            const content = document.getElementById(sectionId);
            const icon = event.currentTarget.querySelector('.fa-chevron-down, .fa-chevron-up');
            
            if (content.classList.contains('show')) {
                content.classList.remove('show');
                icon.className = 'fas fa-chevron-down float-end';
            } else {
                content.classList.add('show');
                icon.className = 'fas fa-chevron-up float-end';
            }
        }
        
        function toggleMailFields() {
            const mailDriver = document.getElementById('mail_driver').value;
            const smtpFields = document.getElementById('smtp-fields');
            
            if (mailDriver === 'smtp') {
                smtpFields.style.display = 'block';
                // Make SMTP fields required
                document.getElementById('mail_host').required = true;
                document.getElementById('mail_port').required = true;
                document.getElementById('mail_username').required = true;
                document.getElementById('mail_password').required = true;
            } else {
                smtpFields.style.display = 'none';
                // Remove required attribute
                document.getElementById('mail_host').required = false;
                document.getElementById('mail_port').required = false;
                document.getElementById('mail_username').required = false;
                document.getElementById('mail_password').required = false;
            }
        }
        
        // Initialize mail fields visibility
        document.addEventListener('DOMContentLoaded', function() {
            toggleMailFields();
            
            // Auto-open email section
            toggleSection('email-config');
        });
    </script>
</body>
</html>