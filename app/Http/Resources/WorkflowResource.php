<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'application_id' => $this->application_id,
            'author_id' => $this->author_id,
            'trigger' => $this->trigger,
            'trigger_config' => $this->trigger_config,
            'action' => $this->action,
            'action_config' => $this->action_config,
            'delay' => $this->delay,
            'status' => $this->status,
            'active_from' => $this->active_from,
            'active_to' => $this->active_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}