<?php

use function Livewire\Volt\{layout, title, state, usesFileUploads};
use App\Models\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

usesFileUploads();

layout('components.layouts.client');
title('Profile - Client Portal');

state([
    'first_name' => fn() => auth('client')->user()?->first_name ?? '',
    'middle_name' => fn() => auth('client')->user()?->middle_name ?? '',
    'username' => fn() => auth('client')->user()?->username ?? '',
    'address' => fn() => auth('client')->user()?->address ?? '',
    'photo' => null,
    'current_photo_url' => fn() => auth('client')->user()?->profile_picture ? asset('storage/' . auth('client')->user()->profile_picture) : null,

    // Security fields
    'new_password' => '',
    'new_password_confirmation' => '',
]);

$handleSaveAttempt = function () {
    // If the user entered a new password, trigger the confirmation modal first
    if (! empty($this->new_password)) {
        $this->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        Flux::modal('security-confirmation-modal')->show();
        return;
    }

    // Otherwise, save standard profile info directly
    $this->saveProfile();
};

$saveProfile = function () {
    $rules = [
        'first_name' => ['required', 'string', 'max:255'],
        'middle_name' => ['nullable', 'string', 'max:255'],
        'address' => ['nullable', 'string'],
        'photo' => ['nullable', 'image', 'max:2048'],
    ];

    $requiresLogout = false;

    if (! empty($this->new_password)) {
        $rules['new_password'] = ['required', 'string', 'min:8', 'confirmed'];
        $requiresLogout = true;
    }

    $validated = $this->validate($rules);

    /** @var Client|null $client */
    $client = Client::find(auth('client')->id());

    if (! $client) {
        return redirect()->route('client.login');
    }

    // Handle photo upload if present
    if ($this->photo) {
        if ($client->profile_picture && Storage::disk('public')->exists($client->profile_picture)) {
            Storage::disk('public')->delete($client->profile_picture);
        }

        $path = $this->photo->store('profile-pictures', 'public');
        $validated['profile_picture'] = $path;
    }

    // Handle password change if provided
    if (! empty($this->new_password)) {
        $validated['password'] = Hash::make($this->new_password);
    }

    unset($validated['photo'], $validated['new_password'], $validated['new_password_confirmation']);

    // Update database
    $client->update($validated);

    // If password was changed, invalidate session and redirect to login
    if ($requiresLogout) {
        Auth::guard('client')->logout();
        session()->invalidate();
        session()->regenerateToken();

        session()->flash('status', 'Your security details were updated successfully. Please log in with your new credentials.');

        return redirect()->route('client.login');
    }

    // Reset temporary state & refresh photo URL using asset()
    $updatedClient = $client->fresh();
    $this->photo = null;
    $this->current_photo_url = $updatedClient->profile_picture ? asset('storage/' . $updatedClient->profile_picture) : null;

    Flux::toast(
        text: 'Profile updated successfully!',
        variant: 'success'
    );
};

?>

<div class="max-w-2xl space-y-6">
    <div>
        <flux:heading size="xl" level="1">Account Profile</flux:heading>
        <flux:subheading>Update your personal account information and security credentials.</flux:subheading>
    </div>

    <form wire:submit="handleSaveAttempt" class="space-y-6">
        <!-- 1. Profile Picture Section -->
        <flux:card class="space-y-4">
            <flux:heading size="lg">Profile Picture</flux:heading>

            <div class="flex items-center gap-6">
                <!-- Avatar Preview -->
                <div class="relative shrink-0">
                    @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" class="size-20 rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700" alt="Preview" />
                    @elseif ($current_photo_url)
                    <img src="{{ $current_photo_url }}" class="size-20 rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700" alt="Profile" />
                    @else
                    <div class="size-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 font-semibold text-xl ring-2 ring-zinc-200 dark:ring-zinc-700">
                        {{ strtoupper(substr($first_name ?? 'C', 0, 1)) }}
                    </div>
                    @endif

                    <div wire:loading wire:target="photo" class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center">
                        <flux:icon name="arrow-path" class="size-6 text-white animate-spin" />
                    </div>
                </div>

                <!-- Upload Controls -->
                <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-3">
                        <input
                            type="file"
                            x-ref="photoInput"
                            wire:model="photo"
                            class="hidden"
                            accept="image/png, image/jpeg, image/webp" />

                        <flux:button
                            type="button"
                            variant="subtle"
                            size="sm"
                            icon="arrow-up-tray"
                            x-on:click="$refs.photoInput.click()">
                            {{ $photo || $current_photo_url ? 'Change Photo' : 'Upload Photo' }}
                        </flux:button>

                        @if ($photo)
                        <flux:button type="button" variant="ghost" size="sm" wire:click="$set('photo', null)">
                            Cancel
                        </flux:button>
                        @endif
                    </div>

                    <flux:text class="text-xs text-zinc-500">JPG, PNG, or WEBP up to 2MB.</flux:text>

                    @error('photo')
                    <div class="text-xs text-red-500 font-medium">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </flux:card>

        <!-- 2. Personal Details -->
        <flux:card class="space-y-4">
            <flux:heading size="lg">Personal Details</flux:heading>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input label="First Name" wire:model="first_name" required />
                <flux:input label="Middle Name" wire:model="middle_name" />
            </div>

            <flux:textarea label="Address" wire:model="address" rows="3" />
        </flux:card>

        <!-- 3. Security (Username & Password) -->
        <flux:card class="space-y-4">
            <div>
                <flux:heading size="lg">Security Credentials</flux:heading>
                <flux:subheading>Update your login username or set a new password.</flux:subheading>
            </div>

            <flux:input label="Username" wire:model="username" disabled />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input
                    type="password"
                    label="New Password"
                    wire:model="new_password"
                    placeholder="Leave blank to keep current"
                    viewable />

                <flux:input
                    type="password"
                    label="Confirm New Password"
                    wire:model="new_password_confirmation"
                    placeholder="Re-enter new password"
                    viewable />
            </div>
        </flux:card>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="check">
                Save Changes
            </flux:button>
        </div>
    </form>

    <!-- Logout Confirmation Modal -->
    <flux:modal name="security-confirmation-modal" class="min-w-[22rem]" backdrop="blur">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Update Security Credentials?</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to change your password?<br>
                    <strong class="text-amber-600 dark:text-amber-400">This action will log you out immediately</strong> and require you to sign in again.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="saveProfile">
                    Yes, Update & Log Out
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>