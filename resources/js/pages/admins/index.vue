<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import AdminList from '@/components/admins/AdminList/AdminList.vue';
import type { Admin } from '@/types/admin';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Admins',
    href: '/admins',
  },
];

const handleAdminSelected = (admin: Admin) => {
  // TODO: Navigate to admin detail page
  console.log('Admin selected:', admin);
};
</script>

<template>
  <Head title="Admins" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <AdminList 
        @admin-selected="handleAdminSelected"
      />
    </div>
  </AppLayout>
</template>
