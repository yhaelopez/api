export interface Artist {
  id: number;
  name: string;
  spotify_id?: string;
  popularity?: number;
  followers_count?: number;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
  owner?: {
    id: number;
    name: string;
    email: string;
  };
  profile_photo?: {
    id: number;
    url: string;
    name: string;
    size: number;
    mime_type: string;
  };
}

export interface CreateArtist {
  name: string;
  spotify_id?: string;
  popularity?: number;
  followers_count?: number;
  temp_folder?: string;
}

export interface UpdateArtist {
  name?: string;
  spotify_id?: string;
  popularity?: number;
  followers_count?: number;
  temp_folder?: string;
}

export interface ArtistListState {
  artists: Artist[];
  loading: boolean;
  error: string | null;
}

// Component-specific types
export interface ArtistTableProps {
  artists: Artist[];
  loading?: boolean;
}

export interface ArtistTableEmits {
  (e: 'select', artist: Artist): void;
  (e: 'sort', field: keyof Artist): void;
  (e: 'delete', artist: Artist): void;
  (e: 'restore', artist: Artist): void;
  (e: 'forceDelete', artist: Artist): void;
  (e: 'edit', artist: Artist): void;
}

export interface ArtistListProps {
  initialArtists?: Artist[];
}

export interface ArtistListEmits {
  (e: 'artistSelected', artist: Artist): void;
}
