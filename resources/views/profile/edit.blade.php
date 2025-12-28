@extends('layouts.master')

@section('content')
<div class="container mt-5 mb-5" style="max-width: 700px;">

    <h2 class="mb-4">Profile Information</h2>
    <p class="text-muted">
        Update your account's profile information and email address.
    </p>

    {{-- Success message --}}
    @if (session('status') === 'profile-updated')
        <div class="alert alert-success">
            Profile updated successfully.
        </div>
    @endif

    {{-- Update Profile --}}
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   value="{{ old('name', auth()->user()->name) }}"
                   required>
        </div>

        <div class="mb-4">
            <label class="form-label">Email</label>
            <input type="email"
                   name="email"
                   class="form-control"
                   value="{{ old('email', auth()->user()->email) }}"
                   required>
        </div>

        <button class="btn btn-success">Save</button>
    </form>

    <hr class="my-5">

    {{-- UPDATE PASSWORD --}}
    <h4>Update Password</h4>
    <p class="text-muted">
        Ensure your account is using a long, random password to stay secure.
    </p>

    @if (session('status') === 'password-updated')
        <div class="alert alert-success">
            Password updated successfully.
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button class="btn btn-success">Save</button>
    </form>

    <hr class="my-5">

    {{-- DELETE ACCOUNT --}}
    <h4 class="text-danger">Delete Account</h4>
    <p class="text-muted">
        Once your account is deleted, all of its resources and data will be permanently deleted.
        Before deleting your account, please download any data or information that you wish to retain.
    </p>

    <!-- Trigger Modal -->
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
        Delete Account
    </button>

</div>

<!-- DELETE ACCOUNT MODAL -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content">
        @csrf
        @method('DELETE')

        <div class="modal-header">
            <h5 class="modal-title text-danger">Are you sure you want to delete your account?</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
            <p>
                Once your account is deleted, all of its resources and data will be permanently deleted.
                Please enter your password to confirm.
            </p>

            <input type="password"
                   name="password"
                   class="form-control"
                   placeholder="Enter your password"
                   required>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Cancel
            </button>
            <button type="submit" class="btn btn-danger">
                Delete Account
            </button>
        </div>
    </form>
  </div>
</div>
@endsection
