<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { UserList } from '@/components/users/UserList';
import type { User } from '@/types/user';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Users',
    href: '/users',
  },
];

const handleUserSelected = (user: User) => {
  // TODO: Navigate to user detail page
  console.log('User selected:', user);
};
</script>

<template>
  <Head title="Users" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <UserList 
        @user-selected="handleUserSelected"
      />
    </div>
  </AppLayout>
</template> 