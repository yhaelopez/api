import type { User } from '@/types/user';

export interface UserTableService {
  formatDate(dateString: string): string;
  getUserInitials(user: User): string;
  getUserStatus(user: User): { text: string; isVerified: boolean };
  sortUsers(users: User[], field: keyof User, direction: 'asc' | 'desc'): User[];
}

export class UserTableServiceImpl implements UserTableService {
  formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString();
  }

  getUserInitials(user: User): string {
    return user.name.charAt(0).toUpperCase();
  }

  getUserStatus(user: User): { text: string; isVerified: boolean } {
    const isVerified = !!user.email_verified_at;
    return {
      text: isVerified ? 'Verified' : 'Pending',
      isVerified,
    };
  }

  sortUsers(users: User[], field: keyof User, direction: 'asc' | 'desc'): User[] {
    return [...users].sort((a, b) => {
      const aValue = a[field];
      const bValue = b[field];

      if (aValue === bValue) return 0;

      const comparison = aValue < bValue ? -1 : 1;
      return direction === 'asc' ? comparison : -comparison;
    });
  }
}

export const userTableService = new UserTableServiceImpl(); 