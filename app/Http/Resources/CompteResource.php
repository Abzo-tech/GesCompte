<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $client = $this->whenLoaded('client');
        $titulaire = $client ? trim(($client->prenom ?? '') . ' ' . ($client->nom ?? '')) : null;

        return [
            'id' => $this->id,
            'numeroCompte' => $this->numero,
            'titulaire' => $titulaire ?: null,
            'type' => $this->type,
            // Placeholder: solde non implémenté. Retourner 0 par défaut.
            'solde' => 0,
            'devise' => $this->devise,
            'dateCreation' => optional($this->date_creation)->toISOString(),
            'statut' => $this->statut,
            // Placeholder motifBlocage
            'motifBlocage' => $this->statut === 'bloque' ? ($this->motif_blocage ?? 'Bloqué') : null,
            'metadata' => [
                'derniereModification' => optional($this->updated_at)->toISOString(),
                'version' => 1,
            ],
        ];
    }
}
