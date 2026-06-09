<?php

use App\Models\Mitra;
use App\Models\Invoice;
use App\Models\User;

test('mitra can be created with valid data', function () {
    $mitra = Mitra::factory()->create([
        'company_name' => 'PT. Example Company',
    ]);

    expect($mitra)->toBeInstanceOf(Mitra::class);
    expect($mitra->company_name)->toBe('PT. Example Company');
    expect($mitra->email)->not()->toBeNull();
});

test('mitra can have many invoices', function () {
    $mitra = Mitra::factory()->create();

    Invoice::factory(2)->create([
        'mitra_id' => $mitra->id,
    ]);

    $invoices = $mitra->invoices;

    expect($invoices)->toHaveCount(2);
});

test('mitra contact information is required', function () {
    $mitra = Mitra::factory()->create();

    expect($mitra->company_name)->not()->toBeNull();
    expect($mitra->email)->not()->toBeNull();
    expect($mitra->phone)->not()->toBeNull();
});

test('mitra can be updated', function () {
    $mitra = Mitra::factory()->create([
        'company_name' => 'PT. Original Name',
    ]);

    $mitra->update([
        'company_name' => 'PT. Updated Name',
    ]);

    expect($mitra->company_name)->toBe('PT. Updated Name');
});

test('mitra can be deleted', function () {
    $mitra = Mitra::factory()->create();
    $mitraId = $mitra->id;

    $mitra->delete();

    expect(Mitra::find($mitraId))->toBeNull();
});
