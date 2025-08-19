<script setup lang="ts">
import type { UserTableProps, UserTableEmits, User } from '@/types/user';
import { userTableService } from '@/services/UserTableService';

const props = withDefaults(defineProps<UserTableProps>(), {
  loading: false,
});

const emit = defineEmits<UserTableEmits>();

const handleUserClick = (user: User) => {
  emit('select', user);
};
</script>

<template>
  <div class="w-full">
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-6 w-6 border-2 border-gray-300 border-t-gray-600"></div>
    </div>
    
    <div v-else-if="users.length === 0" class="text-center py-12">
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
              Status
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Created
            </th>
          </tr>
        </thead>
        <tbody class="bg-background divide-y divide-border">
          <tr 
            v-for="user in users" 
            :key="user.id"
            class="hover:bg-muted/50 transition-colors cursor-pointer"
            @click="handleUserClick(user)"
          >
            <td class="px-4 py-4 whitespace-nowrap">
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
            <td class="px-4 py-4 whitespace-nowrap">
              <div class="text-sm text-muted-foreground">{{ user.email }}</div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                           <span 
               class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
               :class="userTableService.getUserStatus(user).isVerified
                 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' 
                 : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'"
             >
               {{ userTableService.getUserStatus(user).text }}
             </span>
            </td>
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-muted-foreground">
               {{ userTableService.formatDate(user.created_at) }}
             </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
