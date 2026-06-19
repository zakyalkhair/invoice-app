<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staf', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

test('user index page is accessible', function () {
    $this->get(route('users.index'))->assertStatus(200);
});

test('user create page is accessible', function () {
    $this->get(route('users.create'))->assertStatus(200);
});

test('user can be stored and assigned staf role', function () {
    $this->post(route('users.store'), [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password12',
        'password_confirmation' => 'password12',
    ])->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);

    $created = User::where('email', 'newuser@example.com')->first();
    expect($created->hasRole('staf'))->toBeTrue();
});

test('user store fails with duplicate email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->post(route('users.store'), [
        'name' => 'Another',
        'email' => 'existing@example.com',
        'password' => 'password12',
        'password_confirmation' => 'password12',
    ])->assertSessionHasErrors('email');
});

test('user show page is accessible', function () {
    $user = User::factory()->create();
    $this->get(route('users.show', $user))->assertStatus(200);
});

test('user edit page is accessible', function () {
    $user = User::factory()->create();
    $this->get(route('users.edit', $user))->assertStatus(200);
});

test('user can be updated without changing password', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $this->put(route('users.update', $user), [
        'name' => 'Updated Name',
        'email' => $user->email,
    ])->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
});

test('user can be updated with new password', function () {
    $user = User::factory()->create();

    $this->put(route('users.update', $user), [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'newpassword12',
        'password_confirmation' => 'newpassword12',
    ])->assertRedirect(route('users.index'));
});

test('user can be deleted', function () {
    $user = User::factory()->create();

    $this->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('user cannot delete their own account', function () {
    $this->delete(route('users.destroy', $this->admin))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
});
