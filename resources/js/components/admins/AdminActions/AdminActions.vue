<script setup lang="ts">
import { ref } from 'vue';
import type { Admin } from '@/types/admin';
import { AdminService } from '@/services/AdminService';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Edit, Key } from 'lucide-vue-next';

interface Props {
  admin: Admin;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  adminDeleted: [admin: Admin];
  adminRestored: [admin: Admin];
  adminForceDeleted: [admin: Admin];
  adminEdit: [admin: Admin];
  adminResetPassword: [admin: Admin];
}>();

const isDeleting = ref(false);
const isRestoring = ref(false);
const isForceDeleting = ref(false);
const isResettingPassword = ref(false);
const showDeleteDialog = ref(false);
const showRestoreDialog = ref(false);
const showForceDeleteDialog = ref(false);
const showResetPasswordDialog = ref(false);
const forceDeleteConfirmation = ref('');

const isAdminDeleted = AdminService.isAdminDeleted(props.admin);

const handleDelete = async () => {
  if (isDeleting.value) return;
  
  isDeleting.value = true;
  try {
    await AdminService.deleteAdmin(props.admin.id);
    emit('adminDeleted', props.admin);
    showDeleteDialog.value = false;
  } catch (error) {
    console.error('Failed to delete admin:', error);
  } finally {
    isDeleting.value = false;
  }
};

const handleRestore = async () => {
  if (isRestoring.value) return;
  
  isRestoring.value = true;
  try {
    await AdminService.restoreAdmin(props.admin.id);
    emit('adminRestored', props.admin);
    showRestoreDialog.value = false;
  } catch (error) {
    console.error('Failed to restore admin:', error);
  } finally {
    isRestoring.value = false;
  }
};

const handleForceDelete = async () => {
  if (isForceDeleting.value || forceDeleteConfirmation.value !== 'Delete') return;
  
  isForceDeleting.value = true;
  try {
    await AdminService.forceDeleteAdmin(props.admin.id);
    emit('adminForceDeleted', props.admin);
    showForceDeleteDialog.value = false;
    forceDeleteConfirmation.value = '';
  } catch (error) {
    console.error('Failed to force delete admin:', error);
  } finally {
    isForceDeleting.value = false;
  }
};

const handleResetPassword = async () => {
  if (isResettingPassword.value) return;
  
  isResettingPassword.value = true;
  try {
    // Use the new admin-specific endpoint
    const response = await fetch(`/api/admin/v1/admins/${props.admin.id}/send-password-reset`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      }
    });

    if (response.ok) {
      emit('adminResetPassword', props.admin);
      showResetPasswordDialog.value = false;
    } else {
      throw new Error('Failed to send password reset link');
    }
  } catch (error) {
    console.error('Failed to reset password:', error);
  } finally {
    isResettingPassword.value = false;
  }
};

const openDeleteDialog = () => {
  showDeleteDialog.value = true;
};

const openRestoreDialog = () => {
  showRestoreDialog.value = true;
};

const openForceDeleteDialog = () => {
  showForceDeleteDialog.value = true;
};

const openResetPasswordDialog = () => {
  showResetPasswordDialog.value = true;
};
</script>

<template>
  <div class="flex items-center gap-2">
    <!-- Edit button for active admins -->
    <Button
      v-if="!isAdminDeleted"
      variant="outline"
      size="sm"
      @click="emit('adminEdit', admin)"
    >
      <Edit class="h-4 w-4 mr-1" />
      Edit
    </Button>

    <!-- Restore button for deleted admins -->
    <Button
      v-if="isAdminDeleted"
      variant="outline"
      size="sm"
      :disabled="isRestoring"
      @click="openRestoreDialog"
    >
      <span v-if="isRestoring" class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-gray-600"></span>
      <span v-else>Restore</span>
    </Button>

    <!-- Delete button for active admins -->
    <Button
      v-else
      variant="outline"
      size="sm"
      :disabled="isDeleting"
      @click="openDeleteDialog"
    >
      <span v-if="isDeleting" class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-gray-600"></span>
      <span v-else>Delete</span>
    </Button>

    <!-- Force delete button for deleted admins -->
    <Button
      v-if="isAdminDeleted"
      variant="destructive"
      size="sm"
      :disabled="isForceDeleting"
      @click="openForceDeleteDialog"
    >
      <span v-if="isForceDeleting" class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-gray-600"></span>
      <span v-else>Permanently Delete</span>
    </Button>

    <!-- Reset Password button for active admins -->
    <Button
      v-if="!isAdminDeleted"
      variant="outline"
      size="sm"
      :disabled="isResettingPassword"
      @click="openResetPasswordDialog"
    >
      <Key class="h-4 w-4 mr-1" />
      Reset Password
    </Button>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Admin</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete {{ admin.name }}? This will soft delete the admin and they can be restored later.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button variant="destructive" :disabled="isDeleting" @click="handleDelete">
            Delete Admin
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Restore Confirmation Dialog -->
    <Dialog v-model:open="showRestoreDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Restore Admin</DialogTitle>
          <DialogDescription>
            Are you sure you want to restore {{ admin.name }}? This will reactivate the admin account and they will be able to access the system again.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button variant="default" :disabled="isRestoring" @click="handleRestore">
            Restore Admin
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Force Delete Confirmation Dialog -->
    <Dialog v-model:open="showForceDeleteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Permanently Delete Admin</DialogTitle>
          <DialogDescription>
            Are you sure you want to permanently delete {{ admin.name }}? This action cannot be undone and all admin data will be lost forever.
          </DialogDescription>
        </DialogHeader>
        
        <div class="grid gap-2">
          <Label for="force-delete-confirmation">To confirm, type <span class="font-mono text-red-600">Delete</span> in the box below:</Label>
          <Input
            id="force-delete-confirmation"
            type="text"
            v-model="forceDeleteConfirmation"
            placeholder="Type 'Delete' to confirm"
          />
        </div>

        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button 
            variant="destructive" 
            :disabled="isForceDeleting || forceDeleteConfirmation !== 'Delete'"
            @click="handleForceDelete"
          >
            Permanently Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Reset Password Confirmation Dialog -->
    <Dialog v-model:open="showResetPasswordDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Reset Password</DialogTitle>
          <DialogDescription>
            Are you sure you want to reset the password for {{ admin.name }}? This will send a password reset link to their email address.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button variant="default" :disabled="isResettingPassword" @click="handleResetPassword">
            Reset Password
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
