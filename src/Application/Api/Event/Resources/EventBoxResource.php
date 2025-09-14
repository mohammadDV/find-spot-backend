<?php

namespace Application\Api\Event\Resources;

use Application\Api\Address\Resources\AreaResource;
use Application\Api\Address\Resources\CityResource;
use Application\Api\Address\Resources\CountryResource;
use Carbon\Carbon;
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
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->format('F j') : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->format('F j') : null,
            'link' => $this->link,
            'amount' => intval($this->amount),
            'lat' => $this->lat,
            'long' => $this->long,
            'image' => $this->image,
        ];
    }
}
