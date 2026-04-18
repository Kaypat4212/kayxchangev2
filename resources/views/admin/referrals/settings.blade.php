@extends('adminnavlayout')

@push('styles')
<style>
:root {
    --kx-green: #00cc00;
    --kx-dark: #0d1117;
    --kx-card: #161b27;
    --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.08);
    --kx-text: #e4e8f0;
    --kx-muted: #7a8599;
}
.ref-wrap { max-width: 1200px; margin: 0 auto; }
.ref-head {
    background: linear-gradient(135deg, rgba(0,204,0,0.15), rgba(0,90,0,0.08));
    border: 1px solid rgba(0,204,0,0.24);
    border-radius: 14px;
    padding: 1.2rem 1.4rem;
    margin-bottom: 1.2rem;
}
.ref-head h4 { margin: 0 0 .35rem; color: #fff; font-weight: 700; }
.ref-head p { margin: 0; color: var(--kx-muted); font-size: .85rem; }
.ref-panel {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 14px;
    margin-bottom: 1.1rem;
}
.ref-panel .hd {
    border-bottom: 1px solid var(--kx-border);
    padding: .95rem 1.2rem;
    color: #fff;
    font-weight: 600;
    font-size: .9rem;
}
.ref-panel .bd { padding: 1.1rem 1.2rem; }
.ref-input,
.ref-select,
.ref-textarea {
    background: var(--kx-card2) !important;
    border: 1px solid var(--kx-border) !important;
    color: var(--kx-text) !important;
    border-radius: 10px !important;
    font-size: .85rem !important;
}
.ref-input:focus,
.ref-select:focus,
.ref-textarea:focus {
    border-color: rgba(0,204,0,0.45) !important;
    box-shadow: 0 0 0 3px rgba(0,204,0,0.11) !important;
}
.ref-select option { background: var(--kx-card2); color: var(--kx-text); }
.ref-muted { color: var(--kx-muted); font-size: .78rem; }
.ref-btn {
    border: none;
    border-radius: 9px;
    background: var(--kx-green);
    color: #052405;
    font-weight: 700;
    padding: .55rem 1rem;
    font-size: .82rem;
}
.ref-btn-outline {
    border: 1px solid var(--kx-border);
    border-radius: 9px;
    background: transparent;
    color: var(--kx-text);
    padding: .5rem .85rem;
    font-size: .8rem;
}
.ref-table { width: 100%; border-collapse: collapse; }
.ref-table th,
.ref-table td {
    border-bottom: 1px solid var(--kx-border);
    padding: .72rem .55rem;
    font-size: .78rem;
    color: var(--kx-text);
    vertical-align: top;
}
.ref-table th {
    color: var(--kx-muted);
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.ref-pill {
    border-radius: 999px;
    font-size: .68rem;
    padding: .18rem .55rem;
    display: inline-block;
    font-weight: 600;
}
.ref-pill.active { background: rgba(0,204,0,.13); color: var(--kx-green); }
.ref-pill.inactive { background: rgba(255,255,255,.12); color: var(--kx-muted); }
.ref-code { font-family: monospace; font-size: .88rem; color: #fff; }
@media (max-width: 900px) {
    .ref-scroll { overflow-x: auto; }
    .ref-table { min-width: 980px; }
}
</style>
@endpush

@section('content')
<div class="ref-wrap">
    <div class="ref-head">
        <h4><i class="bi bi-megaphone-fill me-2" style="color:var(--kx-green)"></i>Referral Settings</h4>
        <p>Set your global referral reward and manage ambassador or influencer special codes from one place.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="font-size:.82rem">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="ref-panel">
                <div class="hd">Default Reward Configuration</div>
                <div class="bd">
                    <form method="POST" action="{{ route('admin.referrals.defaults.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label text-light" style="font-size:.8rem">Referrer Reward (NGN)</label>
                            <input type="number" step="0.01" min="0" name="referral_reward_amount" value="{{ old('referral_reward_amount', $defaultReward) }}" class="form-control ref-input" required>
                            <div class="ref-muted mt-1">Default is 2000 per successful referral signup.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light" style="font-size:.8rem">Special Code Signup Bonus (NGN)</label>
                            <input type="number" step="0.01" min="0" name="special_referral_signup_bonus" value="{{ old('special_referral_signup_bonus', $defaultSignupBonus) }}" class="form-control ref-input" required>
                            <div class="ref-muted mt-1">Default bonus for new users who register with special ambassador or influencer codes.</div>
                        </div>
                        <button class="ref-btn" type="submit"><i class="bi bi-check-circle me-1"></i>Save Defaults</button>
                    </form>
                </div>
            </div>

            <div class="ref-panel">
                <div class="hd">Create Special Referral Code</div>
                <div class="bd">
                    <form method="POST" action="{{ route('admin.referrals.codes.store') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label text-light" style="font-size:.8rem">Code</label>
                            <input type="text" name="code" class="form-control ref-input" placeholder="e.g. AMB001" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-light" style="font-size:.8rem">Label</label>
                            <input type="text" name="label" class="form-control ref-input" placeholder="Optional display title">
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-light" style="font-size:.8rem">Category</label>
                            <select class="form-select ref-select" name="category" required>
                                <option value="ambassador">Ambassador</option>
                                <option value="influencer">Influencer</option>
                                <option value="partner">Partner</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-light" style="font-size:.8rem">Owner User (optional)</label>
                            <select class="form-select ref-select" name="owner_user_id">
                                <option value="">No linked owner</option>
                                @foreach($users as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label text-light" style="font-size:.8rem">Signup Bonus (NGN)</label>
                                <input type="number" step="0.01" min="0" name="signup_bonus" value="{{ $defaultSignupBonus }}" class="form-control ref-input" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-light" style="font-size:.8rem">Referrer Reward (NGN)</label>
                                <input type="number" step="0.01" min="0" name="referrer_reward" class="form-control ref-input" placeholder="Use default if empty">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light" style="font-size:.8rem">Notes</label>
                            <textarea class="form-control ref-textarea" rows="2" name="notes" placeholder="Optional note for this code"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" checked name="is_active" value="1" id="activeCreateCode">
                            <label class="form-check-label text-light" for="activeCreateCode" style="font-size:.8rem">Active code</label>
                        </div>
                        <button class="ref-btn" type="submit"><i class="bi bi-plus-circle me-1"></i>Create Code</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="ref-panel">
                <div class="hd">Special Codes</div>
                <div class="bd ref-scroll">
                    <table class="ref-table">
                        <thead>
                        <tr>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Owner</th>
                            <th>Reward</th>
                            <th>Signup Bonus</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($codes as $code)
                            <tr>
                                <td>
                                    <div class="ref-code">{{ $code->code }}</div>
                                    <div class="ref-muted">{{ $code->label ?: 'No label' }}</div>
                                </td>
                                <td>{{ ucfirst($code->category) }}</td>
                                <td>
                                    @if($code->owner)
                                        {{ $code->owner->name }}
                                        <div class="ref-muted">{{ $code->owner->email }}</div>
                                    @else
                                        <span class="ref-muted">No owner</span>
                                    @endif
                                </td>
                                <td>
                                    @if($code->referrer_reward !== null)
                                        ₦{{ number_format((float) $code->referrer_reward, 2) }}
                                    @else
                                        <span class="ref-muted">Default (₦{{ number_format($defaultReward, 2) }})</span>
                                    @endif
                                </td>
                                <td>₦{{ number_format((float) $code->signup_bonus, 2) }}</td>
                                <td>
                                    <span class="ref-pill {{ $code->is_active ? 'active' : 'inactive' }}">{{ $code->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td>
                                    <button class="ref-btn-outline" type="button" data-bs-toggle="collapse" data-bs-target="#edit-code-{{ $code->id }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.referrals.codes.destroy', $code) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ref-btn-outline" type="submit" onclick="return confirm('Delete this special referral code?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="collapse" id="edit-code-{{ $code->id }}">
                                <td colspan="7" style="background:rgba(255,255,255,0.02)">
                                    <form method="POST" action="{{ route('admin.referrals.codes.update', $code) }}" class="row g-2 align-items-end">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-md-2">
                                            <label class="form-label text-light" style="font-size:.75rem">Code</label>
                                            <input class="form-control ref-input" name="code" value="{{ $code->code }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-light" style="font-size:.75rem">Category</label>
                                            <select class="form-select ref-select" name="category" required>
                                                <option value="ambassador" {{ $code->category === 'ambassador' ? 'selected' : '' }}>Ambassador</option>
                                                <option value="influencer" {{ $code->category === 'influencer' ? 'selected' : '' }}>Influencer</option>
                                                <option value="partner" {{ $code->category === 'partner' ? 'selected' : '' }}>Partner</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-light" style="font-size:.75rem">Owner</label>
                                            <select class="form-select ref-select" name="owner_user_id">
                                                <option value="">None</option>
                                                @foreach($users as $owner)
                                                    <option value="{{ $owner->id }}" {{ (int) $code->owner_user_id === (int) $owner->id ? 'selected' : '' }}>{{ $owner->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-light" style="font-size:.75rem">Referrer Reward</label>
                                            <input class="form-control ref-input" type="number" step="0.01" min="0" name="referrer_reward" value="{{ $code->referrer_reward }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-light" style="font-size:.75rem">Signup Bonus</label>
                                            <input class="form-control ref-input" type="number" step="0.01" min="0" name="signup_bonus" value="{{ $code->signup_bonus }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-light" style="font-size:.75rem">Active</label>
                                            <select class="form-select ref-select" name="is_active">
                                                <option value="1" {{ $code->is_active ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ !$code->is_active ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label text-light" style="font-size:.75rem">Label</label>
                                            <input class="form-control ref-input" name="label" value="{{ $code->label }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label text-light" style="font-size:.75rem">Notes</label>
                                            <textarea class="form-control ref-textarea" name="notes" rows="2">{{ $code->notes }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <button class="ref-btn" type="submit"><i class="bi bi-save me-1"></i>Save Changes</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center" style="color:var(--kx-muted);padding:2rem 1rem">
                                    No special referral codes created yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
