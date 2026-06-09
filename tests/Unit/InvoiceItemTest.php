<?php

use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Mitra;

test('invoice item can be created with valid data', function () {
    $item = InvoiceItem::factory()->create([
        'item_name' => 'Product A',
        'quantity' => 10,
    ]);

    expect($item)->toBeInstanceOf(InvoiceItem::class);
    expect($item->item_name)->toBe('Product A');
    expect($item->quantity)->toBe(10);
});

test('invoice item belongs to invoice', function () {
    $item = InvoiceItem::factory()->create();

    expect($item->invoice)->toBeInstanceOf(Invoice::class);
    expect($item->invoice_id)->not()->toBeNull();
});

test('invoice item can be updated', function () {
    $item = InvoiceItem::factory()->create([
        'quantity' => 2,
    ]);

    $item->update(['quantity' => 5]);

    expect($item->quantity)->toBe(5);
});

test('invoice item can be deleted', function () {
    $item = InvoiceItem::factory()->create();
    $itemId = $item->id;

    $item->delete();

    expect(InvoiceItem::find($itemId))->toBeNull();
});
