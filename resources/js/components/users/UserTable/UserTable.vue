<script setup lang="ts">
import type { UserTableProps, UserTableEmits, User } from '@/types/user';
import { userTableService } from '@/services/UserTableService';
import UserActions from '@/components/users/UserActions/UserActions.vue';

const props = withDefaults(defineProps<UserTableProps>(), {
  loading: false,
});

const emit = defineEmits<UserTableEmits>();

const handleUserClick = (user: User) => {
  emit('select', user);
};

const handleUserDeleted = (user: User) => {
  emit('delete', user);
};

const handleUserRestored = (user: User) => {
  emit('restore', user);
};

const handleUserForceDeleted = (user: User) => {
  emit('forceDelete', user);
};

const handleUserEdit = (user: User) => {
  emit('edit', user);
};
</script>

<template>
  <div class="w-full">
    <div v-if="props.loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-6 w-6 border-2 border-gray-300 border-t-gray-600"></div>
    </div>
    
    <div v-else-if="props.users.length === 0" class="text-center py-12">
      <div class="text-gray-500 text-sm">No users found</div>
    </div>
    
    <div v-else class="overflow-hidden rounded-lg border">
      <table class="min-w-full divide-y divide-border">
        <thead class="bg-muted/50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              User
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Email
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Role
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Status
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Created
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-background divide-y divide-border">
          <tr 
            v-for="user in props.users" 
            :key="user.id"
            class="hover:bg-muted/50 transition-colors"
            :class="userTableService.getUserStatus(user).isDeleted ? 'opacity-60' : ''"
          >
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleUserClick(user)">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-8 w-8">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-medium">
                    {{ userTableService.getUserInitials(user) }}
                  </div>
                </div>
                <div class="ml-3">
                  <div class="text-sm font-medium text-foreground">{{ user.name }}</div>
                </div>
              </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleUserClick(user)">
              <div class="text-sm text-muted-foreground">{{ user.email }}</div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleUserClick(user)">
              <div class="text-sm text-muted-foreground">
                <span v-if="user.roles && user.roles.length > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                  {{ user.roles[0].name }}
                </span>
                <span v-else class="text-gray-400">No role</span>
              </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleUserClick(user)">
              <span 
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="{
                  'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': userTableService.getUserStatus(user).isVerified && !userTableService.getUserStatus(user).isDeleted,
                  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': !userTableService.getUserStatus(user).isVerified && !userTableService.getUserStatus(user).isDeleted,
                  'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': userTableService.getUserStatus(user).isDeleted
                }"
              >
                {{ userTableService.getUserStatus(user).text }}
              </span>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-muted-foreground cursor-pointer" @click="handleUserClick(user)">
              {{ userTableService.formatDate(user.created_at) }}
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
              <UserActions
                :user="user"
                @user-deleted="handleUserDeleted"
                @user-restored="handleUserRestored"
                @user-force-deleted="handleUserForceDeleted"
                @user-edit="handleUserEdit"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
