<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_clients()
    {
        // Créer des clients de test
        Client::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/clients');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'nom',
                             'prenom',
                             'email',
                             'telephone',
                             'adresse',
                             'statut'
                         ]
                     ]
                 ])
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_can_create_a_client()
    {
        $clientData = [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean.dupont@example.com',
            'telephone' => '01 23 45 67 89',
            'adresse' => '123 Rue de la Test',
            'statut' => 'actif'
        ];

        $response = $this->postJson('/api/v1/clients', $clientData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'nom',
                         'prenom',
                         'email',
                         'telephone',
                         'adresse',
                         'statut'
                     ]
                 ])
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('clients', $clientData);
    }

    /** @test */
    public function it_validates_client_creation()
    {
        $invalidData = [
            'nom' => '',
            'email' => 'invalid-email'
        ];

        $response = $this->postJson('/api/v1/clients', $invalidData);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'error' => [
                         'code',
                         'message',
                         'details'
                     ]
                 ])
                 ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_can_show_a_specific_client()
    {
        $client = Client::factory()->create();

        $response = $this->getJson("/api/v1/clients/{$client->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'nom',
                         'prenom',
                         'email',
                         'telephone',
                         'adresse',
                         'statut'
                     ]
                 ])
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_client()
    {
        $response = $this->getJson('/api/v1/clients/nonexistent-uuid');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_a_client()
    {
        $client = Client::factory()->create();

        $updateData = [
            'nom' => 'Martin',
            'prenom' => 'Marie',
            'email' => 'marie.martin@example.com'
        ];

        $response = $this->putJson("/api/v1/clients/{$client->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'nom',
                         'prenom',
                         'email'
                     ]
                 ])
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('clients', array_merge(['id' => $client->id], $updateData));
    }

    /** @test */
    public function it_can_delete_a_client()
    {
        $client = Client::factory()->create();

        $response = $this->deleteJson("/api/v1/clients/{$client->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
