import { artistsApi, type ArtistsListParams, type ArtistsListResponse } from '@/lib/api/artists';
import type { Artist, CreateArtist } from '@/types/artist';

export interface ArtistFilters {
  search?: string;
  status?: 'active' | 'inactive';
  sortBy?: keyof Artist;
  sortDirection?: 'asc' | 'desc';
  withInactive?: boolean;
  onlyInactive?: boolean;
}

export interface ArtistListOptions {
  page?: number;
  perPage?: number;
  filters?: ArtistFilters;
}

export class ArtistService {
  /**
   * Fetch artists with optional filtering and pagination
   */
  static async getArtists(options: ArtistListOptions = {}): Promise<ArtistsListResponse> {
    const { page = 1, perPage = 15, filters = {} } = options;

    const params: ArtistsListParams = {
      page,
      per_page: perPage,
    };

    // Add with_inactive filter if specified
    if (filters.withInactive === true) {
      params.with_inactive = true;
    }

    // Add only_inactive filter if specified
    if (filters.onlyInactive === true) {
      params.only_inactive = true;
    }

    // Add search filter
    if (filters.search) {
      params.search = filters.search;
    }

    // Add sorting
    if (filters.sortBy) {
      params.sort_by = filters.sortBy;
      params.sort_direction = filters.sortDirection || 'asc';
    }

    return artistsApi.list(params);
  }

  /**
   * Get a single artist by ID
   */
  static async getArtist(id: number) {
    return artistsApi.show(id);
  }

  /**
   * Create a new artist
   */
  static async createArtist(artistData: CreateArtist) {
    return artistsApi.create(artistData);
  }

  /**
   * Update an existing artist
   */
  static async updateArtist(id: number, artistData: Partial<Artist>) {
    return artistsApi.update(id, artistData);
  }

  /**
   * Delete an artist (soft delete)
   */
  static async deleteArtist(id: number) {
    return artistsApi.delete(id);
  }

  /**
   * Restore a soft-deleted artist
   */
  static async restoreArtist(id: number) {
    return artistsApi.restore(id);
  }

  /**
   * Force delete an artist permanently
   */
  static async forceDeleteArtist(id: number) {
    return artistsApi.forceDelete(id);
  }

  /**
   * Format artist data for display
   */
  static formatArtistForDisplay(artist: Artist) {
    return {
      ...artist,
      displayName: artist.name,
      isActive: !artist.deleted_at,
      statusText: artist.deleted_at ? 'Deleted' : 'Active',
      createdAt: new Date(artist.created_at).toLocaleDateString(),
      popularityText: artist.popularity ? `${artist.popularity}%` : 'Unknown',
      followersText: artist.followers_count ? artist.followers_count.toLocaleString() : 'Unknown',
    };
  }

  /**
   * Validate artist data
   */
  static validateArtist(artistData: Partial<Artist>): string[] {
    const errors: string[] = [];

    if (!artistData.name?.trim()) {
      errors.push('Name is required');
    }

    if (artistData.popularity !== undefined && (artistData.popularity < 0 || artistData.popularity > 100)) {
      errors.push('Popularity must be between 0 and 100');
    }

    if (artistData.followers_count !== undefined && artistData.followers_count < 0) {
      errors.push('Followers count cannot be negative');
    }

    return errors;
  }

  /**
   * Check if artist is soft deleted
   */
  static isArtistDeleted(artist: Artist): boolean {
    return !!artist.deleted_at;
  }

  /**
   * Get artist initials for avatar
   */
  static getArtistInitials(artist: Artist): string {
    return artist.name.charAt(0).toUpperCase();
  }
}
