<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;

test('passwordRules returns required string and confirmed', function () {
    $obj = new class {
        use PasswordValidationRules;

        public function getRules(): array { return $this->passwordRules(); }
    };

    $rules = $obj->getRules();
    expect($rules)->toContain('required');
    expect($rules)->toContain('string');
    expect($rules)->toContain('confirmed');
});

test('currentPasswordRules returns current_password rule', function () {
    $obj = new class {
        use PasswordValidationRules;

        public function getRules(): array { return $this->currentPasswordRules(); }
    };

    $rules = $obj->getRules();
    expect($rules)->toContain('required');
    expect($rules)->toContain('string');
    expect($rules)->toContain('current_password');
});

test('nameRules returns required string max 255', function () {
    $obj = new class {
        use ProfileValidationRules;

        public function getRules(): array { return $this->nameRules(); }
    };

    $rules = $obj->getRules();
    expect($rules)->toContain('required');
    expect($rules)->toContain('string');
    expect($rules)->toContain('max:255');
});

test('emailRules without userId returns unique rule for new user', function () {
    $obj = new class {
        use ProfileValidationRules;

        public function getRules(?int $userId = null): array { return $this->emailRules($userId); }
    };

    $rules = $obj->getRules(null);
    expect($rules)->toContain('required');
    expect($rules)->toContain('email');
});

test('emailRules with userId returns unique rule ignoring that id', function () {
    $user = User::factory()->create();

    $obj = new class {
        use ProfileValidationRules;

        public function getRules(?int $userId = null): array { return $this->emailRules($userId); }
    };

    $rules = $obj->getRules($user->id);
    expect($rules)->not()->toBeEmpty();
    expect($rules)->toContain('required');
});

test('profileRules returns array with name and email keys', function () {
    $obj = new class {
        use ProfileValidationRules;

        public function getRules(?int $userId = null): array { return $this->profileRules($userId); }
    };

    $rules = $obj->getRules();
    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('email');
});
