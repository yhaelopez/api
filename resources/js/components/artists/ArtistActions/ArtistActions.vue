<script setup lang="ts">
import { ref } from 'vue';
import type { Artist } from '@/types/artist';
import { ArtistService } from '@/services/ArtistService';
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
import { Edit } from 'lucide-vue-next';

interface Props {
  artist: Artist;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  artistDeleted: [artist: Artist];
  artistRestored: [artist: Artist];
  artistForceDeleted: [artist: Artist];
  artistEdit: [artist: Artist];
}>();

const isDeleting = ref(false);
const isRestoring = ref(false);
const isForceDeleting = ref(false);
const showDeleteDialog = ref(false);
const showRestoreDialog = ref(false);
const showForceDeleteDialog = ref(false);
const forceDeleteConfirmation = ref('');

const isArtistDeleted = ArtistService.isArtistDeleted(props.artist);

const handleDelete = async () => {
  if (isDeleting.value) return;
  
  isDeleting.value = true;
  try {
    await ArtistService.deleteArtist(props.artist.id);
    emit('artistDeleted', props.artist);
    showDeleteDialog.value = false;
  } catch (error) {
    console.error('Failed to delete artist:', error);
  } finally {
    isDeleting.value = false;
  }
};

const handleRestore = async () => {
  if (isRestoring.value) return;
  
  isRestoring.value = true;
  try {
    await ArtistService.restoreArtist(props.artist.id);
    emit('artistRestored', props.artist);
    showRestoreDialog.value = false;
  } catch (error) {
    console.error('Failed to restore artist:', error);
  } finally {
    isRestoring.value = false;
  }
};

const handleForceDelete = async () => {
  if (isForceDeleting.value || forceDeleteConfirmation.value !== 'Delete') return;
  
  isForceDeleting.value = true;
  try {
    await ArtistService.forceDeleteArtist(props.artist.id);
    emit('artistForceDeleted', props.artist);
    showForceDeleteDialog.value = false;
    forceDeleteConfirmation.value = '';
  } catch (error) {
    console.error('Failed to force delete artist:', error);
  } finally {
    isForceDeleting.value = false;
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
</script>

<template>
  <div class="flex items-center gap-2">
    <!-- Edit button for active artists -->
    <Button
      v-if="!isArtistDeleted"
      variant="outline"
      size="sm"
      @click="emit('artistEdit', artist)"
    >
      <Edit class="h-4 w-4 mr-1" />
      Edit
    </Button>

    <!-- Restore button for deleted artists -->
    <Button
      v-if="isArtistDeleted"
      variant="outline"
      size="sm"
      :disabled="isRestoring"
      @click="openRestoreDialog"
    >
      <span v-if="isRestoring" class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-gray-600"></span>
      <span v-else>Restore</span>
    </Button>

    <!-- Delete button for active artists -->
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

    <!-- Force delete button for deleted artists -->
    <Button
      v-if="isArtistDeleted"
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
          <DialogTitle>Delete Artist</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete {{ artist.name }}? This will soft delete the artist and they can be restored later.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button variant="destructive" :disabled="isDeleting" @click="handleDelete">
            Delete Artist
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Restore Confirmation Dialog -->
    <Dialog v-model:open="showRestoreDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Restore Artist</DialogTitle>
          <DialogDescription>
            Are you sure you want to restore {{ artist.name }}? This will reactivate the artist and make it available again.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary">Cancel</Button>
          </DialogClose>
          <Button variant="default" :disabled="isRestoring" @click="handleRestore">
            Restore Artist
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Force Delete Confirmation Dialog -->
    <Dialog v-model:open="showForceDeleteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Permanently Delete Artist</DialogTitle>
          <DialogDescription>
            Are you sure you want to permanently delete {{ artist.name }}? This action cannot be undone and all artist data will be lost forever.
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
  </div>
</template>
