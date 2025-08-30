<script setup lang="ts">
import { onMounted, ref } from 'vue';
import type { UserListProps, UserListEmits, User } from '@/types/user';
import { UserTable } from '../UserTable';
import { UserForm } from '../UserForm';
import { useUsers } from '@/composables/useUsers';
import { Button } from '@/components/ui/button';
import { UserPlus } from 'lucide-vue-next';

const props = withDefaults(defineProps<UserListProps>(), {
  initialUsers: () => [],
});

const emit = defineEmits<UserListEmits>();

const { users, loading, error, fetchUsers } = useUsers();
const showCreateForm = ref(false);
const showEditForm = ref(false);
const editingUser = ref<User | null>(null);


const handleUserSelect = (user: User) => {
  emit('userSelected', user);
};

const handleUserDeleted = () => {
  // Refresh the user list to show updated status
  fetchUsers();
};

const handleUserRestored = () => {
  // Refresh the user list to show updated status
  fetchUsers();
};

const handleUserForceDeleted = () => {
  // Refresh the user list to remove the permanently deleted user
  fetchUsers();
};

const handleUserCreated = () => {
  // Refresh the user list to show the new user
  fetchUsers();
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
  fetchUsers();
  showEditForm.value = false;
  editingUser.value = null;
};

const handleEditCancelled = () => {
  showEditForm.value = false;
  editingUser.value = null;
};

onMounted(() => {
  if (props.initialUsers.length === 0) {
    fetchUsers();
  }
});
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Users</h1>
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
    />

    <!-- Create User Form Modal -->
    <div
      v-if="showCreateForm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="handleCreateCancelled"
    >
      <div @click.stop>
        <UserForm
          :is-edit-mode="false"
          @user-created="handleUserCreated"
          @cancelled="handleCreateCancelled"
        />
      </div>
    </div>

    <!-- Edit User Form Modal -->
    <div
      v-if="showEditForm && editingUser"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="handleEditCancelled"
    >
      <div @click.stop>
        <UserForm
          :is-edit-mode="true"
          :user="editingUser"
          @user-updated="handleUserUpdated"
          @cancelled="handleEditCancelled"
        />
      </div>
    </div>

  </div>
</template>
