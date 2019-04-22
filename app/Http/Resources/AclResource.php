<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AclResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $entity_type = explode('\\', $this->entity_type);
        return [
            'action' => $this->name,
            'entityId' => $this->entity_id,
            'entityType' => array_pop($entity_type),
        ];
    }
}
