<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArtistList } from '@/components/artists/ArtistList';
import type { Artist } from '@/types/artist';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Artists',
    href: '/artists',
  },
];

const handleArtistSelected = (artist: Artist) => {
  // TODO: Navigate to artist detail page
  console.log('Artist selected:', artist);
};
</script>

<template>
  <Head title="Artists" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <ArtistList 
        @artist-selected="handleArtistSelected"
      />
    </div>
  </AppLayout>
</template>
