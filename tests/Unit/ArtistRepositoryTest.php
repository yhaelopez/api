<?php

namespace Tests\Unit;

use App\Helpers\TestHelper;
use App\Models\Artist;
use App\Repositories\ArtistRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ArtistRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ArtistRepository $artistRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artistRepository = app(ArtistRepository::class);
        // Create permissions and roles for all tests
        TestHelper::createPermissionsAndRoles();
    }

    public function test_can_find_artist_by_id()
    {
        $artist = Artist::factory()->create();

        $foundArtist = $this->artistRepository->find($artist->id);

        $this->assertNotNull($foundArtist);
        $this->assertEquals($artist->id, $foundArtist->id);
    }

    public function test_can_find_artist_with_owner()
    {
        $artist = Artist::factory()->create();

        $foundArtist = $this->artistRepository->findWithOwner($artist->id);

        $this->assertNotNull($foundArtist);
        $this->assertEquals($artist->id, $foundArtist->id);
        $this->assertTrue($foundArtist->relationLoaded('owner'));
    }

    public function test_can_paginate_artists()
    {
        Artist::factory()->count(25)->create();

        /** @var LengthAwarePaginator */
        $paginatedArtists = $this->artistRepository->paginate(1, 15);

        $this->assertEquals(15, $paginatedArtists->count());
        $this->assertEquals(25, $paginatedArtists->total());
        $this->assertEquals(1, $paginatedArtists->currentPage());
    }

    public function test_can_create_artist()
    {
        $owner = TestHelper::createTestUser();
        $artistData = [
            'owner_id' => $owner->id,
            'name' => 'Test Artist',
            'spotify_id' => 'test_spotify_id_123',
            'popularity' => 75,
            'followers_count' => 1000000,
        ];

        $artist = $this->artistRepository->create($artistData);

        $this->assertInstanceOf(Artist::class, $artist);
        $this->assertEquals('Test Artist', $artist->name);
        $this->assertEquals('test_spotify_id_123', $artist->spotify_id);
        $this->assertEquals(75, $artist->popularity);
        $this->assertEquals(1000000, $artist->followers_count);
        $this->assertEquals($owner->id, $artist->owner_id);
    }

    public function test_can_update_artist()
    {
        $artist = Artist::factory()->create();
        $updateData = ['name' => 'Updated Artist Name'];

        $updatedArtist = $this->artistRepository->update($artist, $updateData);

        $this->assertEquals('Updated Artist Name', $updatedArtist->name);
        $this->assertEquals($artist->id, $updatedArtist->id);
    }

    public function test_can_delete_artist()
    {
        $artist = Artist::factory()->create();

        $result = $this->artistRepository->delete($artist);

        $this->assertTrue($result);
        $this->assertSoftDeleted($artist);
    }

    public function test_can_restore_artist()
    {
        $artist = Artist::factory()->create();
        $artist->delete();
        $this->assertSoftDeleted($artist);

        $result = $this->artistRepository->restore($artist);

        $this->assertTrue($result);
        $this->assertDatabaseHas('artists', [
            'id' => $artist->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_force_delete_artist()
    {
        $artist = Artist::factory()->create();
        $artist->delete();
        $this->assertSoftDeleted($artist);

        $result = $this->artistRepository->forceDelete($artist);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('artists', [
            'id' => $artist->id,
        ]);
    }

    public function test_paginate_loads_owner_relationship()
    {
        Artist::factory()->count(5)->create();

        $paginatedArtists = $this->artistRepository->paginate(1, 10);

        foreach ($paginatedArtists->items() as $artist) {
            $this->assertTrue($artist->relationLoaded('owner'));
        }
    }

    public function test_find_returns_null_for_non_existent_artist()
    {
        $foundArtist = $this->artistRepository->find(999999);

        $this->assertNull($foundArtist);
    }

    public function test_find_or_fail_throws_exception_for_non_existent_artist()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->artistRepository->findOrFail(999999);
    }

    public function test_can_create_artist_with_minimal_data()
    {
        $owner = TestHelper::createTestUser();
        $artistData = [
            'owner_id' => $owner->id,
            'name' => 'Minimal Artist',
        ];

        $artist = $this->artistRepository->create($artistData);

        $this->assertInstanceOf(Artist::class, $artist);
        $this->assertEquals('Minimal Artist', $artist->name);
        $this->assertEquals($owner->id, $artist->owner_id);
        $this->assertNull($artist->spotify_id);
        $this->assertNull($artist->popularity);
        $this->assertNull($artist->followers_count);
    }

    public function test_can_update_artist_spotify_data()
    {
        $artist = Artist::factory()->withoutSpotifyData()->create();
        $this->assertNull($artist->spotify_id);
        $this->assertNull($artist->popularity);

        $updateData = [
            'spotify_id' => 'updated_spotify_id',
            'popularity' => 85,
            'followers_count' => 2000000,
        ];

        $updatedArtist = $this->artistRepository->update($artist, $updateData);

        $this->assertEquals('updated_spotify_id', $updatedArtist->spotify_id);
        $this->assertEquals(85, $updatedArtist->popularity);
        $this->assertEquals(2000000, $updatedArtist->followers_count);
    }
}
