export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface UserListState {
  users: User[];
  loading: boolean;
  error: string | null;
}

// Component-specific types
export interface UserTableProps {
  users: User[];
  loading?: boolean;
}

export interface UserTableEmits {
  (e: 'select', user: User): void;
  (e: 'sort', field: keyof User): void;
}

export interface UserListProps {
  initialUsers?: User[];
}

export interface UserListEmits {
  (e: 'userSelected', user: User): void;
} 