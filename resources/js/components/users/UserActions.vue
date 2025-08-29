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
  DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
  user: User;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  userDeleted: [user: User];
  userRestored: [user: User];
  userForceDeleted: [user: User];
}>();

const isDeleting = ref(false);
const isRestoring = ref(false);
const isForceDeleting = ref(false);
const showDeleteDialog = ref(false);
const showForceDeleteDialog = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);
const forceDeletePassword = ref('');

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
  } catch (error) {
    console.error('Failed to restore user:', error);
  } finally {
    isRestoring.value = false;
  }
};

const handleForceDelete = async () => {
  if (isForceDeleting.value || !forceDeletePassword.value) return;
  
  isForceDeleting.value = true;
  try {
    await UserService.forceDeleteUser(props.user.id);
    emit('userForceDeleted', props.user);
    showForceDeleteDialog.value = false;
    forceDeletePassword.value = '';
  } catch (error) {
    console.error('Failed to force delete user:', error);
  } finally {
    isForceDeleting.value = false;
  }
};

const openDeleteDialog = () => {
  showDeleteDialog.value = true;
};

const openForceDeleteDialog = () => {
  showForceDeleteDialog.value = true;
};
</script>

<template>
  <div class="flex items-center gap-2">
    <!-- Restore button for deleted users -->
    <Button
      v-if="isUserDeleted"
      variant="outline"
      size="sm"
      :disabled="isRestoring"
      @click="handleRestore"
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
          <Label for="force-delete-password">Enter your password to confirm</Label>
          <Input
            id="force-delete-password"
            type="password"
            v-model="forceDeletePassword"
            ref="passwordInput"
            placeholder="Your password"
            @keyup.enter="handleForceDelete"
          />
        </div>

        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button 
            variant="destructive" 
            :disabled="isForceDeleting || !forceDeletePassword"
            @click="handleForceDelete"
          >
            Permanently Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
