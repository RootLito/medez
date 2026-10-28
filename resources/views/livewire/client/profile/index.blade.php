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
    'last_name' => fn() => auth('client')->user()?->last_name ?? '',
    'username' => fn() => auth('client')->user()?->username ?? '',
    'address' => fn() => auth('client')->user()?->address ?? '',
    'photo' => null,
    'current_photo_url' => fn() => auth('client')->user()?->profile_picture ? asset('storage/' . auth('client')->user()->profile_picture) : null,

    // Security fields
    'new_password' => '',
    'new_password_confirmation' => '',
]);

$handleSaveAttempt = function () {
    // If user entered a new password, trigger confirmation modal first
    if (! empty($this->new_password)) {
        $this->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        Flux::modal('security-confirmation-modal')->show();
        return;
    }

    // Otherwise, save profile directly
    $this->saveProfile();
};

$saveProfile = function () {
    $rules = [
        'first_name' => ['required', 'string', 'max:255'],
        'middle_name' => ['nullable', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
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

    // Reset temporary state & refresh photo URL
    $updatedClient = $client->fresh();
    $this->photo = null;
    $this->current_photo_url = $updatedClient->profile_picture ? asset('storage/' . $updatedClient->profile_picture) : null;

    Flux::toast(
        text: 'Profile updated successfully!',
        variant: 'success'
    );
};

?>

<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <flux:heading size="xl" level="1">Account Settings</flux:heading>
        <flux:subheading>Manage your personal profile, contact information, and account security.</flux:subheading>
    </div>

    <form wire:submit="handleSaveAttempt" class="space-y-6">
        <!-- 1. Interactive Profile Header & Avatar Card -->
        <flux:card class="p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">

                <!-- Avatar with Camera Overlay Trigger -->
                <div class="relative group shrink-0">
                    <input type="file" x-ref="photoInput" wire:model="photo" class="hidden"
                        accept="image/png, image/jpeg, image/webp" />

                    <div
                        class="size-24 rounded-full overflow-hidden ring-4 ring-zinc-100 dark:ring-zinc-800 shadow-md bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                        @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" class="size-full object-cover" alt="Avatar Preview" />
                        @elseif ($current_photo_url)
                        <img src="{{ $current_photo_url }}" class="size-full object-cover" alt="Profile" />
                        @else
                        <span class="text-2xl font-bold text-zinc-500">
                            {{ strtoupper(substr($first_name ?: 'C', 0, 1)) }}
                        </span>
                        @endif
                    </div>

                    <!-- Tap/Click to Change Camera Badge Button -->
                    <button type="button" x-on:click="$refs.photoInput.click()"
                        class="absolute bottom-0 right-0 p-2 rounded-full bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-lg hover:scale-110 active:scale-95 transition"
                        title="Upload new picture">
                        <flux:icon.camera class="size-4" />
                    </button>

                    <!-- Upload Loading Overlay Spinner -->
                    <div wire:loading wire:target="photo"
                        class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center">
                        <flux:icon.arrow-path class="size-6 text-white animate-spin" />
                    </div>
                </div>

                <!-- Profile Info & Upload Metadata -->
                <div class="flex-1 text-center sm:text-left space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                {{ trim($first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name) ?:
                                'Client User' }}
                            </h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">@<span>{{ $username }}</span></p>
                        </div>
                        <div>
                            <flux:badge size="sm" color="zinc">Client Account</flux:badge>
                        </div>
                    </div>

                    <p class="text-xs text-zinc-500 dark:text-zinc-400 pt-1">
                        Allowed formats: JPG, PNG, or WEBP (Max: 2MB).
                    </p>

                    @if ($photo)
                    <div class="inline-flex items-center gap-2 pt-1">
                        <flux:badge size="sm" color="green">New photo selected</flux:badge>
                        <button type="button" wire:click="$set('photo', null)"
                            class="text-xs text-red-500 hover:underline">
                            Remove
                        </button>
                    </div>
                    @endif

                    @error('photo')
                    <p class="text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </flux:card>

        <!-- 2. Personal Information Section -->
        <flux:card class="space-y-4 p-5 sm:p-6">
            <div class="flex items-center gap-2 border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                <flux:icon.user class="size-5 text-zinc-500" />
                <div>
                    <flux:heading size="lg">Personal Details</flux:heading>
                    <flux:subheading class="text-xs">Your official applicant identification name and contact address.
                    </flux:subheading>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                <flux:field>
                    <flux:label>First Name <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="first_name" placeholder="First name" required />
                    <flux:error name="first_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Middle Name</flux:label>
                    <flux:input wire:model="middle_name" placeholder="Middle name (optional)" />
                    <flux:error name="middle_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Last Name <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="last_name" placeholder="Last name" required />
                    <flux:error name="last_name" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Complete Residential / Contact Address</flux:label>
                <flux:textarea wire:model="address" rows="3" placeholder="Barangay, Street, Municipality, Province" />
                <flux:error name="address" />
            </flux:field>
        </flux:card>

        <!-- 3. Security Credentials Section -->
        <flux:card class="space-y-4 p-5 sm:p-6">
            <div class="flex items-center gap-2 border-b border-zinc-200/80 dark:border-zinc-800 pb-3">
                <flux:icon.shield-check class="size-5 text-zinc-500" />
                <div>
                    <flux:heading size="lg">Account & Security</flux:heading>
                    <flux:subheading class="text-xs">Manage your sign-in identifier and change your password.
                    </flux:subheading>
                </div>
            </div>

            <!-- Username Notice -->
            <div class="space-y-1">
                <flux:field>
                    <flux:label class="flex items-center justify-between">
                        <span>Username</span>
                        <span class="text-[11px] text-zinc-400 font-normal">Permanent ID</span>
                    </flux:label>
                    <flux:input wire:model="username" icon="lock-closed" disabled
                        class="bg-zinc-50 dark:bg-zinc-900/50 cursor-not-allowed" />
                </flux:field>
                <p class="text-[11px] text-zinc-400">Your unique login username cannot be changed.</p>
            </div>

            <!-- Password Change Warning Callout -->
            <div
                class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5">
                <flux:icon.information-circle class="size-4 shrink-0 mt-0.5 text-amber-600 dark:text-amber-400" />
                <span>
                    Leave password fields blank if you do not wish to change it. Changing your password will
                    automatically log you out across all sessions.
                </span>
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <flux:field>
                    <flux:label>New Password</flux:label>
                    <flux:input type="password" wire:model="new_password" placeholder="At least 8 characters"
                        viewable />
                    <flux:error name="new_password" />
                </flux:field>

                <flux:field>
                    <flux:label>Confirm New Password</flux:label>
                    <flux:input type="password" wire:model="new_password_confirmation" placeholder="Re-enter password"
                        viewable />
                </flux:field>
            </div>
        </flux:card>

        <!-- Save Button Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <flux:button type="submit" variant="primary" icon="check" class="w-full sm:w-auto"
                wire:loading.attr="disabled">
                Save Profile Changes
            </flux:button>
        </div>
    </form>

    <!-- Logout Confirmation Modal -->
    <flux:modal name="security-confirmation-modal" class="min-w-[22rem] max-w-sm" backdrop="blur">
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <flux:icon.exclamation-triangle class="size-6" />
                </div>
                <div>
                    <flux:heading size="lg">Update Password?</flux:heading>
                    <flux:subheading class="text-xs">Security authentication update</flux:subheading>
                </div>
            </div>

            <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">
                Updating your password will immediately sign you out. You must log back in using your new credentials to
                continue.
            </flux:text>

            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="subtle">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="saveProfile">
                    Yes, Change & Sign Out
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>