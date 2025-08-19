<script setup lang="ts">
import { onMounted } from 'vue';
import type { UserListProps, UserListEmits, User } from '@/types/user';
import { UserTable } from '../UserTable';
import { useUsers } from '@/composables/useUsers';

const props = withDefaults(defineProps<UserListProps>(), {
  initialUsers: () => [],
});

const emit = defineEmits<UserListEmits>();

const { users, loading, error, fetchUsers } = useUsers();

const handleUserSelect = (user: User) => {
  emit('userSelected', user);
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
    />
  </div>
</template>
