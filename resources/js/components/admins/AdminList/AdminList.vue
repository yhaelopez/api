<script setup lang="ts">
import { onMounted, ref } from 'vue';
import type { AdminListEmits, Admin } from '@/types/admin';
import AdminTable from '../AdminTable/AdminTable.vue';
import AdminForm from '../AdminForm/AdminForm.vue';
import { useAdmins } from '@/composables/useAdmins';
import { Button } from '@/components/ui/button';
import { Pagination } from '@/components/ui/pagination';
import { UserPlus } from 'lucide-vue-next';

const emit = defineEmits<AdminListEmits>();

const { admins, loading, error, pagination, fetchAdmins } = useAdmins();
const showCreateForm = ref(false);
const showEditForm = ref(false);
const editingAdmin = ref<Admin | null>(null);

// Roles state
const roles = ref<Array<{ id: number; name: string }>>([]);
const loadingRoles = ref(false);

// Load roles from API
const loadRoles = async () => {
  try {
    loadingRoles.value = true;
    const response = await fetch('/api/admin/v1/roles?guard=admin');
    const data = await response.json();
    roles.value = data.data;
  } catch (error) {
    console.error('Failed to load roles:', error);
  } finally {
    loadingRoles.value = false;
  }
};

// URL-based pagination
const getCurrentPageFromUrl = (): number => {
  const urlParams = new URLSearchParams(window.location.search);
  const page = urlParams.get('page');
  return page ? parseInt(page, 10) : 1;
};

const getCurrentPerPageFromUrl = (): number => {
  const urlParams = new URLSearchParams(window.location.search);
  const perPage = urlParams.get('per_page');
  return perPage ? parseInt(perPage, 10) : 10;
};

const updateUrlWithPage = (page: number) => {
  const url = new URL(window.location.href);
  url.searchParams.set('page', page.toString());
  window.history.pushState({}, '', url.toString());
};

const updateUrlWithPerPage = (perPage: number) => {
  const url = new URL(window.location.href);
  url.searchParams.set('per_page', perPage.toString());
  // Reset to page 1 when changing per_page
  url.searchParams.set('page', '1');
  window.history.pushState({}, '', url.toString());
};

const handleAdminSelect = (admin: Admin) => {
  emit('adminSelected', admin);
};

const handleAdminDeleted = () => {
  // Refresh admins after deletion, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchAdmins({ page: currentPage, perPage: currentPerPage });
};

const handleAdminRestored = () => {
  // Refresh admins after restoration, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchAdmins({ page: currentPage, perPage: currentPerPage });
};

const handleAdminForceDeleted = () => {
  // Refresh admins after permanent deletion, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchAdmins({ page: currentPage, perPage: currentPerPage });
};

const handleAdminCreated = () => {
  // Refresh admins after creation, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchAdmins({ page: currentPage, perPage: currentPerPage });
  showCreateForm.value = false;
};

const handleCreateCancelled = () => {
  showCreateForm.value = false;
};

const openCreateForm = () => {
  showCreateForm.value = true;
};

const handleAdminEdit = (admin: Admin) => {
  editingAdmin.value = admin;
  showEditForm.value = true;
};

const handleAdminUpdated = () => {
  // Refresh admins after update, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchAdmins({ page: currentPage, perPage: currentPerPage });
  showEditForm.value = false;
  editingAdmin.value = null;
};

const handleEditCancelled = () => {
  showEditForm.value = false;
  editingAdmin.value = null;
};

const handlePageChange = (page: number) => {
  updateUrlWithPage(page);
  fetchAdmins({ page, perPage: getCurrentPerPageFromUrl() });
};

const handlePerPageChange = (perPage: number) => {
  updateUrlWithPerPage(perPage);
  fetchAdmins({ page: 1, perPage }); // Reset to page 1 when changing per_page
};

const handleAdminResetPassword = (admin: Admin) => {
  // Implement password reset logic here
  console.log('Reset password for admin:', admin);
  // You might want to show a confirmation modal or a success message
};

onMounted(() => {
  // Load roles once for both forms
  loadRoles();
  
  // Get current values from URL (with sensible defaults)
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  
  // Fetch admins with the current page and per_page from URL
  fetchAdmins({ page: currentPage, perPage: currentPerPage });
});
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-end items-center">
      <!-- <h1 class="text-2xl font-bold text-gray-900">Admins</h1> -->
      <Button @click="openCreateForm" class="flex items-center gap-2">
        <UserPlus class="h-4 w-4" />
        Create Admin
      </Button>
    </div>
    
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <div class="mt-2 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>
    
    <AdminTable 
      :admins="admins" 
      :loading="loading"
      @select="handleAdminSelect"
      @delete="handleAdminDeleted"
      @restore="handleAdminRestored"
      @force-delete="handleAdminForceDeleted"
      @edit="handleAdminEdit"
      @admin-reset-password="handleAdminResetPassword"
    />

    <!-- Pagination -->
    <div class="border-t pt-4">
      <Pagination
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        :per-page="pagination.per_page"
        :from="pagination.from"
        :to="pagination.to"
        @page-change="handlePageChange"
        @per-page-change="handlePerPageChange"
      />
    </div>

    <!-- Create Admin Form Modal -->
    <AdminForm
      :is-edit-mode="false"
      :open="showCreateForm"
      :roles="roles"
      :loading-roles="loadingRoles"
      @update:open="showCreateForm = $event"
      @admin-created="handleAdminCreated"
      @cancelled="handleCreateCancelled"
    />

    <!-- Edit Admin Form Modal -->
    <AdminForm
      :is-edit-mode="true"
      :admin="editingAdmin || undefined"
      :open="showEditForm"
      :roles="roles"
      :loading-roles="loadingRoles"
      @update:open="showEditForm = $event"
      @admin-updated="handleAdminUpdated"
      @cancelled="handleEditCancelled"
    />

  </div>
</template>
