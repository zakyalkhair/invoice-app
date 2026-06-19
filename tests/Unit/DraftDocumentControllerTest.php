<?php

use App\Models\DraftDocument;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staf', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('draft document index page is accessible', function () {
    $this->get(route('draft-documents.index'))->assertStatus(200);
});

test('draft document index with search filter', function () {
    DraftDocument::create([
        'name' => 'Dokumen Pencarian',
        'file_url' => 'https://example.com/file.pdf',
        'file_original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
    ]);

    $this->get(route('draft-documents.index', ['search' => 'Pencarian']))
        ->assertStatus(200);
});

test('draft document create page is accessible', function () {
    $this->get(route('draft-documents.create'))->assertStatus(200);
});

test('draft document store fails with missing name', function () {
    $this->post(route('draft-documents.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('draft document store fails with missing file', function () {
    $this->post(route('draft-documents.store'), [
        'name' => 'Test Doc',
        'date' => '2026-06-10',
    ])->assertSessionHasErrors('file');
});

test('draft document store returns upload error when cloudinary not configured', function () {
    $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->post(route('draft-documents.store'), [
        'name' => 'Test Doc',
        'date' => '2026-06-10',
        'file' => $file,
    ]);

    $response->assertSessionHasErrors('file');
});

test('draft document show page is accessible', function () {
    $doc = DraftDocument::create([
        'name' => 'Test Document',
        'file_url' => 'https://example.com/file.pdf',
        'file_original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
    ]);

    $this->get(route('draft-documents.show', $doc))->assertStatus(200);
});

test('draft document edit page is accessible', function () {
    $doc = DraftDocument::create([
        'name' => 'Test Document',
        'file_url' => 'https://example.com/file.pdf',
        'file_original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
    ]);

    $this->get(route('draft-documents.edit', $doc))->assertStatus(200);
});

test('draft document can be updated without changing file', function () {
    $doc = DraftDocument::create([
        'name' => 'Old Name',
        'file_url' => 'https://example.com/file.pdf',
        'file_original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
    ]);

    $this->put(route('draft-documents.update', $doc), [
        'name' => 'New Name',
        'date' => '2026-06-10',
    ])->assertRedirect(route('draft-documents.index'));

    $this->assertDatabaseHas('draft_documents', ['name' => 'New Name']);
});

test('draft document can be deleted when no cloudinary public id', function () {
    $doc = DraftDocument::create([
        'name' => 'To Delete',
        'file_url' => 'https://example.com/file.pdf',
        'file_public_id' => null,
        'file_original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
    ]);

    $this->delete(route('draft-documents.destroy', $doc))
        ->assertRedirect(route('draft-documents.index'));

    $this->assertDatabaseMissing('draft_documents', ['id' => $doc->id]);
});

test('draft document can be deleted when cloudinary not configured', function () {
    $doc = DraftDocument::create([
        'name' => 'To Delete With ID',
        'file_url' => 'https://example.com/file.pdf',
        'file_public_id' => 'some-public-id',
        'file_original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
    ]);

    $this->delete(route('draft-documents.destroy', $doc))
        ->assertRedirect(route('draft-documents.index'));

    $this->assertDatabaseMissing('draft_documents', ['id' => $doc->id]);
});

test('draft document store succeeds when cloudinary upload returns valid result', function () {
    $this->app->bind(
        \App\Http\Controllers\DraftDocumentController::class,
        fn () => new class extends \App\Http\Controllers\DraftDocumentController {
            protected function uploadToCloudinary($file): array
            {
                return [
                    'secure_url' => 'https://res.cloudinary.com/test/image/upload/test.pdf',
                    'public_id' => 'test/test-public-id',
                ];
            }
        }
    );

    $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->post(route('draft-documents.store'), [
        'name' => 'Mocked Upload Doc',
        'date' => '2026-06-10',
        'file' => $file,
    ])->assertRedirect(route('draft-documents.index'));

    $this->assertDatabaseHas('draft_documents', [
        'name' => 'Mocked Upload Doc',
        'file_public_id' => 'test/test-public-id',
    ]);
});

test('draft document update with new file succeeds when cloudinary upload returns valid result', function () {
    $doc = DraftDocument::create([
        'name' => 'Old Doc',
        'file_url' => 'https://example.com/old.pdf',
        'file_public_id' => 'old-public-id',
        'file_original_name' => 'old.pdf',
        'mime_type' => 'application/pdf',
        'size' => 512,
    ]);

    $this->app->bind(
        \App\Http\Controllers\DraftDocumentController::class,
        fn () => new class extends \App\Http\Controllers\DraftDocumentController {
            protected function uploadToCloudinary($file): array
            {
                return [
                    'secure_url' => 'https://res.cloudinary.com/test/image/upload/new.pdf',
                    'public_id' => 'test/new-public-id',
                ];
            }

            protected function destroyFromCloudinary($publicId, $mimeType = null): bool
            {
                return true;
            }
        }
    );

    $file = \Illuminate\Http\UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');

    $this->put(route('draft-documents.update', $doc), [
        'name' => 'Updated Doc',
        'date' => '2026-06-15',
        'file' => $file,
    ])->assertRedirect(route('draft-documents.index'));

    $this->assertDatabaseHas('draft_documents', [
        'id' => $doc->id,
        'name' => 'Updated Doc',
        'file_public_id' => 'test/new-public-id',
    ]);
});
