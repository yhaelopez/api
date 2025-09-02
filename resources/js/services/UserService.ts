import { usersApi, type UsersListParams, type UsersListResponse } from '@/lib/api/users';
import type { User, CreateUser } from '@/types/user';

export interface UserFilters {
  search?: string;
  status?: 'verified' | 'pending';
  sortBy?: keyof User;
  sortDirection?: 'asc' | 'desc';
  withInactive?: boolean;
  onlyInactive?: boolean;
}

export interface UserListOptions {
  page?: number;
  perPage?: number;
  filters?: UserFilters;
}

export class UserService {
  /**
   * Fetch users with optional filtering and pagination
   */
  static async getUsers(options: UserListOptions = {}): Promise<UsersListResponse> {
    const { page = 1, perPage = 15, filters = {} } = options;

    const params: UsersListParams = {
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

    return usersApi.list(params);
  }

  /**
   * Get a single user by ID
   */
  static async getUser(id: number) {
    return usersApi.show(id);
  }

  /**
   * Create a new user
   */
  static async createUser(userData: CreateUser) {
    return usersApi.create(userData);
  }

  /**
   * Update an existing user
   */
  static async updateUser(id: number, userData: Partial<User>) {
    return usersApi.update(id, userData);
  }

  /**
   * Delete a user (soft delete)
   */
  static async deleteUser(id: number) {
    return usersApi.delete(id);
  }

  /**
   * Restore a soft-deleted user
   */
  static async restoreUser(id: number) {
    return usersApi.restore(id);
  }

  /**
   * Force delete a user permanently
   */
  static async forceDeleteUser(id: number) {
    return usersApi.forceDelete(id);
  }

  /**
   * Format user data for display
   */
  static formatUserForDisplay(user: User) {
    return {
      ...user,
      displayName: user.name,
      displayEmail: user.email,
      isVerified: !!user.email_verified_at,
      statusText: user.email_verified_at ? 'Verified' : 'Pending',
      createdAt: new Date(user.created_at).toLocaleDateString(),
    };
  }

  /**
   * Validate user data
   */
  static validateUser(userData: Partial<User>): string[] {
    const errors: string[] = [];

    if (!userData.name?.trim()) {
      errors.push('Name is required');
    }

    if (!userData.email?.trim()) {
      errors.push('Email is required');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(userData.email)) {
      errors.push('Email is invalid');
    }

    return errors;
  }

  /**
   * Check if user is soft deleted
   */
  static isUserDeleted(user: User): boolean {
    return !!user.deleted_at;
  }
} 