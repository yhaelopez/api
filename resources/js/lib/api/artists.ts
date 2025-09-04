import { apiClient, type PaginatedResponse } from '../api';
import type { Artist, CreateArtist } from '@/types/artist';

export interface ArtistsListParams {
  page?: number;
  per_page?: number;
  search?: string;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
  with_inactive?: boolean;
  only_inactive?: boolean;
  deleted_from?: string;
  deleted_to?: string;
}

export type ArtistsListResponse = PaginatedResponse<Artist>;

class ArtistsApi {
  private endpoint = '/v1/artists';

  async list(params?: ArtistsListParams): Promise<ArtistsListResponse> {
    return apiClient.get<ArtistsListResponse>(this.endpoint, params);
  }

  async show(id: number): Promise<{ data: Artist }> {
    return apiClient.get<{ data: Artist }>(`${this.endpoint}/${id}`);
  }

  async create(data: CreateArtist): Promise<{ data: Artist }> {
    return apiClient.post<{ data: Artist }>(this.endpoint, data);
  }

  async update(id: number, data: Partial<Artist>): Promise<{ data: Artist }> {
    return apiClient.put<{ data: Artist }>(`${this.endpoint}/${id}`, data);
  }

  async delete(id: number): Promise<{ message: string }> {
    return apiClient.delete<{ message: string }>(`${this.endpoint}/${id}`);
  }

  async restore(id: number): Promise<{ message: string; data: Artist }> {
    return apiClient.post<{ message: string; data: Artist }>(`${this.endpoint}/${id}/restore`);
  }

  async forceDelete(id: number): Promise<{ message: string }> {
    return apiClient.delete<{ message: string }>(`${this.endpoint}/${id}/force-delete`);
  }
}

export const artistsApi = new ArtistsApi();
