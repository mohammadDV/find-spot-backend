<?php

namespace Application\Api\Event\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EventResource extends JsonResource
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
            'information' => $this->information,
            'description' => $this->description,
            'link' => $this->link,
            'address' => $this->address,
            'amount' => intval($this->amount),
            'lat' => $this->lat,
            'long' => $this->long,
            'website' => $this->website,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'whatsapp' => $this->whatsapp,
            'youtube' => $this->youtube,
            'image' => $this->image,
            'description' => $this->description,
            'image' => $this->image,
            'start_date' => Carbon::parse($this->start_date)->format('F j'),
            'end_date' => Carbon::parse($this->end_date)->format('F j'),
            'slider_image' => $this->slider_image,
            'video' => $this->video,
        ];
    }
}
