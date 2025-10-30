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
            'solde' => (float) $this->solde ?? 0,
            'devise' => $this->devise,
            'dateCreation' => optional($this->date_creation)->toIso8601String(),
            'statut' => $this->statut,
            'motifBlocage' => $this->statut === 'bloque' ? ($this->motif_blocage ?? 'Raison non spécifiée') : null,
            'dateBlocage' => $this->when($this->statut === 'bloque', optional($this->date_blocage)->toIso8601String()),
            'dateDeblocagePrevue' => $this->when($this->statut === 'bloque', optional($this->date_deblocage_prevue)->toIso8601String()),
            'dateFermeture' => $this->when($this->trashed(), optional($this->deleted_at)->toIso8601String()),
            'metadata' => [
                'derniereModification' => optional($this->updated_at)->toIso8601String(),
                'version' => $this->version ?? 1,
            ]
        ];
    }
}
