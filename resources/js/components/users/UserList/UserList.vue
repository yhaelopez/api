<script setup lang="ts">
import { onMounted, ref } from 'vue';
import type { UserListEmits, User } from '@/types/user';
import { UserTable } from '../UserTable';
import { UserForm } from '../UserForm';
import { useUsers } from '@/composables/useUsers';
import { Button } from '@/components/ui/button';
import { Pagination } from '@/components/ui/pagination';
import { UserPlus } from 'lucide-vue-next';

const emit = defineEmits<UserListEmits>();

const { users, loading, error, pagination, fetchUsers } = useUsers();
const showCreateForm = ref(false);
const showEditForm = ref(false);
const editingUser = ref<User | null>(null);

// Roles state
const roles = ref<Array<{ id: number; name: string }>>([]);
const loadingRoles = ref(false);

// Load roles from API
const loadRoles = async () => {
  try {
    loadingRoles.value = true;
    const response = await fetch('/api/admin/v1/roles');
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

const handleUserSelect = (user: User) => {
  emit('userSelected', user);
};

const handleUserDeleted = () => {
  // Refresh users after deletion, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchUsers({ page: currentPage, perPage: currentPerPage });
};

const handleUserRestored = () => {
  // Refresh users after restoration, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchUsers({ page: currentPage, perPage: currentPerPage });
};

const handleUserForceDeleted = () => {
  // Refresh users after permanent deletion, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchUsers({ page: currentPage, perPage: currentPerPage });
};

const handleUserCreated = () => {
  // Refresh users after creation, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchUsers({ page: currentPage, perPage: currentPerPage });
  showCreateForm.value = false;
};

const handleCreateCancelled = () => {
  showCreateForm.value = false;
};

const openCreateForm = () => {
  showCreateForm.value = true;
};

const handleUserEdit = (user: User) => {
  editingUser.value = user;
  showEditForm.value = true;
};

const handleUserUpdated = () => {
  // Refresh users after update, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchUsers({ page: currentPage, perPage: currentPerPage });
  showEditForm.value = false;
  editingUser.value = null;
};

const handleEditCancelled = () => {
  showEditForm.value = false;
  editingUser.value = null;
};

const handlePageChange = (page: number) => {
  updateUrlWithPage(page);
  fetchUsers({ page, perPage: getCurrentPerPageFromUrl() });
};

const handlePerPageChange = (perPage: number) => {
  updateUrlWithPerPage(perPage);
  fetchUsers({ page: 1, perPage }); // Reset to page 1 when changing per_page
};

const handleUserResetPassword = (user: User) => {
  // Implement password reset logic here
  console.log('Reset password for user:', user);
  // You might want to show a confirmation modal or a success message
};

onMounted(() => {
  // Load roles once for both forms
  loadRoles();
  
  // Get current values from URL (with sensible defaults)
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  
  // Fetch users with the current page and per_page from URL
  fetchUsers({ page: currentPage, perPage: currentPerPage });
});
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-end items-center">
      <!-- <h1 class="text-2xl font-bold text-gray-900">Users</h1> -->
      <Button @click="openCreateForm" class="flex items-center gap-2">
        <UserPlus class="h-4 w-4" />
        Create User
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
    
    <UserTable 
      :users="users" 
      :loading="loading"
      @select="handleUserSelect"
      @delete="handleUserDeleted"
      @restore="handleUserRestored"
      @force-delete="handleUserForceDeleted"
      @edit="handleUserEdit"
      @user-reset-password="handleUserResetPassword"
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

    <!-- Create User Form Modal -->
    <UserForm
      :is-edit-mode="false"
      :open="showCreateForm"
      :roles="roles"
      :loading-roles="loadingRoles"
      @update:open="showCreateForm = $event"
      @user-created="handleUserCreated"
      @cancelled="handleCreateCancelled"
    />

    <!-- Edit User Form Modal -->
    <UserForm
      :is-edit-mode="true"
      :user="editingUser"
      :open="showEditForm"
      :roles="roles"
      :loading-roles="loadingRoles"
      @update:open="showEditForm = $event"
      @user-updated="handleUserUpdated"
      @cancelled="handleEditCancelled"
    />

  </div>
</template>
