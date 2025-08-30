import { apiClient, type PaginatedResponse } from '../api';
import type { User, CreateUser } from '@/types/user';

export interface UsersListParams {
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

export type UsersListResponse = PaginatedResponse<User>;

class UsersApi {
  private endpoint = '/v1/users';

  async list(params?: UsersListParams): Promise<UsersListResponse> {
    return apiClient.get<UsersListResponse>(this.endpoint, params);
  }

  async show(id: number): Promise<{ data: User }> {
    return apiClient.get<{ data: User }>(`${this.endpoint}/${id}`);
  }

  async create(data: CreateUser): Promise<{ data: User }> {
    return apiClient.post<{ data: User }>(this.endpoint, data);
  }

  async update(id: number, data: Partial<User>): Promise<{ data: User }> {
    return apiClient.put<{ data: User }>(`${this.endpoint}/${id}`, data);
  }

  async delete(id: number): Promise<{ message: string }> {
    return apiClient.delete<{ message: string }>(`${this.endpoint}/${id}`);
  }

  async restore(id: number): Promise<{ message: string; data: User }> {
    return apiClient.post<{ message: string; data: User }>(`${this.endpoint}/${id}/restore`);
  }

  async forceDelete(id: number): Promise<{ message: string }> {
    return apiClient.delete<{ message: string }>(`${this.endpoint}/${id}/force-delete`);
  }
}

export const usersApi = new UsersApi(); 