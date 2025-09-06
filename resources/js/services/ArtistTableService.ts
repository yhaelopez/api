import type { Artist } from '@/types/artist';

export interface ArtistTableService {
  formatDate(dateString: string): string;
  getArtistInitials(artist: Artist): string;
  getArtistStatus(artist: Artist): { text: string; isActive: boolean; isDeleted: boolean };
  sortArtists(artists: Artist[], field: keyof Artist, direction: 'asc' | 'desc'): Artist[];
}

export class ArtistTableServiceImpl implements ArtistTableService {
  formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString();
  }

  getArtistInitials(artist: Artist): string {
    return artist.name.charAt(0).toUpperCase();
  }

  getArtistStatus(artist: Artist): { text: string; isActive: boolean; isDeleted: boolean } {
    const isDeleted = !!artist.deleted_at;
    
    if (isDeleted) {
      return {
        text: 'Deleted',
        isActive: false,
        isDeleted: true,
      };
    }
    
    return {
      text: 'Active',
      isActive: true,
      isDeleted: false,
    };
  }

  sortArtists(artists: Artist[], field: keyof Artist, direction: 'asc' | 'desc'): Artist[] {
    return [...artists].sort((a, b) => {
      const aValue = a[field];
      const bValue = b[field];

      if (aValue === bValue) return 0;
      if (aValue === undefined) return 1;
      if (bValue === undefined) return -1;

      const comparison = aValue < bValue ? -1 : 1;
      return direction === 'asc' ? comparison : -comparison;
    });
  }

}

export const artistTableService = new ArtistTableServiceImpl();
