<?php

namespace Domain\Address\Repositories\Contracts;

use Core\Http\Requests\TableRequest;
use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IAddressRepository.
 */
interface IAddressRepository
{
    /**
     * Get the countrys pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getCountriesPaginate(TableRequest $request) :LengthAwarePaginator;
    /**
     * Get the countrys.
     * @return Collection
     */
    public function activeCountries() :Collection;

    /**
     * Get the areas pagination.
     * @param City $city
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getAreasPaginate(City $city, TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the areas.
     *
     * @param City $city
     * @return Collection
     */
    public function activeAreas(City $city) :Collection;


    /**
     * Get the cites pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getCitiesPaginate(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the cities.
     * @param Country $country
     * @return Collection
     */
    public function activeCities(Country $country) :Collection;

    /**
     * Get address from city id
     * @param City $city
     * @return Collection
     */
    public function getCityDetails(City $city) :Collection;


}
