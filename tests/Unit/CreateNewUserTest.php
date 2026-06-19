<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Validation\ValidationException;

test('creates user with valid data', function () {
    $action = new CreateNewUser;

    $user = $action->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password12',
        'password_confirmation' => 'password12',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('John Doe');
    expect($user->email)->toBe('john@example.com');
    expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
});

test('fails with invalid email format', function () {
    $action = new CreateNewUser;

    expect(fn () => $action->create([
        'name' => 'John Doe',
        'email' => 'not-an-email',
        'password' => 'password12',
        'password_confirmation' => 'password12',
    ]))->toThrow(ValidationException::class);
});

test('fails when password confirmation does not match', function () {
    $action = new CreateNewUser;

    expect(fn () => $action->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password12',
        'password_confirmation' => 'different12',
    ]))->toThrow(ValidationException::class);
});

test('fails when email is already taken', function () {
    User::factory()->create(['email' => 'duplicate@example.com']);

    $action = new CreateNewUser;

    expect(fn () => $action->create([
        'name' => 'Another User',
        'email' => 'duplicate@example.com',
        'password' => 'password12',
        'password_confirmation' => 'password12',
    ]))->toThrow(ValidationException::class);
});
