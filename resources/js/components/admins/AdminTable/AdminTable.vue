<script setup lang="ts">
import type { AdminTableProps, AdminTableEmits, Admin } from '@/types/admin';
import { AdminTableService } from '@/services/AdminTableService';
import AdminActions from '../AdminActions/AdminActions.vue';

const props = withDefaults(defineProps<AdminTableProps>(), {
  loading: false,
});

const emit = defineEmits<AdminTableEmits>();

const handleAdminClick = (admin: Admin) => {
  emit('select', admin);
};

const handleAdminDeleted = (admin: Admin) => {
  emit('delete', admin);
};

const handleAdminRestored = (admin: Admin) => {
  emit('restore', admin);
};

const handleAdminForceDeleted = (admin: Admin) => {
  emit('forceDelete', admin);
};

const handleAdminEdit = (admin: Admin) => {
  emit('edit', admin);
};

const handleAdminResetPassword = (admin: Admin) => {
  emit('adminResetPassword', admin);
};
</script>

<template>
  <div class="w-full">
    <div v-if="props.loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-6 w-6 border-2 border-gray-300 border-t-gray-600"></div>
    </div>
    
    <div v-else-if="props.admins.length === 0" class="text-center py-12">
      <div class="text-gray-500 text-sm">No admins found</div>
    </div>
    
    <div v-else class="overflow-hidden rounded-lg border">
      <table class="min-w-full divide-y divide-border">
        <thead class="bg-muted/50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Admin
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
            v-for="admin in props.admins" 
            :key="admin.id"
            class="hover:bg-muted/50 transition-colors"
            :class="AdminTableService.getAdminStatus(admin).isDeleted ? 'opacity-60' : ''"
          >
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleAdminClick(admin)">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-8 w-8">
                  <div class="h-8 w-8 rounded-full overflow-hidden">
                    <img 
                      v-if="admin.profile_photo?.url" 
                      :src="admin.profile_photo.url" 
                      :alt="admin.name"
                      class="h-full w-full object-cover"
                    />
                    <div 
                      v-else 
                      class="h-full w-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white text-xs font-medium"
                    >
                      {{ AdminTableService.getAdminInitials(admin) }}
                    </div>
                  </div>
                </div>
                <div class="ml-3">
                  <div class="text-sm font-medium text-foreground">{{ admin.name }}</div>
                </div>
              </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleAdminClick(admin)">
              <div class="text-sm text-muted-foreground">{{ admin.email }}</div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleAdminClick(admin)">
              <div class="text-sm text-muted-foreground">
                <span v-if="admin.roles && admin.roles.length > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                  {{ admin.roles[0].name }}
                </span>
                <span v-else class="text-gray-400">No role</span>
              </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleAdminClick(admin)">
              <span 
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="{
                  'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': AdminTableService.getAdminStatus(admin).isVerified && !AdminTableService.getAdminStatus(admin).isDeleted,
                  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': !AdminTableService.getAdminStatus(admin).isVerified && !AdminTableService.getAdminStatus(admin).isDeleted,
                  'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': AdminTableService.getAdminStatus(admin).isDeleted
                }"
              >
                {{ AdminTableService.getAdminStatus(admin).text }}
              </span>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-muted-foreground cursor-pointer" @click="handleAdminClick(admin)">
              {{ AdminTableService.formatDate(admin.created_at) }}
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
              <AdminActions
                :admin="admin"
                @admin-deleted="handleAdminDeleted"
                @admin-restored="handleAdminRestored"
                @admin-force-deleted="handleAdminForceDeleted"
                @admin-edit="handleAdminEdit"
                @admin-reset-password="handleAdminResetPassword"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
