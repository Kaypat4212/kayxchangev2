<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Installation - KayXchange</title>
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
        
        .installation-steps {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .step-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .step-item:last-child {
            border-bottom: none;
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #6c757d;
        }
        
        .step-icon.completed {
            background: #28a745;
            color: white;
        }
        
        .step-icon.processing {
            background: #007bff;
            color: white;
        }
        
        .step-icon.error {
            background: #dc3545;
            color: white;
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
        
        .progress {
            height: 10px;
            border-radius: 50px;
            margin: 20px 0;
        }
        
        .progress-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50px;
            transition: width 0.5s ease;
        }
        
        .installation-log {
            background: #212529;
            color: #28a745;
            border-radius: 10px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 20px;
            display: none;
        }
        
        .log-line {
            margin-bottom: 5px;
        }
        
        .log-success {
            color: #28a745;
        }
        
        .log-error {
            color: #dc3545;
        }
        
        .log-info {
            color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <i class="fas fa-magic fa-2x mb-3"></i>
                <h2>Final Installation</h2>
                <p class="mb-0">Ready to install your KayXchange platform</p>
            </div>
            
            <div class="install-body">
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step completed">2</div>
                    <div class="step completed">3</div>
                    <div class="step completed">4</div>
                    <div class="step active">5</div>
                </div>
                
                <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Installation Error!</strong>
                    <ul class="mb-0 mt-2">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Ready to Install!</strong>
                    <p class="mb-0 mt-2">All configurations have been saved. Click the button below to complete the installation process.</p>
                </div>
                
                <div class="installation-steps">
                    <h5 class="mb-3">Installation Process</h5>
                    
                    <div class="step-item" id="step-migrations">
                        <div class="step-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div>
                            <strong>Database Migration</strong>
                            <div class="text-muted">Create database tables and structure</div>
                        </div>
                    </div>
                    
                    <div class="step-item" id="step-storage">
                        <div class="step-icon">
                            <i class="fas fa-link"></i>
                        </div>
                        <div>
                            <strong>Storage Setup</strong>
                            <div class="text-muted">Create storage symlinks and directories</div>
                        </div>
                    </div>
                    
                    <div class="step-item" id="step-admin">
                        <div class="step-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <strong>Admin Account</strong>
                            <div class="text-muted">Create administrator account</div>
                        </div>
                    </div>
                    
                    <div class="step-item" id="step-cache">
                        <div class="step-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div>
                            <strong>Cache Optimization</strong>
                            <div class="text-muted">Clear and rebuild application cache</div>
                        </div>
                    </div>
                    
                    <div class="step-item" id="step-finalize">
                        <div class="step-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <strong>Finalization</strong>
                            <div class="text-muted">Complete installation and cleanup</div>
                        </div>
                    </div>
                </div>
                
                <div class="progress" id="installProgress" style="display: none;">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                
                <form method="POST" action="<?php echo e(route('install.install')); ?>" id="installForm">
                    <?php echo csrf_field(); ?>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="seed_database" name="seed_database" value="1">
                        <label class="form-check-label" for="seed_database">
                            <strong>Seed Database with Sample Data</strong>
                            <div class="text-muted small">Add sample cryptocurrency rates and demo data (recommended for testing)</div>
                        </label>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="agree_terms" required>
                        <label class="form-check-label" for="agree_terms">
                            I understand that this will create database tables and modify server files
                        </label>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo e(route('install.application')); ?>" class="btn btn-outline-secondary" id="backBtn">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        
                        <button type="button" class="btn btn-install btn-lg" onclick="startInstallation()" id="installBtn">
                            <i class="fas fa-rocket"></i> Install KayXchange
                        </button>
                    </div>
                </form>
                
                <div class="installation-log" id="installLog">
                    <div class="log-line log-info">[INFO] Installation started...</div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-white">
                <i class="fas fa-shield-alt"></i>
                KayXchange Installation Step 5 of 5
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function startInstallation() {
            const installBtn = document.getElementById('installBtn');
            const backBtn = document.getElementById('backBtn');
            const progress = document.getElementById('installProgress');
            const progressBar = progress.querySelector('.progress-bar');
            const log = document.getElementById('installLog');
            
            // Disable buttons and show progress
            installBtn.disabled = true;
            installBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Installing...';
            backBtn.style.display = 'none';
            progress.style.display = 'block';
            log.style.display = 'block';
            
            // Simulate installation steps
            const steps = [
                { id: 'step-migrations', name: 'Database Migration', progress: 20 },
                { id: 'step-storage', name: 'Storage Setup', progress: 40 },
                { id: 'step-admin', name: 'Admin Account', progress: 60 },
                { id: 'step-cache', name: 'Cache Optimization', progress: 80 },
                { id: 'step-finalize', name: 'Finalization', progress: 100 }
            ];
            
            let currentStep = 0;
            
            function processStep() {
                if (currentStep < steps.length) {
                    const step = steps[currentStep];
                    const stepElement = document.getElementById(step.id);
                    const stepIcon = stepElement.querySelector('.step-icon');
                    
                    // Mark as processing
                    stepIcon.classList.add('processing');
                    addLogLine(`[INFO] Processing ${step.name}...`, 'log-info');
                    
                    // Update progress
                    progressBar.style.width = step.progress + '%';
                    
                    setTimeout(() => {
                        // Mark as completed
                        stepIcon.classList.remove('processing');
                        stepIcon.classList.add('completed');
                        stepIcon.innerHTML = '<i class="fas fa-check"></i>';
                        addLogLine(`[SUCCESS] ${step.name} completed`, 'log-success');
                        
                        currentStep++;
                        processStep();
                    }, 1500 + Math.random() * 1000); // Random delay for realism
                } else {
                    // Installation complete
                    setTimeout(() => {
                        document.getElementById('installForm').submit();
                    }, 1000);
                }
            }
            
            // Start processing
            setTimeout(processStep, 500);
        }
        
        function addLogLine(message, className = '') {
            const log = document.getElementById('installLog');
            const line = document.createElement('div');
            line.className = `log-line ${className}`;
            line.textContent = `${new Date().toLocaleTimeString()} ${message}`;
            log.appendChild(line);
            log.scrollTop = log.scrollHeight;
        }
    </script>
</body>
</html><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\install\final.blade.php ENDPATH**/ ?>