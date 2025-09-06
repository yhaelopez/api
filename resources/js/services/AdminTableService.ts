import type { Admin } from '@/types/admin';

export class AdminTableService {
  /**
   * Get admin status information
   */
  static getAdminStatus(admin: Admin) {
    const isDeleted = !!admin.deleted_at;
    const isVerified = !!admin.email_verified_at;

    if (isDeleted) {
      return {
        isDeleted: true,
        isVerified: false,
        text: 'Deleted',
        color: 'red',
      };
    }

    if (isVerified) {
      return {
        isDeleted: false,
        isVerified: true,
        text: 'Verified',
        color: 'green',
      };
    }

    return {
      isDeleted: false,
      isVerified: false,
      text: 'Pending',
      color: 'yellow',
    };
  }

  /**
   * Get admin initials for avatar
   */
  static getAdminInitials(admin: Admin): string {
    if (!admin.name) return 'A';
    
    const names = admin.name.trim().split(' ');
    if (names.length === 1) {
      return names[0].charAt(0).toUpperCase();
    }
    
    return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase();
  }

  /**
   * Format date for display
   */
  static formatDate(dateString: string): string {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) {
      return 'Invalid Date';
    }
    
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  }

  /**
   * Format date and time for display
   */
  static formatDateTime(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  }

  /**
   * Get admin role display text
   */
  static getAdminRole(admin: Admin): string {
    if (!admin.roles || admin.roles.length === 0) {
      return 'No role';
    }
    
    return admin.roles[0].name;
  }

  /**
   * Check if admin has specific role
   */
  static hasRole(admin: Admin, roleName: string): boolean {
    if (!admin.roles) return false;
    
    return admin.roles.some(role => role.name === roleName);
  }

  /**
   * Get admin permissions count
   */
  static getPermissionsCount(admin: Admin): number {
    if (!admin.permissions) return 0;
    
    return admin.permissions.length;
  }

  /**
   * Check if admin has specific permission
   */
  static hasPermission(admin: Admin, permissionName: string): boolean {
    if (!admin.permissions) return false;
    
    return admin.permissions.some(permission => permission.name === permissionName);
  }

  /**
   * Get admin creation info
   */
  static getCreationInfo(admin: Admin): string {
    if (!admin.created_by) {
      return `Created ${this.formatDate(admin.created_at)}`;
    }
    
    return `Created by ${admin.created_by.name} on ${this.formatDate(admin.created_at)}`;
  }

  /**
   * Get admin last update info
   */
  static getLastUpdateInfo(admin: Admin): string {
    if (!admin.updated_by) {
      return `Updated ${this.formatDate(admin.updated_at)}`;
    }
    
    return `Updated by ${admin.updated_by.name} on ${this.formatDate(admin.updated_at)}`;
  }

  /**
   * Get admin deletion info
   */
  static getDeletionInfo(admin: Admin): string | null {
    if (!admin.deleted_at) return null;
    
    if (!admin.deleted_by) {
      return `Deleted ${this.formatDate(admin.deleted_at)}`;
    }
    
    return `Deleted by ${admin.deleted_by.name} on ${this.formatDate(admin.deleted_at)}`;
  }

  /**
   * Get admin restoration info
   */
  static getRestorationInfo(admin: Admin): string | null {
    if (!admin.restored_at) return null;
    
    if (!admin.restored_by) {
      return `Restored ${this.formatDate(admin.restored_at)}`;
    }
    
    return `Restored by ${admin.restored_by.name} on ${this.formatDate(admin.restored_at)}`;
  }
}
