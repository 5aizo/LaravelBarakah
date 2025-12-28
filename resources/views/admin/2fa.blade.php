@extends('layouts.master')

@section('content')
<div class="container mt-5" style="max-width: 700px;">

    <h2 class="mb-4">Two-Factor Authentication (2FA)</h2>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if (auth()->user()->two_factor_secret)

        <div class="alert alert-success">
            ✅ Two-factor authentication is currently <strong>ENABLED</strong>.
        </div>

        <p><strong>Secret Key:</strong></p>
        <div class="bg-light p-2 mb-3 rounded font-monospace">
            {{ decrypt(auth()->user()->two_factor_secret) }}
        </div>

        <p class="text-muted">
            Add this key to Google Authenticator using “Enter a setup key”.
        </p>

        <form method="POST" action="{{ route('admin.2fa.disable') }}">
            @csrf
            <button class="btn btn-warning mb-4">Disable 2FA</button>
        </form>

        <h5>Recovery Codes</h5>
        <ul class="list-group mb-3">
            @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes)) as $code)
                <li class="list-group-item font-monospace">{{ $code }}</li>
            @endforeach
        </ul>

    @else

        <div class="alert alert-warning">
            ❌ Two-factor authentication is currently <strong>DISABLED</strong>.
        </div>

        <form method="POST" action="{{ route('admin.2fa.enable') }}">
            @csrf
            <button class="btn btn-success">Enable 2FA</button>
        </form>

    @endif
</div>
@endsection
