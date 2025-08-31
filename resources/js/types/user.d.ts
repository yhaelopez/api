export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
  roles?: Array<{ id: number; name: string }>;
}

export interface CreateUser {
  name: string;
  email: string;
  password: string;
  role_id?: number | null;
  temp_folder?: string;
}

export interface UpdateUser {
  name?: string;
  email?: string;
  password?: string;
  role_id?: number | null;
  temp_folder?: string;
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
  (e: 'delete', user: User): void;
  (e: 'restore', user: User): void;
  (e: 'forceDelete', user: User): void;
  (e: 'edit', user: User): void;
}

export interface UserListProps {
  initialUsers?: User[];
}

export interface UserListEmits {
  (e: 'userSelected', user: User): void;
} 