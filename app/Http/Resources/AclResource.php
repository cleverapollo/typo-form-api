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
            'ability' => $this->name,
            'resource_id' => $this->entity_id,
            'resource_type' => array_pop($entity_type),
        ];
    }
}
