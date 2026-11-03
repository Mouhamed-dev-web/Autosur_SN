<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('local');
    }

    private function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk;
    }

    private function client(): User
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        return $user;
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Carte grise Corolla',
            'type' => 'carte_grise',
            'file' => UploadedFile::fake()->create('cg.pdf', 200, 'application/pdf'),
        ], $override);
    }

    public function test_un_client_peut_ajouter_un_document(): void
    {
        $user = $this->client();

        $this->actingAs($user)
            ->post(route('client.documents.store'), $this->payload())
            ->assertRedirect(route('client.documents.index'));

        $doc = $user->documents()->first();
        $this->assertNotNull($doc);
        $this->disk()->assertExists($doc->path);
    }

    public function test_format_non_autorise_refuse(): void
    {
        $this->actingAs($this->client())
            ->post(route('client.documents.store'), $this->payload([
                'file' => UploadedFile::fake()->create('virus.exe', 100),
            ]))
            ->assertSessionHasErrors('file');
    }

    public function test_fichier_trop_gros_refuse(): void
    {
        $this->actingAs($this->client())
            ->post(route('client.documents.store'), $this->payload([
                'file' => UploadedFile::fake()->create('gros.pdf', 6000, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('file');
    }

    public function test_impossible_de_lier_au_vehicule_d_un_autre(): void
    {
        $a = $this->client();
        $b = $this->client();
        $vehicle = $b->vehicles()->create([
            'plate' => 'DK-5555-EE', 'brand' => 'Kia', 'model' => 'Rio', 'year' => 2020, 'insurance_status' => 'active',
        ]);

        $this->actingAs($a)
            ->post(route('client.documents.store'), $this->payload(['vehicle_id' => $vehicle->id]))
            ->assertSessionHasErrors('vehicle_id');
    }

    public function test_on_ne_peut_pas_voir_le_document_d_un_autre(): void
    {
        $a = $this->client();
        $b = $this->client();

        $this->actingAs($b)->post(route('client.documents.store'), $this->payload());
        $doc = $b->documents()->first();

        $this->actingAs($a)->get(route('client.documents.show', $doc))->assertForbidden();
        $this->actingAs($a)->get(route('client.documents.download', $doc))->assertForbidden();
        $this->actingAs($a)->delete(route('client.documents.destroy', $doc))->assertForbidden();

        $this->disk()->assertExists($doc->path);
    }

    public function test_suppression_efface_aussi_le_fichier(): void
    {
        $user = $this->client();
        $this->actingAs($user)->post(route('client.documents.store'), $this->payload());
        $doc = $user->documents()->first();

        $this->actingAs($user)->delete(route('client.documents.destroy', $doc))->assertRedirect();

        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
        $this->disk()->assertMissing($doc->path);
    }
}