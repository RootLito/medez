<?php

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use function Livewire\Volt\{layout, title, state, rules, computed};

layout('components.layouts.admin');
title('User Management');

state([
    'name' => '',
    'username' => '',
    'role' => 'mpdc_staff',
    'password' => '',
    'is_active' => true,

    'editingAdminId' => null,
    'edit_name' => '',
    'edit_username' => '',
    'edit_role' => 'mpdc_staff',
    'edit_password' => '',
    'edit_is_active' => true,

    'deletingAdminId' => null,
    'deletingAdminName' => '',
    'confirmDeleteName' => '',

    'showCreateModal' => false,
    'showEditModal' => false,
    'showDeleteModal' => false,
]);

$getFormalPosition = function (string $role): string {
    return match ($role) {
        'admin' => 'System Administrator',
        'mpdc_staff' => 'MPDC Staff',
        'inspector' => 'Building Inspector',
        default => 'Staff',
    };
};

$admins = computed(fn() => Admin::where('role', '!=', 'admin')->latest()->get());

$createAdmin = function () use ($getFormalPosition) {
    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:255', 'unique:admins,username'],
        'role' => ['required', Rule::in(['admin', 'mpdc_staff', 'inspector'])],
        'password' => ['required', 'string', Password::defaults()],
        'is_active' => ['boolean'],
    ]);

    Admin::create([
        'full_name' => $validated['name'],
        'position' => $getFormalPosition($validated['role']),
        'username' => $validated['username'],
        'role' => $validated['role'],
        'password' => Hash::make($validated['password']),
        'is_active' => $validated['is_active'],
    ]);

    $this->reset(['name', 'username', 'role', 'password', 'is_active', 'showCreateModal']);

    Flux::toast(
        heading: 'Success',
        text: 'Admin account created successfully.',
        variant: 'success',
    );
};

$openEditModal = function ($id) {
    $admin = Admin::findOrFail($id);
    $this->editingAdminId = $admin->id;
    $this->edit_name = $admin->full_name;
    $this->edit_username = $admin->username;
    $this->edit_role = $admin->role ?? 'mpdc_staff';
    $this->edit_is_active = (bool) $admin->is_active;
    $this->edit_password = '';
    $this->showEditModal = true;
};

$updateAdmin = function () use ($getFormalPosition) {
    $admin = Admin::findOrFail($this->editingAdminId);

    $validated = $this->validate([
        'edit_name' => ['required', 'string', 'max:255'],
        'edit_username' => ['required', 'string', 'max:255', 'unique:admins,username,' . $admin->id],
        'edit_role' => ['required', Rule::in(['admin', 'mpdc_staff', 'inspector'])],
        'edit_password' => ['nullable', 'string', Password::defaults()],
        'edit_is_active' => ['boolean'],
    ]);

    $data = [
        'full_name' => $validated['edit_name'],
        'position' => $getFormalPosition($validated['edit_role']),
        'username' => $validated['edit_username'],
        'role' => $validated['edit_role'],
        'is_active' => $validated['edit_is_active'],
    ];

    if (!empty($validated['edit_password'])) {
        $data['password'] = Hash::make($validated['edit_password']);
    }

    $admin->update($data);

    $this->reset(['editingAdminId', 'edit_name', 'edit_username', 'edit_role', 'edit_password', 'edit_is_active', 'showEditModal']);

    Flux::toast(
        heading: 'Updated',
        text: 'Admin account updated successfully.',
        variant: 'success',
    );
};

$confirmDelete = function ($id) {
    $admin = Admin::findOrFail($id);
    $this->deletingAdminId = $admin->id;
    $this->deletingAdminName = $admin->full_name;
    $this->confirmDeleteName = '';
    $this->showDeleteModal = true;
};

$deleteAdmin = function () {
    if ($this->confirmDeleteName !== $this->deletingAdminName) {
        $this->addError('confirmDeleteName', 'The typed name does not match.');

        Flux::toast([
            'heading' => 'Action Failed',
            'text' => 'Confirmation name does not match.',
            'variant' => 'danger',
        ]);
        return;
    }

    if (method_exists(Admin::class, 'bootSoftDeletes')) {
        Admin::forceDestroy($this->deletingAdminId);
    } else {
        Admin::destroy($this->deletingAdminId);
    }

    $this->reset(['deletingAdminId', 'deletingAdminName', 'confirmDeleteName', 'showDeleteModal']);

    Flux::toast(
        heading: 'Deleted',
        text: 'Account permanently deleted.',
        variant: 'danger',
    );
};

