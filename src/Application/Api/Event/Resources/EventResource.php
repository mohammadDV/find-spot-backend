<?php

namespace Application\Api\Event\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Domain\Business\Models\Favorite;
use Domain\Event\Models\Event;
use Core\Http\traits\GlobalFunc;

class EventResource extends JsonResource
{
    use GlobalFunc;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // is favorite or not
        $isFavorite = Favorite::query()
                ->where('favoritable_type', Event::class)
                ->where('favoritable_id', $this->id)
                ->where('user_id', $this->getAuthenticatedUser()->id)
                ->exists();

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
            'is_favorite' => $isFavorite,
        ];
    }
}
