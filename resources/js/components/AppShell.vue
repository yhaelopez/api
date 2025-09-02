<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
import { SharedData } from '@/types';
import NotificationContainer from '@/components/NotificationContainer.vue';
import NotificationDebug from '@/components/NotificationDebug.vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage<SharedData>().props.sidebarOpen;
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
        <NotificationContainer />
        <NotificationDebug />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
        <NotificationContainer />
        <NotificationDebug />
    </SidebarProvider>
</template>
