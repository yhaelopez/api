<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Admin, CreateAdmin, UpdateAdmin } from '@/types/admin';
import { AdminService } from '@/services/AdminService';
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
import { LoaderCircle, UserPlus, UserCheck, User, Mail, Lock, Shield } from 'lucide-vue-next';

interface Props {
  onAdminCreated?: (admin: Admin) => void;
  onAdminUpdated?: (admin: Admin) => void;
  admin?: Admin | null;
  isEditMode?: boolean;
  open?: boolean;
  roles?: Array<{ id: number; name: string }>;
  loadingRoles?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  onAdminCreated: undefined,
  onAdminUpdated: undefined,
  admin: null,
  isEditMode: false,
  open: false,
  roles: () => [],
  loadingRoles: false,
});

const emit = defineEmits<{
  adminCreated: [admin: Admin];
  adminUpdated: [admin: Admin];
  cancelled: [];
  'update:open': [open: boolean];
}>();

const form = useForm<CreateAdmin | UpdateAdmin>({
  name: '',
  email: '',
  password: '',
  role_id: undefined,
  temp_folder: '',
});

// Watch for changes in the open prop to reset form when dialog opens
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    resetForm();
    if (props.admin) {
      populateForm();
    }
  }
});

// Watch for changes in the admin prop to populate form when editing
watch(() => props.admin, (admin) => {
  if (admin && props.open) {
    populateForm();
  }
});

const resetForm = () => {
  form.reset();
  form.clearErrors();
  form.temp_folder = '';
};

const populateForm = () => {
  if (props.admin) {
    form.name = props.admin.name;
    form.email = props.admin.email;
    form.password = '';
    form.role_id = props.admin.roles?.[0]?.id || undefined;
    // Reset temp_folder in edit mode
    form.temp_folder = '';
  }
};

const submit = async () => {
  try {
    if (props.isEditMode && props.admin) {
      // Update existing admin
      const updateData: UpdateAdmin = {
        name: form.name || '',
        email: form.email || '',
        role_id: form.role_id,
      };

      // Only include password if it's provided
      if (form.password) {
        updateData.password = form.password || '';
      }

      // Include temp_folder if provided
      if (form.temp_folder) {
        updateData.temp_folder = form.temp_folder;
      }

      const response = await AdminService.updateAdmin(props.admin.id, updateData);
      emit('adminUpdated', response.data);
    } else {
      // Create new admin
      const createData: CreateAdmin = {
        name: form.name || '',
        email: form.email || '',
        password: form.password || '',
        role_id: form.role_id,
      };

      // Include temp_folder if provided
      if (form.temp_folder) {
        createData.temp_folder = form.temp_folder;
      }

      const response = await AdminService.createAdmin(createData);
      emit('adminCreated', response.data);
    }

    // Close the dialog
    emit('update:open', false);
  } catch (error) {
    console.error('Error saving admin:', error);
    // Handle error - you might want to show a toast notification here
  }
};

const cancel = () => {
  emit('cancelled');
  emit('update:open', false);
};

const handleFileUpload = (folder: string) => {
  form.temp_folder = folder;
};
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <UserPlus v-if="!isEditMode" class="h-5 w-5" />
          <UserCheck v-else class="h-5 w-5" />
          {{ isEditMode ? 'Edit Admin' : 'Create Admin' }}
        </DialogTitle>
        <DialogDescription>
          {{ isEditMode ? 'Update admin information' : 'Create a new admin account' }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="space-y-2">
          <Label for="name" class="flex items-center gap-2">
            <User class="h-4 w-4" />
            Name
            <span class="text-red-500">*</span>
          </Label>
          <Input
            id="name"
            v-model="form.name"
            type="text"
            placeholder="Enter admin name"
            required
            :class="{ 'border-red-500': form.errors.name }"
          />
          <InputError :message="form.errors.name" />
        </div>

        <div class="space-y-2">
          <Label for="email" class="flex items-center gap-2">
            <Mail class="h-4 w-4" />
            Email
            <span class="text-red-500">*</span>
          </Label>
          <Input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Enter admin email"
            required
            :class="{ 'border-red-500': form.errors.email }"
          />
          <InputError :message="form.errors.email" />
        </div>

        <div class="space-y-2">
          <Label for="password" class="flex items-center gap-2">
            <Lock class="h-4 w-4" />
            Password
            <span class="text-red-500">*</span>
          </Label>
          <Input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Enter password"
            :required="!isEditMode"
            :class="{ 'border-red-500': form.errors.password }"
          />
          <InputError :message="form.errors.password" />
        </div>


        <div class="space-y-2">
          <Label for="role_id" class="flex items-center gap-2">
            <Shield class="h-4 w-4" />
            Role
            <span class="text-red-500">*</span>
          </Label>
          <Select
            id="role_id"
            :model-value="form.role_id || ''"
            @update:model-value="(value) => form.role_id = value === '' ? undefined : Number(value)"
            :options="roles.map(role => ({ value: role.id, label: role.name }))"
            placeholder="Select a role"
            required
            :disabled="loadingRoles"
            :class="{ 'border-red-500': form.errors.role_id }"
          />
          <InputError :message="form.errors.role_id" />
        </div>

        <div class="space-y-2">
          <Label for="profile_photo">Profile Photo</Label>
          <FilePondUpload
            id="profile_photo"
            v-model="form.temp_folder"
            @uploaded="handleFileUpload"
            accept="image/*"
            :max-files="1"
            :disabled="form.processing"
          />
        </div>
      </form>

      <DialogFooter>
        <Button type="button" variant="outline" @click="cancel" :disabled="form.processing">
          Cancel
        </Button>
        <Button type="submit" @click="submit" :disabled="form.processing">
          <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
          {{ isEditMode ? 'Update Admin' : 'Create Admin' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>