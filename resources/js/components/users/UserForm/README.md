# UserForm Component

A Vue 3 component that can be used for both creating new users and editing existing users.

## Features

- **Dual Mode**: Automatically adapts between create and edit modes
- **Dynamic UI**: Changes title, description, button text, and icon based on mode
- **Password Handling**: Password is required for new users, optional for editing
- **Role Management**: Supports role assignment through dropdown selection
- **Form Validation**: Built-in error handling and validation
- **Responsive Design**: Clean, modern UI using Tailwind CSS

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `isEditMode` | `boolean` | `false` | Whether the form is in edit mode |
| `user` | `User \| null` | `null` | User object to edit (required in edit mode) |
| `onUserCreated` | `function` | `undefined` | Callback when user is created |
| `onUserUpdated` | `function` | `undefined` | Callback when user is updated |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `userCreated` | `User` | Emitted when a new user is successfully created |
| `userUpdated` | `User` | Emitted when an existing user is successfully updated |
| `cancelled` | - | Emitted when the form is cancelled |

## Usage Examples

### Create Mode

```vue
<template>
  <UserForm
    :is-edit-mode="false"
    @user-created="handleUserCreated"
    @cancelled="handleCancelled"
  />
</template>

<script setup>
import { UserForm } from '@/components/users';

const handleUserCreated = (user) => {
  console.log('User created:', user);
  // Handle success (e.g., redirect, show notification)
};

const handleCancelled = () => {
  // Handle cancellation (e.g., go back)
};
</script>
```

### Edit Mode

```vue
<template>
  <UserForm
    :is-edit-mode="true"
    :user="currentUser"
    @user-updated="handleUserUpdated"
    @cancelled="handleCancelled"
  />
</template>

<script setup>
import { UserForm } from '@/components/users';

const currentUser = {
  id: 1,
  name: 'John Doe',
  email: 'john@example.com',
  roles: [{ id: 1, name: 'Admin' }]
};

const handleUserUpdated = (user) => {
  console.log('User updated:', user);
  // Handle success (e.g., redirect, show notification)
};

const handleCancelled = () => {
  // Handle cancellation (e.g., go back)
};
</script>
```

### With Callbacks

```vue
<template>
  <UserForm
    :is-edit-mode="isEditMode"
    :user="selectedUser"
    :on-user-created="handleUserCreated"
    :on-user-updated="handleUserUpdated"
    @cancelled="handleCancelled"
  />
</template>

<script setup>
import { UserForm } from '@/components/users';

const handleUserCreated = (user) => {
  // This callback will be called after successful creation
  // You can also listen to the userCreated event
};

const handleUserUpdated = (user) => {
  // This callback will be called after successful update
  // You can also listen to the userUpdated event
};
</script>
```

## Form Fields

- **Name**: Required text input for user's full name
- **Email**: Required email input with validation
- **Role**: Required dropdown for role selection
- **Password**: 
  - Create mode: Required
  - Edit mode: Optional (leave blank to keep current password)

## Dependencies

- Vue 3 Composition API
- Inertia.js Vue 3
- Tailwind CSS
- Lucide Vue Next (icons)
- Reka UI components (Button, Input, Label, Select, Card)

## Notes

- The component automatically loads available roles from `/api/v1/roles`
- In edit mode, the password field is optional and will only be updated if a new value is provided
- The component handles both create and update operations through the UserService
- Form validation and error handling is built-in
- The UI automatically adapts based on the `isEditMode` prop
