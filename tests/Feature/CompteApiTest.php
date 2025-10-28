<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Compte;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompteApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_comptes()
    {
        // Créer des comptes de test
        Compte::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/comptes');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'numero',
                             'type',
                             'statut',
                             'devise',
                             'date_creation'
                         ]
                     ],
                     'total',
                     'debug'
                 ])
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_can_create_a_compte()
    {
        $compteData = [
            'type' => 'courant',
            'statut' => 'actif',
            'devise' => 'FCFA'
        ];

        $response = $this->postJson('/api/v1/comptes', $compteData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'numero',
                         'type',
                         'statut',
                         'devise',
                         'date_creation'
                     ]
                 ])
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('comptes', [
            'type' => 'courant',
            'statut' => 'actif',
            'devise' => 'FCFA'
        ]);
    }

    /** @test */
    public function it_can_show_a_specific_compte()
    {
        $compte = Compte::factory()->create();

        $response = $this->getJson("/api/v1/comptes/{$compte->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'numero',
                         'type',
                         'statut',
                         'devise',
                         'date_creation'
                     ]
                 ])
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_compte()
    {
        $response = $this->getJson('/api/v1/comptes/nonexistent-id');

        $response->assertStatus(404)
                 ->assertJsonStructure([
                     'success',
                     'error' => [
                         'code',
                         'message'
                     ]
                 ])
                 ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_can_update_a_compte()
    {
        $compte = Compte::factory()->create();

        $updateData = [
            'type' => 'epargne',
            'statut' => 'inactif'
        ];

        $response = $this->putJson("/api/v1/comptes/{$compte->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'numero',
                         'type',
                         'statut',
                         'devise',
                         'date_creation'
                     ]
                 ])
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('comptes', array_merge(['id' => $compte->id], $updateData));
    }

    /** @test */
    public function it_can_archive_a_compte()
    {
        $compte = Compte::factory()->create();

        $response = $this->deleteJson("/api/v1/comptes/{$compte->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message'
                 ])
                 ->assertJson(['success' => true]);

        // Vérifier que le compte est soft deleted
        $this->assertSoftDeleted('comptes', ['id' => $compte->id]);
    }

    /** @test */
    public function it_can_list_archived_comptes()
    {
        // Créer et archiver des comptes
        $compte1 = Compte::factory()->create();
        $compte2 = Compte::factory()->create();

        $compte1->delete(); // Soft delete

        $response = $this->getJson('/api/v1/comptes/archives');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'numero',
                             'type',
                             'statut',
                             'devise',
                             'date_creation'
                         ]
                     ],
                     'total'
                 ])
                 ->assertJson(['success' => true]);

        // Vérifier qu'un seul compte est dans les archives
        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function it_can_restore_an_archived_compte()
    {
        $compte = Compte::factory()->create();
        $compte->delete(); // Soft delete

        $response = $this->postJson("/api/v1/comptes/{$compte->id}/restore");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'numero',
                         'type',
                         'statut',
                         'devise',
                         'date_creation'
                     ]
                 ])
                 ->assertJson(['success' => true]);

        // Vérifier que le compte n'est plus soft deleted
        $this->assertDatabaseHas('comptes', ['id' => $compte->id]);
        $this->assertNull(Compte::find($compte->id)->deleted_at);
    }
}
