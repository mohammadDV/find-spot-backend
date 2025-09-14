<?php

namespace Application\Api\Business\Resources;

use Application\Api\Address\Resources\AreaResource;
use Application\Api\Business\Resources\CategoryResource;
use Application\Api\Business\Resources\FilterResource;
use Application\Api\Business\Resources\FileResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;
use Application\Api\User\Resources\UserResource;
use DateTimeZone;

class BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $currentDayOfWeek = now()->format('l'); // Returns English day name (Monday, Tuesday, etc.)
        // now
        $from = 'from_' . strtolower($currentDayOfWeek);
        $to = 'to_' . strtolower($currentDayOfWeek);
        if (!empty($this->$from) && !empty($this->$to)) {
            $opening_hours = now()->dayName . ' ' . $this->$from . ':00 الی ' . $this->$to . ':00';
        } else {
            $opening_hours = now()->dayName . ' ' . $this->from_monday . ' الی ' . $this->to_monday;
        }


        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'from_monday' => $this->from_monday,
            'to_monday' => $this->to_monday,
            'from_tuesday' => $this->from_tuesday,
            'to_tuesday' => $this->to_tuesday,
            'from_wednesday' => $this->from_wednesday,
            'to_wednesday' => $this->to_wednesday,
            'from_thursday' => $this->from_thursday,
            'to_thursday' => $this->to_thursday,
            'from_friday' => $this->from_friday,
            'to_friday' => $this->to_friday,
            'from_saturday' => $this->from_saturday,
            'to_saturday' => $this->to_saturday,
            'from_sunday' => $this->from_sunday,
            'to_sunday' => $this->to_sunday,
            'country_id' => $this->country_id,
            'city_id' => $this->city_id,
            'path_type' => $this->path_type,
            'amount_type' => $this->amount_type,
            'start_amount' => intval($this->start_amount),
            'lat' => $this->lat,
            'long' => $this->long,
            'website' => $this->website,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'image' => $this->image,
            'status' => $this->status,
            'description' => $this->description,
            'vip' => $this->vip,
            'image' => $this->image,
            'menu_image' => $this->menu_image,
            'video' => $this->video,
            'rate' => $this->rate,
            'reviews_count' => $this->reviews()->count(),
            'opening_hours' => $opening_hours,
            'area' => new AreaResource($this->whenLoaded('area')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'filters' => FilterResource::collection($this->whenLoaded('filters')),
            'files' => FileResource::collection($this->whenLoaded('files')),
            'user' => new UserResource($this->whenLoaded('user')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'facilities' => FacilityResource::collection($this->whenLoaded('facilities'))
        ];
    }
}
