<?php

use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staf', 'guard_name' => 'web']);
});

test('logout action logs out authenticated user and redirects to home', function () {
    Route::get('_test/logout', Logout::class)->middleware('web');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('_test/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});
