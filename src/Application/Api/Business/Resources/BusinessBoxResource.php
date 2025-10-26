<?php

namespace Application\Api\Business\Resources;

use Application\Api\Address\Resources\AreaResource;
use Application\Api\Address\Resources\CityResource;
use Application\Api\Address\Resources\CountryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessBoxResource extends JsonResource
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
            'id' => $this->id,
            'title' => $this->title,
            'amount_type' => $this->amount_type,
            'start_amount' => intval($this->start_amount),
            'description' => $this->description,
            'lat' => $this->lat,
            'long' => $this->long,
            'image' => $this->image,
            'rate' => $this->rate,
            'status' => $this->status,
            'area' => new AreaResource($this->whenLoaded('area')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
