<script setup lang="ts">
import { onMounted, ref } from 'vue';
import type { ArtistListEmits, Artist } from '@/types/artist';
import type { User } from '@/types/user';
import { ArtistTable } from '../ArtistTable';
import { ArtistForm } from '../ArtistForm';
import { useArtists } from '@/composables/useArtists';
import { UserService } from '@/services/UserService';
import { Button } from '@/components/ui/button';
import { Pagination } from '@/components/ui/pagination';
import { UserPlus } from 'lucide-vue-next';

const emit = defineEmits<ArtistListEmits>();

const { artists, loading, error, pagination, fetchArtists } = useArtists();
const showCreateForm = ref(false);
const showEditForm = ref(false);
const editingArtist = ref<Artist | null>(null);

// User management
const users = ref<User[]>([]);
const loadingUsers = ref(false);

// Fetch users for owner selection
const fetchUsers = async () => {
  if (loadingUsers.value) return;
  
  try {
    loadingUsers.value = true;
    const response = await UserService.getUsers();
    users.value = response.data;
  } catch (error) {
    console.error('Failed to fetch users:', error);
  } finally {
    loadingUsers.value = false;
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

const handleArtistSelect = (artist: Artist) => {
  emit('artistSelected', artist);
};

const handleArtistDeleted = () => {
  // Refresh artists after deletion, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchArtists({ page: currentPage, perPage: currentPerPage });
};

const handleArtistRestored = () => {
  // Refresh artists after restoration, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchArtists({ page: currentPage, perPage: currentPerPage });
};

const handleArtistForceDeleted = () => {
  // Refresh artists after permanent deletion, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchArtists({ page: currentPage, perPage: currentPerPage });
};

const handleArtistCreated = () => {
  // Refresh artists after creation, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchArtists({ page: currentPage, perPage: currentPerPage });
  showCreateForm.value = false;
};

const handleCreateCancelled = () => {
  showCreateForm.value = false;
};

const openCreateForm = () => {
  showCreateForm.value = true;
};

const handleArtistEdit = (artist: Artist) => {
  editingArtist.value = artist;
  showEditForm.value = true;
};

const handleArtistUpdated = () => {
  // Refresh artists after update, preserving current pagination
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  fetchArtists({ page: currentPage, perPage: currentPerPage });
  showEditForm.value = false;
  editingArtist.value = null;
};

const handleEditCancelled = () => {
  showEditForm.value = false;
  editingArtist.value = null;
};

const handlePageChange = (page: number) => {
  updateUrlWithPage(page);
  fetchArtists({ page, perPage: getCurrentPerPageFromUrl() });
};

const handlePerPageChange = (perPage: number) => {
  updateUrlWithPerPage(perPage);
  fetchArtists({ page: 1, perPage }); // Reset to page 1 when changing per_page
};

onMounted(async () => {
  // Get current values from URL (with sensible defaults)
  const currentPage = getCurrentPageFromUrl();
  const currentPerPage = getCurrentPerPageFromUrl();
  
  // Fetch both artists and users in parallel
  await Promise.all([
    fetchArtists({ page: currentPage, perPage: currentPerPage }),
    fetchUsers()
  ]);
});
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Artists</h1>
      <Button @click="openCreateForm" class="flex items-center gap-2">
        <UserPlus class="h-4 w-4" />
        Create Artist
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
    
    <ArtistTable 
      :artists="artists" 
      :loading="loading"
      @select="handleArtistSelect"
      @delete="handleArtistDeleted"
      @restore="handleArtistRestored"
      @force-delete="handleArtistForceDeleted"
      @edit="handleArtistEdit"
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

    <!-- Create Artist Form Modal -->
    <ArtistForm
      :is-edit-mode="false"
      :open="showCreateForm"
      :users="users"
      :loading-users="loadingUsers"
      @update:open="showCreateForm = $event"
      @artist-created="handleArtistCreated"
      @cancelled="handleCreateCancelled"
    />

    <!-- Edit Artist Form Modal -->
    <ArtistForm
      :is-edit-mode="true"
      :artist="editingArtist"
      :open="showEditForm"
      :users="users"
      :loading-users="loadingUsers"
      @update:open="showEditForm = $event"
      @artist-updated="handleArtistUpdated"
      @cancelled="handleEditCancelled"
    />

  </div>
</template>
