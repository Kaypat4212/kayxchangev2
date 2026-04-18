<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KayXchange Installation</title>
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
            padding: 40px;
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
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-list i {
            color: #28a745;
            margin-right: 10px;
            width: 20px;
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
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <i class="fas fa-rocket fa-3x mb-3"></i>
                <h1>Welcome to KayXchange Installation</h1>
                <p class="mb-0">Let's get your cryptocurrency exchange platform up and running!</p>
            </div>
            
            <div class="install-body">
                <div class="step-indicator">
                    <div class="step active">1</div>
                    <div class="step">2</div>
                    <div class="step">3</div>
                    <div class="step">4</div>
                    <div class="step">5</div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h3>What's Included</h3>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> Complete Trading Platform</li>
                            <li><i class="fas fa-check"></i> Admin Dashboard</li>
                            <li><i class="fas fa-check"></i> User Management</li>
                            <li><i class="fas fa-check"></i> Telegram Integration</li>
                            <li><i class="fas fa-check"></i> Real-time Analytics</li>
                            <li><i class="fas fa-check"></i> Payment Gateway Integration</li>
                            <li><i class="fas fa-check"></i> KYC Verification</li>
                            <li><i class="fas fa-check"></i> Multi-cryptocurrency Support</li>
                            <li><i class="fas fa-check"></i> Responsive Design</li>
                            <li><i class="fas fa-check"></i> Security Features</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h3>Installation Process</h3>
                        <div class="mb-4">
                            <h6><i class="fas fa-server text-primary"></i> Step 1: System Requirements</h6>
                            <p class="text-muted small">Check PHP version, extensions, and permissions</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-database text-primary"></i> Step 2: Database Setup</h6>
                            <p class="text-muted small">Configure your database connection</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-cog text-primary"></i> Step 3: Application Config</h6>
                            <p class="text-muted small">Set up admin account and integrations</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-magic text-primary"></i> Step 4: Installation</h6>
                            <p class="text-muted small">Run migrations and complete setup</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-check-circle text-success"></i> Step 5: Complete</h6>
                            <p class="text-muted small">Your platform is ready to use!</p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Important:</strong> Make sure you have a backup of your server before proceeding with the installation.
                    </div>
                    
                    <a href="<?php echo e(route('install.requirements')); ?>" class="btn btn-install btn-lg">
                        <i class="fas fa-arrow-right"></i> Start Installation
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-white">
                <i class="fas fa-shield-alt"></i>
                KayXchange v1.0 - Secure Cryptocurrency Exchange Platform
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\install\welcome.blade.php ENDPATH**/ ?>