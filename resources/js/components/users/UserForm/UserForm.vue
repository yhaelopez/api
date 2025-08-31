<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { User, CreateUser, UpdateUser } from '@/types/user';
import { UserService } from '@/services/UserService';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
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
  onUserCreated?: (user: User) => void;
  onUserUpdated?: (user: User) => void;
  user?: User | null;
  isEditMode?: boolean;
  open?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  onUserCreated: undefined,
  onUserUpdated: undefined,
  user: null,
  isEditMode: false,
  open: false,
});

const emit = defineEmits<{
  userCreated: [user: User];
  userUpdated: [user: User];
  cancelled: [];
  'update:open': [open: boolean];
}>();

const form = useForm<CreateUser | UpdateUser>({
  name: '',
  email: '',
  password: '',
  role_id: null,
  temp_folder: '',
});

const showPassword = ref(false);
const roles = ref<Array<{ id: number; name: string }>>([]);
const loadingRoles = ref(false);
const existingProfilePhoto = ref<Record<string, any> | undefined>(undefined);

// Load roles from API
const loadRoles = async () => {
  try {
    loadingRoles.value = true;
    const response = await fetch('/api/v1/roles');
    const data = await response.json();
    roles.value = data.data;
  } catch (error) {
    console.error('Failed to load roles:', error);
  } finally {
    loadingRoles.value = false;
  }
};

// Watch for user prop changes to populate form in edit mode
watch(() => props.user, (newUser) => {
  if (newUser && props.isEditMode) {
    form.name = newUser.name;
    form.email = newUser.email;
    // Get the first role's ID if user has roles
    form.role_id = newUser.roles && newUser.roles.length > 0 ? newUser.roles[0].id : undefined;
    // Don't populate password in edit mode
    form.password = '';
    // Reset temp_folder in edit mode
    form.temp_folder = '';
    // Set existing profile photo if available
    existingProfilePhoto.value = newUser.profile_photo || undefined;
  }
}, { immediate: true });

onMounted(() => {
  loadRoles();
});

const submit = async () => {
  try {
    if (props.isEditMode && props.user) {
      // Update existing user
      const updateData: UpdateUser = {
        name: form.name || props.user.name,
        email: form.email || props.user.email,
        role_id: form.role_id
      };
      
      // Only include password if it's been changed
      if (form.password && form.password.trim()) {
        updateData.password = form.password;
      }

      // Include temp_folder if provided
      if (form.temp_folder) {
        updateData.temp_folder = form.temp_folder;
      }

      const response = await UserService.updateUser(props.user.id, updateData);
      
      // Emit success event
      emit('userUpdated', response.data);
    } else {
      // Create new user - ensure all required fields are present
      if (!form.name || !form.email || !form.password) {
        throw new Error('Name, email, and password are required for creating a user');
      }
      
      const createData: CreateUser = {
        name: form.name,
        email: form.email,
        password: form.password,
        role_id: form.role_id
      };

      // Include temp_folder if provided
      if (form.temp_folder) {
        createData.temp_folder = form.temp_folder;
      }

      const response = await UserService.createUser(createData);

      // Emit success event
      emit('userCreated', response.data);
    }

    // Reset form
    form.reset();
    form.temp_folder = '';
    existingProfilePhoto.value = undefined;
  } catch (error) {
    console.error(`Failed to ${props.isEditMode ? 'update' : 'create'} user:`, error);
  }
};

const cancel = () => {
  form.temp_folder = '';
  existingProfilePhoto.value = undefined;
  emit('cancelled');
};

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
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
const cardTitle = computed(() => isEditMode.value ? 'Edit User' : 'Create New User');
const cardDescription = computed(() => 
  isEditMode.value 
    ? 'Update user information.'
    : 'Add a new user to the system. All fields are required.'
);
const submitButtonText = computed(() => isEditMode.value ? 'Update User' : 'Create User');
const icon = computed(() => isEditMode.value ? UserCheck : UserPlus);
const passwordRequired = computed(() => !isEditMode.value);
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
              Full Name
              <span class="text-red-500">*</span>
            </Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="Enter full name"
              required
              autocomplete="name"
              :class="{ 'border-red-500': form.errors.name }"
            />
            <InputError :message="form.errors.name" />
          </div>

          <!-- Email Field -->
          <div class="space-y-2">
            <Label for="email">
              Email Address
              <span class="text-red-500">*</span>
            </Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="Enter email address"
              required
              autocomplete="email"
              :class="{ 'border-red-500': form.errors.email }"
            />
            <InputError :message="form.errors.email" />
          </div>

          <!-- Role Field -->
          <div class="space-y-2">
            <Label for="role_id">
              Role
              <span class="text-red-500">*</span>
            </Label>
            <Select
              id="role_id"
              :model-value="form.role_id || ''"
              @update:model-value="(value) => form.role_id = value === '' ? null : Number(value)"
              :options="roles.map(role => ({ value: role.id, label: role.name }))"
              placeholder="Select a role"
              required
              :disabled="loadingRoles"
              :class="{ 'border-red-500': form.errors.role_id }"
            />
            <InputError :message="form.errors.role_id" />
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <Label for="password">
              Password
              <span v-if="!isEditMode" class="text-red-500">*</span>
              <span v-else class="text-gray-500 text-sm">(leave blank to keep current)</span>
            </Label>
            <div class="relative">
              <Input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                :placeholder="isEditMode ? 'Enter new password (optional)' : 'Enter password'"
                :required="passwordRequired"
                autocomplete="new-password"
                :class="{ 'border-red-500': form.errors.password }"
              />
              <Button
                type="button"
                variant="ghost"
                size="sm"
                class="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                @click="togglePasswordVisibility"
              >
                <svg
                  v-if="showPassword"
                  class="h-4 w-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                  />
                </svg>
                <svg
                  v-else
                  class="h-4 w-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  />
                </svg>
              </Button>
            </div>
            <InputError :message="form.errors.password" />
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
