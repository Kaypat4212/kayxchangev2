@extends('layout')

@push('body-class', 'telegram-settings')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fab fa-telegram-plane me-2"></i>
                        Telegram Notification Settings
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('settings.telegram.update') }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label for="telegram_username" class="form-label">
                                        <i class="fab fa-telegram-plane text-primary me-2"></i>
                                        Telegram Username
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input type="text" 
                                               class="form-control @error('telegram_username') is-invalid @enderror" 
                                               id="telegram_username" 
                                               name="telegram_username" 
                                               value="{{ old('telegram_username', Auth::user()->telegram_username) }}"
                                               placeholder="Enter your Telegram username">
                                    </div>
                                    @error('telegram_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enter your Telegram username without the @ symbol
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="telegram_notifications"
                                               name="telegram_notifications"
                                               value="1"
                                               {{ Auth::user()->telegram_notifications ? 'checked' : '' }}>
                                        <label class="form-check-label" for="telegram_notifications">
                                            <strong>Enable Telegram Notifications</strong>
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-bell me-1"></i>
                                        Receive trade confirmations and important updates via Telegram
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Save Settings
                                    </button>
                                    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Back to Settings
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-question-circle me-2"></i>
                                        How to Set Up Telegram Notifications
                                    </h5>
                                    
                                    <div class="step-guide">
                                        <div class="step mb-3">
                                            <div class="d-flex align-items-start">
                                                <span class="badge bg-primary rounded-pill me-3">1</span>
                                                <div>
                                                    <strong>Find Your Username</strong>
                                                    <p class="mb-0 text-muted">Open Telegram, go to Settings, and note your username (without @)</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step mb-3">
                                            <div class="d-flex align-items-start">
                                                <span class="badge bg-primary rounded-pill me-3">2</span>
                                                <div>
                                                    <strong>Start Our Bot</strong>
                                                    <p class="mb-1 text-muted">
                                                        Click the button below or scan QR code on mobile:
                                                    </p>
                                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                                        <a href="https://t.me/TradewithkayxchangeBOT" 
                                                           target="_blank" 
                                                           class="btn btn-telegram btn-sm">
                                                            <i class="fab fa-telegram-plane me-2"></i>
                                                            Start @TradewithkayxchangeBOT
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-outline-info btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#qrCodeModal">
                                                            <i class="fas fa-qrcode me-2"></i>
                                                            QR Code
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step mb-3">
                                            <div class="d-flex align-items-start">
                                                <span class="badge bg-primary rounded-pill me-3">3</span>
                                                <div>
                                                    <strong>Verify Account</strong>
                                                    <p class="mb-0 text-muted">
                                                        Send your account email to the bot for verification, or simply enter your Telegram username here and save settings
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step mb-3">
                                            <div class="d-flex align-items-start">
                                                <span class="badge bg-success rounded-pill me-3">4</span>
                                                <div>
                                                    <strong>Enable Notifications</strong>
                                                    <p class="mb-0 text-muted">Save your settings here and start receiving notifications!</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-telegram mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Two Ways to Connect:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li><strong>Method 1:</strong> Enter your username here and save settings</li>
                                            <li><strong>Method 2:</strong> Chat with our bot and verify with your email</li>
                                        </ul>
                                        <small class="text-muted d-block mt-2">Both methods work perfectly - choose what's easier for you!</small>
                                    </div>
                                    
                                    <div class="alert alert-info mt-2">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        <strong>Privacy:</strong> We only use your Telegram username to send you transaction notifications. Your username is never shared with third parties.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Status Card -->
            <div class="card shadow mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Notification Settings Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="status-item">
                                <div class="status-icon mb-2">
                                    @if(Auth::user()->telegram_username)
                                        <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger" style="font-size: 2rem;"></i>
                                    @endif
                                </div>
                                <h6>Telegram Username</h6>
                                <p class="text-muted mb-0">
                                    {{ Auth::user()->telegram_username ? '@' . Auth::user()->telegram_username : 'Not set' }}
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="status-item">
                                <div class="status-icon mb-2">
                                    @if(Auth::user()->telegram_notifications)
                                        <i class="fas fa-bell text-success" style="font-size: 2rem;"></i>
                                    @else
                                        <i class="fas fa-bell-slash text-warning" style="font-size: 2rem;"></i>
                                    @endif
                                </div>
                                <h6>Notifications</h6>
                                <p class="text-muted mb-0">
                                    {{ Auth::user()->telegram_notifications ? 'Enabled' : 'Disabled' }}
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="status-item">
                                <div class="status-icon mb-2">
                                    @if(Auth::user()->telegram_chat_id && Auth::user()->telegram_verified && Auth::user()->telegram_notifications)
                                        <i class="fas fa-rocket text-success" style="font-size: 2rem;"></i>
                                    @elseif(Auth::user()->telegram_username && Auth::user()->telegram_notifications)
                                        <i class="fas fa-clock text-warning" style="font-size: 2rem;"></i>
                                    @else
                                        <i class="fas fa-cog text-muted" style="font-size: 2rem;"></i>
                                    @endif
                                </div>
                                <h6>Setup Status</h6>
                                <p class="text-muted mb-0">
                                    @if(Auth::user()->telegram_chat_id && Auth::user()->telegram_verified && Auth::user()->telegram_notifications)
                                        <span class="text-success">✅ Fully connected</span>
                                    @elseif(Auth::user()->telegram_username && Auth::user()->telegram_notifications)
                                        <span class="text-warning">⏳ Bot verification needed</span>
                                    @elseif(Auth::user()->telegram_username)
                                        <span class="text-info">📝 Enable notifications</span>
                                    @else
                                        <span class="text-muted">⚙️ Setup required</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Notification Card -->
            @if(Auth::user()->telegram_username && Auth::user()->telegram_notifications)
            <div class="card shadow mt-4">
                <div class="card-body text-center">
                    @if(Auth::user()->telegram_chat_id && Auth::user()->telegram_verified)
                        <h6 class="card-title text-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Test Your Telegram Notifications
                        </h6>
                        <p class="card-text text-muted">Send a test message to verify your settings are working correctly</p>
                        <form method="POST" action="{{ route('settings.telegram.test') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Test Notification
                            </button>
                        </form>
                    @else
                        <h6 class="card-title text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Complete Setup to Test Notifications
                        </h6>
                        <p class="card-text text-muted">
                            You need to verify your account with our bot to receive notifications
                        </p>
                        <div class="alert alert-warning">
                            <strong>Next Step:</strong> Click the Telegram button above to start @TradewithkayxchangeBOT and send your email ({{ Auth::user()->email }}) for verification.
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .step-guide .step {
        position: relative;
    }
    
    .step-guide .step:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 15px;
        top: 35px;
        bottom: -15px;
        width: 2px;
        background: #dee2e6;
    }
    
    .status-item {
        padding: 1rem 0;
    }
    
    .card {
        border: none;
        border-radius: 15px;
    }
    
    .card-header {
        border-radius: 15px 15px 0 0 !important;
    }
    
    .form-control:focus {
        border-color: #00c851;
        box-shadow: 0 0 0 0.2rem rgba(0, 200, 81, 0.25);
    }
    
    .btn-primary {
        background-color: #00c851;
        border-color: #00c851;
    }
    
    .btn-primary:hover {
        background-color: #007e33;
        border-color: #007e33;
    }
    
    .btn-telegram {
        background-color: #0088cc;
        color: white;
        border: none;
        border-radius: 20px;
        padding: 8px 16px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .btn-telegram:hover {
        background-color: #006699;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 136, 204, 0.3);
    }
    
    .alert-telegram {
        background-color: #e3f2fd;
        border-color: #0088cc;
        color: #1565c0;
    }
    
    .bg-telegram {
        background-color: #0088cc;
    }
</style>

<!-- QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-telegram text-white">
                <h5 class="modal-title" id="qrCodeModalLabel">
                    <i class="fab fa-telegram-plane me-2"></i>
                    Scan to Start Bot
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-3">Scan this QR code with your phone's camera to open the bot directly in Telegram:</p>
                <div id="qrcode" class="d-flex justify-content-center mb-3"></div>
                <div class="alert alert-info">
                    <i class="fas fa-mobile-alt me-2"></i>
                    <strong>Mobile tip:</strong> Most phones can scan QR codes directly with the camera app
                </div>
                <p class="text-muted small">
                    Or manually search for <code>@TradewithkayxchangeBOT</code> in Telegram
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" class="btn btn-telegram">
                    <i class="fab fa-telegram-plane me-2"></i>
                    Open in Telegram
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
    // Generate QR code when modal is shown
    document.getElementById('qrCodeModal').addEventListener('shown.bs.modal', function () {
        const qrCodeDiv = document.getElementById('qrcode');
        qrCodeDiv.innerHTML = ''; // Clear any existing QR code
        
        QRCode.toCanvas(document.createElement('canvas'), 'https://t.me/TradewithkayxchangeBOT', {
            width: 200,
            height: 200,
            colorDark: '#0088cc',
            colorLight: '#ffffff',
            margin: 2
        }, function (error, canvas) {
            if (error) {
                console.error(error);
                qrCodeDiv.innerHTML = '<p class="text-danger">Failed to generate QR code</p>';
            } else {
                qrCodeDiv.appendChild(canvas);
            }
        });
    });

    // Real-time status checking
    let statusCheckInterval;
    
    function checkTelegramStatus() {
        fetch('/api/user/telegram-status', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Authorization': 'Bearer ' + document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.verified && !document.querySelector('.status-verified')) {
                // User just got verified, refresh page to show updated status
                location.reload();
            }
        })
        .catch(error => console.log('Status check error:', error));
    }

    // Check status every 5 seconds if not verified
    @if(!Auth::user()->telegram_verified)
        statusCheckInterval = setInterval(checkTelegramStatus, 5000);
    @endif

    // Stop checking when page unloads
    window.addEventListener('beforeunload', function() {
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
        }
    });
</script>
@endsection