<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'fullName' => $this->first_name.' '.$this->last_name,
            'email' => $this->email,
            'permissions' => $this->permissions,
            'lastLogin' => $this->last_login,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
        return $data;
    }
}
