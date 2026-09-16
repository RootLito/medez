<?php
use function Livewire\Volt\{layout, title};

// Tell Volt to use the Client layout shell
layout('components.layouts.client');
title('My Application');
?>

<div>
    <h1 class="text-2xl font-bold">Client Application</h1>
    <p>Manage your application status and details.</p>
</div>