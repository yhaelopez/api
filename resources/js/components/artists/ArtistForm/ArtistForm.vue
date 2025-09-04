<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Artist, CreateArtist, UpdateArtist } from '@/types/artist';
import { ArtistService } from '@/services/ArtistService';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import FilePondUpload from '@/components/FilePondUpload.vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { LoaderCircle, UserPlus, UserCheck } from 'lucide-vue-next';

interface Props {
  onArtistCreated?: (artist: Artist) => void;
  onArtistUpdated?: (artist: Artist) => void;
  artist?: Artist | null;
  isEditMode?: boolean;
  open?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  onArtistCreated: undefined,
  onArtistUpdated: undefined,
  artist: null,
  isEditMode: false,
  open: false,
});

const emit = defineEmits<{
  artistCreated: [artist: Artist];
  artistUpdated: [artist: Artist];
  cancelled: [];
  'update:open': [open: boolean];
}>();

const form = useForm<CreateArtist | UpdateArtist>({
  name: '',
  spotify_id: '',
  popularity: undefined,
  followers_count: undefined,
  temp_folder: '',
});

const existingProfilePhoto = ref<Record<string, any> | undefined>(undefined);

// Watch for artist prop changes to populate form in edit mode
watch(() => props.artist, (newArtist) => {
  if (newArtist && props.isEditMode) {
    form.name = newArtist.name;
    form.spotify_id = newArtist.spotify_id || '';
    form.popularity = newArtist.popularity;
    form.followers_count = newArtist.followers_count;
    // Reset temp_folder in edit mode
    form.temp_folder = '';
    // Set existing profile photo if available
    existingProfilePhoto.value = newArtist.profile_photo || undefined;
  }
}, { immediate: true });

const submit = async () => {
  try {
    if (props.isEditMode && props.artist) {
      // Update existing artist
      const updateData: UpdateArtist = {
        name: form.name || props.artist.name,
        spotify_id: form.spotify_id || undefined,
        popularity: form.popularity,
        followers_count: form.followers_count
      };

      // Include temp_folder if provided
      if (form.temp_folder) {
        updateData.temp_folder = form.temp_folder;
      }

      const response = await ArtistService.updateArtist(props.artist.id, updateData);
      
      // Emit success event
      emit('artistUpdated', response.data);
    } else {
      // Create new artist - ensure name is present
      if (!form.name) {
        throw new Error('Name is required for creating an artist');
      }
      
      const createData: CreateArtist = {
        name: form.name,
        spotify_id: form.spotify_id || undefined,
        popularity: form.popularity,
        followers_count: form.followers_count
      };

      // Include temp_folder if provided
      if (form.temp_folder) {
        createData.temp_folder = form.temp_folder;
      }

      const response = await ArtistService.createArtist(createData);

      // Emit success event
      emit('artistCreated', response.data);
    }

    // Reset form
    form.reset();
    form.temp_folder = '';
    existingProfilePhoto.value = undefined;
  } catch (error) {
    console.error(`Failed to ${props.isEditMode ? 'update' : 'create'} artist:`, error);
  }
};

const cancel = () => {
  form.temp_folder = '';
  existingProfilePhoto.value = undefined;
  emit('cancelled');
};

// FilePond event handlers
const handleFileProcessed = (result: any) => {
  console.log('File processed:', result);
};

const handleFileRemoved = (file: any) => {
  console.log('File removed:', file);
  form.temp_folder = '';
};

// Computed properties for dynamic UI
const isEditMode = computed(() => props.isEditMode);
const cardTitle = computed(() => isEditMode.value ? 'Edit Artist' : 'Create New Artist');
const cardDescription = computed(() => 
  isEditMode.value 
    ? 'Update artist information.'
    : 'Add a new artist to the system. Name is required.'
);
const submitButtonText = computed(() => isEditMode.value ? 'Update Artist' : 'Create Artist');
const icon = computed(() => isEditMode.value ? UserCheck : UserPlus);
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <component :is="icon" class="h-5 w-5" />
          {{ cardTitle }}
        </DialogTitle>
        <DialogDescription>
          {{ cardDescription }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit">
        <div class="space-y-4">
          <!-- Name Field -->
          <div class="space-y-2">
            <Label for="name">
              Artist Name
              <span class="text-red-500">*</span>
            </Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="Enter artist name"
              required
              :class="{ 'border-red-500': form.errors.name }"
            />
            <InputError :message="form.errors.name" />
          </div>

          <!-- Spotify ID Field -->
          <div class="space-y-2">
            <Label for="spotify_id">
              Spotify ID
              <span class="text-gray-500 text-sm">(optional)</span>
            </Label>
            <Input
              id="spotify_id"
              v-model="form.spotify_id"
              type="text"
              placeholder="Enter Spotify ID"
              :class="{ 'border-red-500': form.errors.spotify_id }"
            />
            <InputError :message="form.errors.spotify_id" />
          </div>

          <!-- Popularity Field -->
          <div class="space-y-2">
            <Label for="popularity">
              Popularity (0-100)
              <span class="text-gray-500 text-sm">(optional)</span>
            </Label>
            <Input
              id="popularity"
              v-model.number="form.popularity"
              type="number"
              min="0"
              max="100"
              placeholder="Enter popularity score"
              :class="{ 'border-red-500': form.errors.popularity }"
            />
            <InputError :message="form.errors.popularity" />
          </div>

          <!-- Followers Count Field -->
          <div class="space-y-2">
            <Label for="followers_count">
              Followers Count
              <span class="text-gray-500 text-sm">(optional)</span>
            </Label>
            <Input
              id="followers_count"
              v-model.number="form.followers_count"
              type="number"
              min="0"
              placeholder="Enter followers count"
              :class="{ 'border-red-500': form.errors.followers_count }"
            />
            <InputError :message="form.errors.followers_count" />
          </div>


          <!-- Profile Photo Field -->
          <div class="space-y-2">
            <Label for="profile_photo">
              Profile Photo
              <span class="text-gray-500 text-sm">(optional)</span>
            </Label>
            <FilePondUpload
              v-model="form.temp_folder"
              :existing-file="existingProfilePhoto"
              :user="props.artist || undefined"
              @file-processed="handleFileProcessed"
              @file-removed="handleFileRemoved"
            />
            <InputError :message="form.errors.temp_folder" />
          </div>
        </div>

        <DialogFooter class="flex gap-3 pt-6">
          <Button
            type="button"
            variant="outline"
            @click="cancel"
            :disabled="form.processing"
            class="flex-1"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
            class="flex-1"
          >
            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
            {{ submitButtonText }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
