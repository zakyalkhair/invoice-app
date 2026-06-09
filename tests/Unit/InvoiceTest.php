<?php

use App\Models\Invoice;
use App\Models\User;
use App\Models\Mitra;
use App\Models\InvoiceItem;

test('invoice can be created with valid data', function () {
    $invoice = Invoice::factory()->create([
        'invoice_number' => 'INV-2026-001',
    ]);

    expect($invoice)->toBeInstanceOf(Invoice::class);
    expect($invoice->invoice_number)->toBe('INV-2026-001');
    expect($invoice->total_amount)->toBeGreaterThan(0);
});

test('invoice belongs to user', function () {
    $invoice = Invoice::factory()->create();

    expect($invoice->user)->toBeInstanceOf(User::class);
    expect($invoice->user_id)->not()->toBeNull();
});

test('invoice belongs to mitra', function () {
    $invoice = Invoice::factory()->create();

    expect($invoice->mitra)->toBeInstanceOf(Mitra::class);
    expect($invoice->mitra_id)->not()->toBeNull();
});

test('invoice has many items', function () {
    $invoice = Invoice::factory()->create();

    InvoiceItem::factory(3)->create([
        'invoice_id' => $invoice->id,
    ]);

    $items = $invoice->items;

    expect($items)->toHaveCount(3);
});

test('invoice status can be updated', function () {
    $invoice = Invoice::factory()->create([
        'status' => 'draft',
    ]);

    $invoice->update(['status' => 'submitted']);

    expect($invoice->status)->toBe('submitted');
});

