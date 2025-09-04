import { ref, computed } from 'vue';
import { ArtistService, type ArtistListOptions } from '@/services/ArtistService';
import type { Artist } from '@/types/artist';

export function useArtists() {
  const artists = ref<Artist[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
  });

  const hasArtists = computed(() => artists.value.length > 0);
  const isEmpty = computed(() => !loading.value && artists.value.length === 0);

  const fetchArtists = async (options: ArtistListOptions = {}) => {
    loading.value = true;
    error.value = null;

    try {
      // Always include with_inactive: true for ArtistList view
      const artistOptions: ArtistListOptions = {
        ...options,
        filters: {
          ...options?.filters,
          withInactive: true,
        },
      };
      
      const response = await ArtistService.getArtists(artistOptions);
      
      artists.value = response.data;
      pagination.value = {
        current_page: response.meta.current_page,
        last_page: response.meta.last_page,
        per_page: response.meta.per_page,
        total: response.meta.total,
        from: response.meta.from || 0,
        to: response.meta.to || 0,
      };
    } catch (err) {
      console.error('Failed to fetch artists:', err);
      error.value = err instanceof Error ? err.message : 'Failed to fetch artists';
      artists.value = [];
    } finally {
      loading.value = false;
    }
  };

  const createArtist = async (artistData: Parameters<typeof ArtistService.createArtist>[0]) => {
    try {
      const response = await ArtistService.createArtist(artistData);
      return response;
    } catch (error) {
      console.error('Failed to create artist:', error);
      throw error;
    }
  };

  const updateArtist = async (id: number, artistData: Parameters<typeof ArtistService.updateArtist>[1]) => {
    try {
      const response = await ArtistService.updateArtist(id, artistData);
      return response;
    } catch (error) {
      console.error('Failed to update artist:', error);
      throw error;
    }
  };

  const deleteArtist = async (id: number) => {
    try {
      const response = await ArtistService.deleteArtist(id);
      return response;
    } catch (error) {
      console.error('Failed to delete artist:', error);
      throw error;
    }
  };

  const restoreArtist = async (id: number) => {
    try {
      const response = await ArtistService.restoreArtist(id);
      return response;
    } catch (error) {
      console.error('Failed to restore artist:', error);
      throw error;
    }
  };

  const forceDeleteArtist = async (id: number) => {
    try {
      const response = await ArtistService.forceDeleteArtist(id);
      return response;
    } catch (error) {
      console.error('Failed to force delete artist:', error);
      throw error;
    }
  };

  const clearError = () => {
    error.value = null;
  };

  return {
    // State
    artists,
    loading,
    error,
    pagination,
    hasArtists,
    isEmpty,

    // Actions
    fetchArtists,
    createArtist,
    updateArtist,
    deleteArtist,
    restoreArtist,
    forceDeleteArtist,
    clearError,
  };
}
