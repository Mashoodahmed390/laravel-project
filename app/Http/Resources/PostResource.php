<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "file"=> $this->file,
            "user_id"=> $this->user_id,
            "privacy"=> $this->privacy,
            "created"=>(string)$this->created_at,
            "updated"=>(string)$this->updated_at,
            "comment"=>$this->comment()->get()
        ];
    }
}
