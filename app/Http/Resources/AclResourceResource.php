<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AclResourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'ability' => $this->name,
            'users' => $this->users->pluck('id'),
        ];
    }
}
