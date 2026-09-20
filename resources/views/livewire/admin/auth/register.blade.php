<?php

use function Livewire\Volt\{layout, title, state, rules};
use App\Models\Admin;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Flux\Flux;

layout('components.layouts.admin');
title('Register Staff Account - MPDC Admin');

state([
    'full_name' => '',
    'position' => '',
    'username' => '',
    'password' => '',
    'password_confirmation' => '',
    'role' => 'mpdc_staff',
    'permissions' => [
        'applications.view',
        'applications.encode_walkin',
        'applications.verify',
        'applications.return',
        'applications.update_status',
        'geospatial.view_map',
        'inspections.schedule',
    ],
]);

rules([
    'full_name' => ['required', 'string', 'max:255'],
    'position' => ['required', 'string', 'max:255'],
    'username' => ['required', 'string', 'max:255', 'unique:admins,username'],
    'password' => ['required', 'string', 'confirmed', Password::defaults()],
    'role' => ['required', 'in:admin,mpdc_staff,inspector'],
    'permissions' => ['array'],
]);

$register = function () {
    $validated = $this->validate();

    $admin = Admin::create([
        'full_name' => $validated['full_name'],
        'position' => $validated['position'],
        'username' => $validated['username'],
        'password' => Hash::make($validated['password']),
        'role' => $validated['role'],
        'permissions' => $validated['role'] === 'admin' ? [] : $validated['permissions'],
        'is_active' => true,
    ]);

    // Log account creation activity
    ActivityLog::create([
        'admin_id' => auth('admin')->id(),
        'action' => 'CREATE_USER',
        'module' => 'User Management',
        'description' => "Created new staff account: {$admin->username} ({$admin->position})",
        'ip_address' => request()->ip(),
    ]);

    Flux::toast(text: 'Staff account registered successfully!', variant: 'success');

    return redirect()->route('admin.users.index');
};

?>

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <flux:heading size="xl" level="1">Register Staff Account</flux:heading>
        <flux:subheading>Create credentials and assign initial permissions for new MPDC personnel.</flux:subheading>
    </div>

    <flux:card class="p-6">
        <form wire:submit="register" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input label="Full Name" wire:model="full_name" placeholder="e.g. Juan Dela Cruz" required />
                <flux:input label="Position Title" wire:model="position" placeholder="e.g. Zoning Officer" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input label="Username" wire:model="username" placeholder="e.g. jdelacruz" required />
                <flux:select label="System Role" wire:model.live="role">
                    <flux:select.option value="mpdc_staff">MPDC Staff</flux:select.option>
                    <flux:select.option value="inspector">Field Inspector</flux:select.option>
                    <flux:select.option value="admin">System Administrator</flux:select.option>
                </flux:select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input type="password" label="Password" wire:model="password" placeholder="••••••••" required viewable />
                <flux:input type="password" label="Confirm Password" wire:model="password_confirmation" placeholder="••••••••" required viewable />
            </div>

            @if($role !== 'admin')
                <div class="space-y-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:heading size="sm">Initial Permissions</flux:heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-2 p-2 rounded border border-zinc-200 dark:border-zinc-700">
                            <input type="checkbox" value="applications.view" wire:model="permissions" class="rounded border-zinc-300 text-indigo-600" />
                            <span>View Applications</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded border border-zinc-200 dark:border-zinc-700">
                            <input type="checkbox" value="applications.encode_walkin" wire:model="permissions" class="rounded border-zinc-300 text-indigo-600" />
                            <span>Encode Walk-ins</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded border border-zinc-200 dark:border-zinc-700">
                            <input type="checkbox" value="applications.verify" wire:model="permissions" class="rounded border-zinc-300 text-indigo-600" />
                            <span>Verify Documents</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded border border-zinc-200 dark:border-zinc-700">
                            <input type="checkbox" value="geospatial.view_map" wire:model="permissions" class="rounded border-zinc-300 text-indigo-600" />
                            <span>Access GIS Map</span>
                        </label>
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button href="{{ route('admin.users.index') }}" variant="ghost" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" variant="primary" icon="user-plus">
                    Register Account
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>