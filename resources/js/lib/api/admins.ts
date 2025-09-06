import { apiClient, type PaginatedResponse } from '../api';
import type { Admin, CreateAdmin, UpdateAdmin } from '@/types/admin';

export interface AdminsListParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: 'verified' | 'pending';
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
  with_inactive?: boolean;
  only_inactive?: boolean;
}

export type AdminsListResponse = PaginatedResponse<Admin>;

export const adminsApi = {
  /**
   * Get paginated list of admins
   */
  list: (params: AdminsListParams = {}): Promise<AdminsListResponse> => {
    return apiClient.get<AdminsListResponse>('/v1/admins', params);
  },

  /**
   * Get a single admin by ID
   */
  show: (id: number): Promise<{ data: Admin }> => {
    return apiClient.get<{ data: Admin }>(`/v1/admins/${id}`);
  },

  /**
   * Create a new admin
   */
  create: (admin: CreateAdmin): Promise<{ data: Admin }> => {
    return apiClient.post<{ data: Admin }>('/v1/admins', admin);
  },

  /**
   * Update an existing admin
   */
  update: (id: number, admin: UpdateAdmin): Promise<{ data: Admin }> => {
    return apiClient.put<{ data: Admin }>(`/v1/admins/${id}`, admin);
  },

  /**
   * Delete an admin (soft delete)
   */
  delete: (id: number): Promise<{ message: string }> => {
    return apiClient.delete<{ message: string }>(`/v1/admins/${id}`);
  },

  /**
   * Restore a soft-deleted admin
   */
  restore: (id: number): Promise<{ data: Admin }> => {
    return apiClient.post<{ data: Admin }>(`/v1/admins/${id}/restore`);
  },

  /**
   * Force delete an admin permanently
   */
  forceDelete: (id: number): Promise<{ message: string }> => {
    return apiClient.delete<{ message: string }>(`/v1/admins/${id}/force-delete`);
  },

  /**
   * Remove admin profile photo
   */
  removeProfilePhoto: (id: number): Promise<{ message: string }> => {
    return apiClient.delete<{ message: string }>(`/v1/admins/${id}/profile-photo`);
  },

  /**
   * Send password reset link to admin
   */
  sendPasswordReset: (id: number): Promise<{ message: string }> => {
    return apiClient.post<{ message: string }>(`/v1/admins/${id}/send-password-reset`);
  },
};
