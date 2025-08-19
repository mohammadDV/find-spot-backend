<?php

namespace Domain\Address\Repositories;

use Core\Http\Requests\TableRequest;
use Domain\Address\Models\Area;
use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Repositories\Contracts\IAddressRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class AddressRepository.
 */
class AddressRepository implements IAddressRepository
{

    /**
     * Get the countrys pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getCountriesPaginate(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return Country::query()
            // ->when(Auth::user()->level != 3, function ($query) {
            //     return $query->where('user_id', Auth::user()->id);
            // })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
                    // ->orWhere('alias_title','like','%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the countrys.
     * @return Collection
     */
    public function activeCountries() :Collection
    {
        return Country::query()
            ->where('status', 1)
            ->get();
    }


    /**
     * Get the areas pagination.
     * @param City $city
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getAreasPaginate(City $city, TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return Area::query()
            ->where('city_id', $city->id)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the areas.
     *
     * @param City $city
     * @return Collection
     */
    public function activeAreas(City $city) :Collection
    {
        return Area::query()
            ->where('city_id', $city->id)
            ->where('status', 1)
            ->get();
    }


    /**
     * Get the cities pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getCitiesPaginate(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return City::query()
            ->with('province.country')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'priority'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the cities.
     * @param Country $country
     * @return Collection
     */
    public function activeCities(Country $country) :Collection
    {
        return City::query()
            ->where('country_id', $country->id)
            ->where('status', 1)
            ->get();
    }

    /**
     * Get address from city id
     * @param City $city
     * @return JsonResponse
     */
    public function getCityDetails(City $city) :Collection
    {
        return City::query()
            ->with('province.country')
            ->where('id', $city->id)
            ->get();
    }
}
