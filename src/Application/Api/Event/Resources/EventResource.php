<?php

namespace Application\Api\Event\Resources;

use Application\Api\Address\Resources\CityResource;
use Application\Api\Address\Resources\CountryResource;
use Application\Api\Address\Resources\ProvinceResource;
use Application\Api\Business\Resources\CategoryResource;
use Application\Api\Business\Resources\FilterResource;
use Application\Api\Business\Resources\FileResource;
use Domain\Business\Models\Business;
use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;
use Application\Api\User\Resources\UserResource;

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
        $destinationImage = config('image.default_business_image');

            if ($this->relationLoaded('area') && $this->area?->image) {
                $destinationImage = $this->area->image;
            } elseif ($this->relationLoaded('city') && $this->city?->image) {
                $destinationImage = $this->city->image;
            }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'path_type' => $this->path_type,
            'amount_type' => $this->amount_type,
            'start_amount' => $this->start_amount,
            'end_amount' => $this->end_amount,
            'lat' => $this->lat,
            'long' => $this->long,
            'website' => $this->website,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'image' => $this->image,
            'status' => $this->status,
            'description' => $this->description,
            'vip' => $this->vip,
            'image' => $destinationImage,
            'menu_image' => $destinationImage,
            'video' => $destinationImage,
            'send_date' => $this->send_date ? Jalalian::fromDateTime($this->send_date)->format('d F') : null,
            'receive_date' => $this->receive_date ? Jalalian::fromDateTime($this->receive_date)->format('d F') : null,
            'country' => new CountryResource($this->whenLoaded('oCountry')),
            'province' => new ProvinceResource($this->whenLoaded('oProvince')),
            'city' => new CityResource($this->whenLoaded('oCity')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'filters' => FilterResource::collection($this->whenLoaded('filters')),
            'files' => FileResource::collection($this->whenLoaded('files')),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
