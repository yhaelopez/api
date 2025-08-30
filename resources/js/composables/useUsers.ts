import { ref, computed } from 'vue';
import { UserService, type UserListOptions } from '@/services/UserService';
import type { User } from '@/types/user';

export function useUsers() {
  const users = ref<User[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
  });

  const hasUsers = computed(() => users.value.length > 0);
  const isEmpty = computed(() => !loading.value && users.value.length === 0);

  const fetchUsers = async (options?: UserListOptions) => {
    loading.value = true;
    error.value = null;

    try {
      // Always include with_inactive: true for UserList view
      const userOptions: UserListOptions = {
        ...options,
        filters: {
          ...options?.filters,
          withInactive: true,
        },
      };
      
      const response = await UserService.getUsers(userOptions);
      users.value = response.data;
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
        from: response.from,
        to: response.to,
      };
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load users';
      console.error('Error fetching users:', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchUser = async (id: number) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await UserService.getUser(id);
      return response.data;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load user';
      console.error('Error fetching user:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const createUser = async (userData: Partial<User>) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await UserService.createUser(userData);
      return response.data;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to create user';
      console.error('Error creating user:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateUser = async (id: number, userData: Partial<User>) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await UserService.updateUser(id, userData);
      return response.data;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to update user';
      console.error('Error updating user:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const deleteUser = async (id: number) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await UserService.deleteUser(id);
      return response.message;
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to delete user';
      console.error('Error deleting user:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const clearError = () => {
    error.value = null;
  };

  return {
    // State
    users,
    loading,
    error,
    pagination,
    hasUsers,
    isEmpty,

    // Actions
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    clearError,
  };
} 