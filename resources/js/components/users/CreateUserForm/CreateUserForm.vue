<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { User, CreateUser } from '@/types/user';
import { UserService } from '@/services/UserService';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { LoaderCircle, UserPlus } from 'lucide-vue-next';

interface Props {
  onUserCreated?: (user: User) => void;
}

const props = withDefaults(defineProps<Props>(), {
  onUserCreated: undefined,
});

const emit = defineEmits<{
  userCreated: [user: User];
  cancelled: [];
}>();

const form = useForm<CreateUser>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Role options based on the backend RoleEnum
const roleOptions = [
  { value: 'user', label: 'User' },
  { value: 'superadmin', label: 'Super Admin' },
];

const submit = async () => {
  try {
    const response = await UserService.createUser({
      name: form.name,
      email: form.email,
      password: form.password,
      role: form.role
    });

    // Emit success event
    emit('userCreated', response.data);
    
    // Call optional callback
    if (props.onUserCreated) {
      props.onUserCreated(response.data);
    }

    // Reset form
    form.reset();
  } catch (error) {
    console.error('Failed to create user:', error);
  }
};

const cancel = () => {
  emit('cancelled');
};

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
  showPasswordConfirmation.value = !showPasswordConfirmation.value;
};
</script>

<template>
  <Card class="w-full max-w-md mx-auto">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <UserPlus class="h-5 w-5" />
        Create New User
      </CardTitle>
      <CardDescription>
        Add a new user to the system. All fields are required.
      </CardDescription>
    </CardHeader>

    <form @submit.prevent="submit">
      <CardContent class="space-y-4">
        <!-- Name Field -->
        <div class="space-y-2">
          <Label for="name">Full Name</Label>
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
          <Label for="email">Email Address</Label>
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
          <Label for="role">Role</Label>
          <Select
            id="role"
            v-model="form.role"
            :options="roleOptions"
            placeholder="Select a role"
            required
            :class="{ 'border-red-500': form.errors.role }"
          />
          <InputError :message="form.errors.role" />
        </div>

        <!-- Password Field -->
        <div class="space-y-2">
          <Label for="password">Password</Label>
          <div class="relative">
            <Input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Enter password"
              required
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

        <!-- Password Confirmation Field -->
        <div class="space-y-2">
          <Label for="password_confirmation">Confirm Password</Label>
          <div class="relative">
            <Input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showPasswordConfirmation ? 'text' : 'password'"
              placeholder="Confirm password"
              required
              autocomplete="new-password"
              :class="{ 'border-red-500': form.errors.password_confirmation }"
            />
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
              @click="togglePasswordConfirmationVisibility"
            >
              <svg
                v-if="showPasswordConfirmation"
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
          <InputError :message="form.errors.password_confirmation" />
        </div>
      </CardContent>

      <CardFooter class="flex gap-3">
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
          Create User
        </Button>
      </CardFooter>
    </form>
  </Card>
</template>
