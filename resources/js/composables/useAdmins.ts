import { ref } from 'vue';
import { AdminService } from '@/services/AdminService';
import type { Admin, AdminListOptions } from '@/types/admin';

export function useAdmins() {
  const admins = ref<Admin[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0,
  });

  const fetchAdmins = async (options: AdminListOptions = {}) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await AdminService.getAdmins({
        page: options.page || 1,
        perPage: options.perPage || 10,
        filters: {
          ...options.filters,
          withInactive: true,
        },
      });

      admins.value = response.data;
      pagination.value = {
        current_page: response.meta.current_page,
        last_page: response.meta.last_page,
        per_page: response.meta.per_page,
        total: response.meta.total,
        from: response.meta.from,
        to: response.meta.to,
      };
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch admins';
      console.error('Error fetching admins:', err);
    } finally {
      loading.value = false;
    }
  };

  return {
    admins,
    loading,
    error,
    pagination,
    fetchAdmins,
  };
}
