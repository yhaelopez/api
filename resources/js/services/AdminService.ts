import { adminsApi, type AdminsListParams, type AdminsListResponse } from '@/lib/api/admins';
import type { Admin, CreateAdmin, AdminFilters } from '@/types/admin';

export interface AdminListOptions {
  page?: number;
  perPage?: number;
  filters?: AdminFilters;
}

export class AdminService {
  /**
   * Fetch admins with optional filtering and pagination
   */
  static async getAdmins(options: AdminListOptions = {}): Promise<AdminsListResponse> {
    const { page = 1, perPage = 15, filters = {} } = options;

    const params: AdminsListParams = {
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

    return adminsApi.list(params);
  }

  /**
   * Get a single admin by ID
   */
  static async getAdmin(id: number) {
    return adminsApi.show(id);
  }

  /**
   * Create a new admin
   */
  static async createAdmin(adminData: CreateAdmin) {
    return adminsApi.create(adminData);
  }

  /**
   * Update an existing admin
   */
  static async updateAdmin(id: number, adminData: Partial<Admin>) {
    return adminsApi.update(id, adminData);
  }

  /**
   * Delete an admin (soft delete)
   */
  static async deleteAdmin(id: number) {
    return adminsApi.delete(id);
  }

  /**
   * Restore a soft-deleted admin
   */
  static async restoreAdmin(id: number) {
    return adminsApi.restore(id);
  }

  /**
   * Force delete an admin permanently
   */
  static async forceDeleteAdmin(id: number) {
    return adminsApi.forceDelete(id);
  }

  /**
   * Send password reset link to admin
   */
  static async sendPasswordResetLink(id: number) {
    return adminsApi.sendPasswordReset(id);
  }

  /**
   * Remove admin profile photo
   */
  static async removeProfilePhoto(id: number) {
    return adminsApi.removeProfilePhoto(id);
  }

  /**
   * Format admin data for display
   */
  static formatAdminForDisplay(admin: Admin) {
    return {
      ...admin,
      displayName: admin.name,
      displayEmail: admin.email,
      isVerified: !!admin.email_verified_at,
      statusText: admin.email_verified_at ? 'Verified' : 'Pending',
      createdAt: new Date(admin.created_at).toLocaleDateString(),
    };
  }

  /**
   * Validate admin data
   */
  static validateAdmin(adminData: Partial<Admin>): string[] {
    const errors: string[] = [];

    if (!adminData.name?.trim()) {
      errors.push('Name is required');
    }

    if (!adminData.email?.trim()) {
      errors.push('Email is required');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(adminData.email)) {
      errors.push('Email is invalid');
    }

    return errors;
  }

  /**
   * Check if admin is soft deleted
   */
  static isAdminDeleted(admin: Admin): boolean {
    return !!admin.deleted_at;
  }
}
