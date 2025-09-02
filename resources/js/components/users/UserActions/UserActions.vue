<script setup lang="ts">
import { ref } from 'vue';
import type { User } from '@/types/user';
import { UserService } from '@/services/UserService';
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
  user: User;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  userDeleted: [user: User];
  userRestored: [user: User];
  userForceDeleted: [user: User];
  userEdit: [user: User];
  userResetPassword: [user: User];
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

const isUserDeleted = UserService.isUserDeleted(props.user);

const handleDelete = async () => {
  if (isDeleting.value) return;
  
  isDeleting.value = true;
  try {
    await UserService.deleteUser(props.user.id);
    emit('userDeleted', props.user);
    showDeleteDialog.value = false;
  } catch (error) {
    console.error('Failed to delete user:', error);
  } finally {
    isDeleting.value = false;
  }
};

const handleRestore = async () => {
  if (isRestoring.value) return;
  
  isRestoring.value = true;
  try {
    await UserService.restoreUser(props.user.id);
    emit('userRestored', props.user);
    showRestoreDialog.value = false;
  } catch (error) {
    console.error('Failed to restore user:', error);
  } finally {
    isRestoring.value = false;
  }
};

const handleForceDelete = async () => {
  if (isForceDeleting.value || forceDeleteConfirmation.value !== 'Delete') return;
  
  isForceDeleting.value = true;
  try {
    await UserService.forceDeleteUser(props.user.id);
    emit('userForceDeleted', props.user);
    showForceDeleteDialog.value = false;
    forceDeleteConfirmation.value = '';
  } catch (error) {
    console.error('Failed to force delete user:', error);
  } finally {
    isForceDeleting.value = false;
  }
};

const handleResetPassword = async () => {
  if (isResettingPassword.value) return;
  
  isResettingPassword.value = true;
  try {
    // Use the new user-specific endpoint
    const response = await fetch(`/api/v1/users/${props.user.id}/send-password-reset`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      }
    });

    if (response.ok) {
      emit('userResetPassword', props.user);
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
    <!-- Edit button for active users -->
    <Button
      v-if="!isUserDeleted"
      variant="outline"
      size="sm"
      @click="emit('userEdit', user)"
    >
      <Edit class="h-4 w-4 mr-1" />
      Edit
    </Button>

    <!-- Restore button for deleted users -->
    <Button
      v-if="isUserDeleted"
      variant="outline"
      size="sm"
      :disabled="isRestoring"
      @click="openRestoreDialog"
    >
      <span v-if="isRestoring" class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-gray-600"></span>
      <span v-else>Restore</span>
    </Button>

    <!-- Delete button for active users -->
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

    <!-- Force delete button for deleted users -->
    <Button
      v-if="isUserDeleted"
      variant="destructive"
      size="sm"
      :disabled="isForceDeleting"
      @click="openForceDeleteDialog"
    >
      <span v-if="isForceDeleting" class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-gray-600"></span>
      <span v-else>Permanently Delete</span>
    </Button>

    <!-- Reset Password button for active users -->
    <Button
      v-if="!isUserDeleted"
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
          <DialogTitle>Delete User</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete {{ user.name }}? This will soft delete the user and they can be restored later.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button variant="destructive" :disabled="isDeleting" @click="handleDelete">
            Delete User
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Restore Confirmation Dialog -->
    <Dialog v-model:open="showRestoreDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Restore User</DialogTitle>
          <DialogDescription>
            Are you sure you want to restore {{ user.name }}? This will reactivate the user account and they will be able to access the system again.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button variant="default" :disabled="isRestoring" @click="handleRestore">
            Restore User
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Force Delete Confirmation Dialog -->
    <Dialog v-model:open="showForceDeleteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Permanently Delete User</DialogTitle>
          <DialogDescription>
            Are you sure you want to permanently delete {{ user.name }}? This action cannot be undone and all user data will be lost forever.
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
            Are you sure you want to reset the password for {{ user.name }}? This will send a password reset link to their email address.
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
