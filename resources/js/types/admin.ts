export interface Admin {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  restored_at: string | null;
  roles?: Role[];
  permissions?: Permission[];
  profile_photo?: ProfilePhoto;
  created_by?: Admin;
  updated_by?: Admin;
  deleted_by?: Admin;
  restored_by?: Admin;
}

export interface CreateAdmin {
  name: string;
  email: string;
  password: string;
  role_id?: number | null;
  temp_folder?: string;
}

export interface UpdateAdmin {
  name?: string;
  email?: string;
  password?: string;
  password_confirmation?: string;
  role_id?: number | null;
  temp_folder?: string;
}

export interface AdminTableProps {
  admins: Admin[];
  loading?: boolean;
}

export interface AdminTableEmits {
  select: [admin: Admin];
  delete: [admin: Admin];
  restore: [admin: Admin];
  forceDelete: [admin: Admin];
  edit: [admin: Admin];
  adminResetPassword: [admin: Admin];
}

export interface AdminListProps {
  loading?: boolean;
  error?: string;
}

export interface AdminListEmits {
  adminSelected: [admin: Admin];
  adminCreated: [admin: Admin];
  adminUpdated: [admin: Admin];
  adminDeleted: [admin: Admin];
  adminRestored: [admin: Admin];
  adminForceDeleted: [admin: Admin];
  adminResetPassword: [admin: Admin];
}

export interface AdminFormProps {
  onAdminCreated?: (admin: Admin) => void;
  onAdminUpdated?: (admin: Admin) => void;
  admin?: Admin | null;
  isEditMode?: boolean;
  open?: boolean;
  roles?: Array<{ id: number; name: string }>;
  loadingRoles?: boolean;
}

export interface AdminFormEmits {
  adminCreated: [admin: Admin];
  adminUpdated: [admin: Admin];
  cancelled: [];
  'update:open': [open: boolean];
}

export interface AdminFilters {
  search?: string;
  status?: 'verified' | 'pending';
  sortBy?: keyof Admin;
  sortDirection?: 'asc' | 'desc';
  withInactive?: boolean;
  onlyInactive?: boolean;
}

export interface AdminListOptions {
  page?: number;
  perPage?: number;
  filters?: AdminFilters;
}

export interface Role {
  id: number;
  name: string;
  guard_name: string;
  permissions?: Permission[];
}

export interface Permission {
  id: number;
  name: string;
  guard_name: string;
}

export interface ProfilePhoto {
  id: number;
  url: string;
  name: string;
  file_name: string;
  mime_type: string;
  size: number;
  created_at: string;
}
