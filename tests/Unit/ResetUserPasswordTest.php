<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Validation\ValidationException;

test('resets user password with valid input', function () {
    $user = User::factory()->create();
    $oldHash = $user->password;

    $action = new ResetUserPassword;
    $action->reset($user, [
        'password' => 'newpassword12',
        'password_confirmation' => 'newpassword12',
    ]);

    $user->refresh();
    expect($user->password)->not()->toBe($oldHash);
});

test('fails when password confirmation does not match', function () {
    $user = User::factory()->create();

    $action = new ResetUserPassword;

    expect(fn () => $action->reset($user, [
        'password' => 'newpassword12',
        'password_confirmation' => 'wrongconfirm',
    ]))->toThrow(ValidationException::class);
});
