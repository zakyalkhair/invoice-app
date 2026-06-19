<?php

use App\Models\User;

test('user can be created with valid data', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('John Doe');
    expect($user->email)->toBe('john@example.com');
});

test('user has hidden attributes', function () {
    $user = User::factory()->create();
    $hidden = $user->getHidden();

    expect($hidden)->toContain('password');
    expect($hidden)->toContain('remember_token');
});

test('user password is hashed', function () {
    $user = User::factory()->create([
        'password' => 'secret123',
    ]);

    expect($user->password)->not()->toBe('secret123');
});

test('user email is unique', function () {
    User::factory()->create([
        'email' => 'duplicate@example.com',
    ]);

    $this->expectException(\Illuminate\Database\QueryException::class);
    User::create([
        'name' => 'Another User',
        'email' => 'duplicate@example.com',
        'password' => 'password',
    ]);
});

test('user can be updated', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
    ]);

    $user->update(['name' => 'Updated Name']);

    expect($user->name)->toBe('Updated Name');
});

test('user can be deleted', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    $user->delete();

    expect(User::find($userId))->toBeNull();
});

test('user initials returns first letter of each of the first two words', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    expect($user->initials())->toBe('JD');
});

test('user initials with single word name returns one letter', function () {
    $user = User::factory()->create(['name' => 'Admin']);
    expect($user->initials())->toBe('A');
});
