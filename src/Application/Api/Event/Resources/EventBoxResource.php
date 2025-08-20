<?php

namespace Application\Api\Event\Resources;

use Application\Api\Address\Resources\AreaResource;
use Application\Api\Address\Resources\CityResource;
use Application\Api\Address\Resources\CountryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EventBoxResource extends JsonResource
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
            'summary' => $this->summary,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'link' => $this->link,
            'amount' => $this->amount,
            'lat' => $this->lat,
            'long' => $this->long,
            'slider_image' => $this->slider_image,
        ];
    }
}
