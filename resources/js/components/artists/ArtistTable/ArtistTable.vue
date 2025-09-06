<script setup lang="ts">
import type { ArtistTableProps, ArtistTableEmits, Artist } from '@/types/artist';
import { artistTableService } from '@/services/ArtistTableService';
import ArtistActions from '@/components/artists/ArtistActions/ArtistActions.vue';

const props = withDefaults(defineProps<ArtistTableProps>(), {
  loading: false,
});

const emit = defineEmits<ArtistTableEmits>();

const handleArtistClick = (artist: Artist) => {
  emit('select', artist);
};

const handleArtistDeleted = (artist: Artist) => {
  emit('delete', artist);
};

const handleArtistRestored = (artist: Artist) => {
  emit('restore', artist);
};

const handleArtistForceDeleted = (artist: Artist) => {
  emit('forceDelete', artist);
};

const handleArtistEdit = (artist: Artist) => {
  emit('edit', artist);
};
</script>

<template>
  <div class="w-full">
    <div v-if="props.loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-6 w-6 border-2 border-gray-300 border-t-gray-600"></div>
    </div>
    
    <div v-else-if="props.artists.length === 0" class="text-center py-12">
      <div class="text-gray-500 text-sm">No artists found</div>
    </div>
    
    <div v-else class="overflow-hidden rounded-lg border">
      <table class="min-w-full divide-y divide-border">
        <thead class="bg-muted/50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Artist
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Spotify ID
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
            v-for="artist in props.artists" 
            :key="artist.id"
            class="hover:bg-muted/50 transition-colors"
            :class="artistTableService.getArtistStatus(artist).isDeleted ? 'opacity-60' : ''"
          >
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleArtistClick(artist)">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-8 w-8">
                  <div class="h-8 w-8 rounded-full overflow-hidden">
                    <img 
                      v-if="artist.profile_photo?.url" 
                      :src="artist.profile_photo.url" 
                      :alt="artist.name"
                      class="h-full w-full object-cover"
                    />
                    <div 
                      v-else 
                      class="h-full w-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white text-xs font-medium"
                    >
                      {{ artistTableService.getArtistInitials(artist) }}
                    </div>
                  </div>
                </div>
                <div class="ml-3">
                  <div class="text-sm font-medium text-foreground">{{ artist.name }}</div>
                </div>
              </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleArtistClick(artist)">
              <div class="text-sm text-muted-foreground">
                <span v-if="artist.spotify_id" class="font-mono text-xs bg-gray-100 px-2 py-1 rounded dark:bg-gray-800">
                  {{ artist.spotify_id }}
                </span>
                <span v-else class="text-gray-400">Not linked</span>
              </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap cursor-pointer" @click="handleArtistClick(artist)">
              <span 
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="{
                  'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': artistTableService.getArtistStatus(artist).isActive,
                  'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': artistTableService.getArtistStatus(artist).isDeleted
                }"
              >
                {{ artistTableService.getArtistStatus(artist).text }}
              </span>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-muted-foreground cursor-pointer" @click="handleArtistClick(artist)">
              {{ artistTableService.formatDate(artist.created_at) }}
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
              <ArtistActions
                :artist="artist"
                @artist-deleted="handleArtistDeleted"
                @artist-restored="handleArtistRestored"
                @artist-force-deleted="handleArtistForceDeleted"
                @artist-edit="handleArtistEdit"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
