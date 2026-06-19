<?php

use App\Models\Mitra;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staf', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('mitra index page is accessible', function () {
    $this->get(route('mitras.index'))->assertStatus(200);
});

test('mitra create page is accessible', function () {
    $this->get(route('mitras.create'))->assertStatus(200);
});

test('mitra can be stored with valid data', function () {
    $this->post(route('mitras.store'), [
        'company_name' => 'PT Test Company',
        'contact_person' => 'John Doe',
        'email' => 'company@example.com',
        'phone' => '081234567890',
        'address' => 'Jl. Test No. 1',
    ])->assertRedirect(route('mitras.index'));

    $this->assertDatabaseHas('mitras', ['company_name' => 'PT Test Company']);
});

test('mitra store fails with missing company_name', function () {
    $this->post(route('mitras.store'), ['company_name' => ''])
        ->assertSessionHasErrors('company_name');
});

test('mitra show page is accessible', function () {
    $mitra = Mitra::factory()->create();
    $this->get(route('mitras.show', $mitra))->assertStatus(200);
});

test('mitra edit page is accessible', function () {
    $mitra = Mitra::factory()->create();
    $this->get(route('mitras.edit', $mitra))->assertStatus(200);
});

test('mitra can be updated', function () {
    $mitra = Mitra::factory()->create(['company_name' => 'Old Company']);

    $this->put(route('mitras.update', $mitra), [
        'company_name' => 'New Company',
    ])->assertRedirect(route('mitras.index'));

    $this->assertDatabaseHas('mitras', ['company_name' => 'New Company']);
});

test('mitra update fails with empty company_name', function () {
    $mitra = Mitra::factory()->create();

    $this->put(route('mitras.update', $mitra), ['company_name' => ''])
        ->assertSessionHasErrors('company_name');
});

test('mitra can be deleted', function () {
    $mitra = Mitra::factory()->create();

    $this->delete(route('mitras.destroy', $mitra))
        ->assertRedirect(route('mitras.index'));

    $this->assertDatabaseMissing('mitras', ['id' => $mitra->id]);
});

test('getMitras returns json with correct structure', function () {
    Mitra::factory(3)->create();

    $this->get(route('mitras.api'))
        ->assertStatus(200)
        ->assertJsonStructure([['id', 'company_name', 'contact_person']]);
});
