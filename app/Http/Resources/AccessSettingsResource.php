<?php

namespace App\Http\Resources;

use App\Models\AccessLevel;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $accessLevel = AccessLevel::findOrFail($this->access_level_id)->value;
        return [
            'access_level' => $accessLevel,
        ];
    }
}
