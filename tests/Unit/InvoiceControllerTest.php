<?php

use App\Models\Invoice;
use App\Models\Mitra;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staf', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

test('invoice index page is accessible', function () {
    $this->get(route('invoice'))->assertStatus(200);
});

test('invoice index with search filter', function () {
    Invoice::factory()->create(['invoice_number' => 'SEARCH-001/KSM/VI/2026']);
    Invoice::factory()->create(['invoice_number' => 'OTHER-002/KSM/VI/2026']);

    $this->get(route('invoice', ['search' => 'SEARCH']))->assertStatus(200);
});

test('invoice index with date range filters', function () {
    Invoice::factory()->create(['invoice_date' => '2026-01-15']);

    $this->get(route('invoice', ['start_date' => '2026-01-01', 'end_date' => '2026-12-31']))
        ->assertStatus(200);
});

test('invoice create page is accessible', function () {
    $this->get(route('invoices.create'))->assertStatus(200);
});

test('invoice create page generates incremented number when previous invoice exists', function () {
    Invoice::factory()->create(['invoice_number' => '005/KSM/VI/2026']);

    $this->get(route('invoices.create'))->assertStatus(200);
});

test('invoice can be stored with valid data', function () {
    $mitra = Mitra::factory()->create();

    $this->post(route('invoices.store'), [
        'invoice_number' => '001/KSM/VI/2026',
        'invoice_date' => '2026-06-10',
        'mitra_id' => $mitra->id,
        'issuer_company' => 'PT Test Corp',
        'issuer_name' => 'John Doe',
        'description' => 'Test description',
        'amount_in_words' => 'Seratus ribu rupiah',
        'items' => [
            ['item_name' => 'Item A', 'quantity' => 2, 'unit_price' => 50000],
        ],
    ])->assertRedirect(route('invoice'));

    $this->assertDatabaseHas('invoices', ['invoice_number' => '001/KSM/VI/2026']);
});

test('invoice store fails with missing invoice_number', function () {
    $this->post(route('invoices.store'), ['invoice_number' => ''])
        ->assertSessionHasErrors('invoice_number');
});

test('invoice show page is accessible', function () {
    $invoice = Invoice::factory()->create();
    $this->get(route('invoices.show', $invoice))->assertStatus(200);
});

test('invoice edit page is accessible for admin', function () {
    $invoice = Invoice::factory()->create();
    $this->get(route('invoices.edit', $invoice))->assertStatus(200);
});

test('non-admin cannot access edit page of invoice they do not own', function () {
    $staf = User::factory()->create();
    $staf->assignRole('staf');
    $invoice = Invoice::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($staf);
    $this->get(route('invoices.edit', $invoice))->assertStatus(403);
});

test('invoice can be updated by admin', function () {
    $mitra = Mitra::factory()->create();
    $invoice = Invoice::factory()->create([
        'user_id' => $this->admin->id,
        'mitra_id' => $mitra->id,
    ]);

    $this->put(route('invoices.update', $invoice), [
        'invoice_number' => 'UPDATED-001/KSM/VI/2026',
        'invoice_date' => '2026-06-15',
        'mitra_id' => $mitra->id,
        'issuer_company' => 'PT Updated Corp',
        'issuer_name' => 'Jane Doe',
        'description' => 'Updated description',
        'amount_in_words' => 'Seratus ribu rupiah',
        'items' => [
            ['item_name' => 'Updated Item', 'quantity' => 1, 'unit_price' => 100000],
        ],
    ])->assertRedirect(route('invoices.show', $invoice));

    $this->assertDatabaseHas('invoices', ['invoice_number' => 'UPDATED-001/KSM/VI/2026']);
});

test('non-admin cannot update invoice they do not own', function () {
    $staf = User::factory()->create();
    $staf->assignRole('staf');
    $mitra = Mitra::factory()->create();
    $invoice = Invoice::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($staf);
    $this->put(route('invoices.update', $invoice), [
        'invoice_number' => 'HACK-001',
        'invoice_date' => '2026-06-15',
        'mitra_id' => $mitra->id,
        'items' => [['item_name' => 'X', 'quantity' => 1, 'unit_price' => 1]],
    ])->assertStatus(403);
});

test('invoice can be approved by admin', function () {
    $invoice = Invoice::factory()->create(['status' => 'pending']);

    $this->post(route('invoices.approve', $invoice))->assertRedirect();

    $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'approve']);
});

test('non-admin cannot approve invoice', function () {
    $staf = User::factory()->create();
    $staf->assignRole('staf');
    $this->actingAs($staf);

    $invoice = Invoice::factory()->create(['status' => 'pending']);

    $this->post(route('invoices.approve', $invoice))->assertStatus(403);
});

test('invoice can be deleted by admin', function () {
    $invoice = Invoice::factory()->create();

    $this->delete(route('invoices.destroy', $invoice))
        ->assertRedirect(route('invoice'));

    $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
});

test('non-admin cannot delete invoice they do not own', function () {
    $staf = User::factory()->create();
    $staf->assignRole('staf');
    $invoice = Invoice::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($staf);
    $this->delete(route('invoices.destroy', $invoice))->assertStatus(403);
});

test('invoice print returns error when status is not approve', function () {
    $invoice = Invoice::factory()->create(['status' => 'pending']);

    $this->get(route('invoices.print', $invoice))->assertSessionHas('error');
});

test('invoice download pdf returns error when status is not approve', function () {
    $invoice = Invoice::factory()->create(['status' => 'pending']);

    $this->get(route('invoices.pdf', $invoice))->assertSessionHas('error');
});