?>

<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1">User Management</flux:heading>
            <flux:subheading>Manage staff accounts, credentials, and access.</flux:subheading>
        </div>
        <flux:modal.trigger name="create-admin-modal">
            <flux:button variant="primary" icon="plus" wire:click="$set('showCreateModal', true)">
                Add User
            </flux:button>
        </flux:modal.trigger>
    </div>

    <flux:separator variant="subtle" class="mb-6" />

    @if ($this->admins->isEmpty())
        <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
            <flux:icon.users class="mx-auto h-12 w-12 text-zinc-400" />
            <flux:heading size="lg" class="mt-4">No Accounts Found</flux:heading>
            <flux:text class="mt-1">Click the "Add User" button above to create staff credentials.</flux:text>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($this->admins as $admin)
                <flux:card class="flex flex-col justify-between p-4 space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between space-x-2">
                            <flux:avatar name="{{ $admin->full_name }}" class="flex-shrink-0" />
                            <flux:badge size="sm" :color="$admin->is_active ? 'success' : 'zinc'">
                                {{ $admin->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </div>

                        <div class="min-w-0">
                            <flux:heading size="base" class="truncate font-semibold">{{ $admin->full_name }}
                            </flux:heading>
                            <flux:text size="xs" class="truncate text-zinc-400">
                                Username: <span>{{ $admin->username }}</span>
                            </flux:text>
                        </div>

                        <div>
                            <flux:badge size="sm" variant="subtle">
                                {{ $admin->position ?? ucfirst(str_replace('_', ' ', $admin->role)) }}
                            </flux:badge>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 mt-4">
                        <flux:button variant="ghost" size="sm" icon="pencil-square"
                            wire:click="openEditModal({{ $admin->id }})">
                            Edit
                        </flux:button>
                        <flux:button variant="filled" color="red" size="sm" icon="trash"
                            wire:click="confirmDelete({{ $admin->id }})">
                            Delete
                        </flux:button>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    <flux:modal name="create-admin-modal" wire:model="showCreateModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add Staff Account</flux:heading>

                <flux:text class="mt-2">
                    Create new credentials to grant internal system access.
                </flux:text>
            </div>

            <form wire:submit="createAdmin" class="space-y-4">
                <flux:input wire:model="name" label="Full Name" required />

                <flux:select wire:model="role" label="Role">
                    <flux:select.option value="mpdc_staff">MPDC Staff</flux:select.option>
                    <flux:select.option value="inspector">Inspector</flux:select.option>
                    <flux:select.option value="admin">System Admin</flux:select.option>
                </flux:select>

                <flux:input wire:model="username" label="Username" required />

                <flux:input wire:model="password" type="password" label="Password" viewable required />

                <flux:switch wire:model="is_active" label="Active Account" />

                <div class="flex gap-2 pt-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">Create Account</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="edit-admin-modal" wire:model="showEditModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Account</flux:heading>

                <flux:text class="mt-2">
                    Update staff profile details, role, or credentials.
                </flux:text>
            </div>

            <form wire:submit="updateAdmin" class="space-y-4">
                <flux:input wire:model="edit_name" label="Full Name" required />

                <flux:select wire:model="edit_role" label="Role">
                    <flux:select.option value="mpdc_staff">MPDC Staff</flux:select.option>
                    <flux:select.option value="inspector">Inspector</flux:select.option>
                    <flux:select.option value="admin">System Admin</flux:select.option>
                </flux:select>

                <flux:input wire:model="edit_username" label="Username" required />

                <flux:input wire:model="edit_password" type="password" label="New Password"
                    placeholder="Leave blank to keep current" viewable />

                <flux:switch wire:model="edit_is_active" label="Active Account" />

                <div class="flex gap-2 pt-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">Save Changes</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="delete-admin-modal" wire:model="showDeleteModal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Account?</flux:heading>

                <flux:text class="mt-2">
                    <span class="text-red-500">This action will delete this admin account.</span><br>
                    To confirm, type <strong class="text-zinc-900 dark:text-zinc-100">{{ $deletingAdminName }}</strong>
                    below:
                </flux:text>
            </div>

            <flux:input wire:model.live="confirmDeleteName" placeholder="{{ $deletingAdminName }}"
                autocomplete="off" />

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button wire:click="deleteAdmin" variant="danger"
                    :disabled="$confirmDeleteName !== $deletingAdminName">
                    Confirm Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
