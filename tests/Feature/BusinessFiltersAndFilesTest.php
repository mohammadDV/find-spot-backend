<?php

namespace Tests\Feature;

use Domain\Business\Models\Business;
use Domain\Business\Models\Filter;
use Domain\Business\Models\File;
use Domain\User\Models\User;
use Domain\Address\Models\Country;
use Domain\Address\Models\City;
use Domain\Address\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BusinessFiltersAndFilesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected $filters;
    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

                // Create a test user
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'nickname' => 'testuser',
            'customer_number' => 'CUST001',
            'role_id' => 2,
            'status' => 1,
            'email' => 'test@example.com',
            'mobile' => '09123456789',
            'password' => bcrypt('password'),
            'level' => 0,
        ]);

                // Create required address records
        $country = Country::create(['title' => 'Test Country']);
        $city = City::create(['title' => 'Test City', 'country_id' => $country->id]);
        $area = Area::create(['title' => 'Test Area', 'city_id' => $city->id]);

        // Create some test filters
        $this->filters = collect([
            Filter::create(['title' => 'Filter 1', 'status' => 1]),
            Filter::create(['title' => 'Filter 2', 'status' => 1]),
            Filter::create(['title' => 'Filter 3', 'status' => 1]),
        ]);

        // Create a test business
        $this->business = Business::create([
            'title' => 'Test Business',
            'lat' => '35.6892',
            'long' => '51.3890',
            'start_amount' => 100.00,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'country_id' => $country->id,
            'city_id' => $city->id,
            'area_id' => $area->id,
        ]);
    }

    #[Test]
    public function it_can_attach_filters_to_business()
    {
        $filterIds = $this->filters->pluck('id')->toArray();

        $this->business->filters()->attach($filterIds);

        $this->assertCount(3, $this->business->filters);
        $this->assertTrue($this->business->filters->contains($this->filters->first()));
    }

    #[Test]
    public function it_can_create_files_for_business()
    {
        $fileData = [
            'path' => 'test/path/image.jpg',
            'type' => 'image',
            'status' => 1,
        ];

        $file = $this->business->files()->create($fileData);

        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals($this->business->id, $file->business_id);
        $this->assertEquals('image', $file->type);
        $this->assertEquals('test/path/image.jpg', $file->path);
    }

    #[Test]
    public function it_can_sync_filters()
    {
        // Attach initial filters
        $this->business->filters()->attach([$this->filters[0]->id, $this->filters[1]->id]);

        // Sync with different filters
        $this->business->filters()->sync([$this->filters[1]->id, $this->filters[2]->id]);

        $this->assertCount(2, $this->business->filters);
        $this->assertTrue($this->business->filters->contains($this->filters[1]));
        $this->assertTrue($this->business->filters->contains($this->filters[2]));
        $this->assertFalse($this->business->filters->contains($this->filters[0]));
    }

    #[Test]
    public function it_can_load_filters_and_files_relationships()
    {
        // Attach filters and create files
        $this->business->filters()->attach($this->filters->pluck('id')->toArray());
        $this->business->files()->create([
            'path' => 'test/file.jpg',
            'type' => 'image',
            'status' => 1,
        ]);

        // Reload with relationships
        $businessWithRelations = Business::with(['filters', 'files'])->find($this->business->id);

        $this->assertTrue($businessWithRelations->relationLoaded('filters'));
        $this->assertTrue($businessWithRelations->relationLoaded('files'));
        $this->assertCount(3, $businessWithRelations->filters);
        $this->assertCount(1, $businessWithRelations->files);
    }
}
