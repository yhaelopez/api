<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next';

interface PaginationProps {
  currentPage: number;
  lastPage: number;
  total: number;
  perPage: number;
  from: number;
  to: number;
}

const props = defineProps<PaginationProps>();

const emit = defineEmits<{
  pageChange: [page: number];
  perPageChange: [perPage: number];
}>();

const handlePageChange = (page: number) => {
  if (page >= 1 && page <= props.lastPage) {
    emit('pageChange', page);
  }
};

const handlePerPageChange = (event: Event) => {
  const target = event.target as HTMLSelectElement;
  const newPerPage = parseInt(target.value, 10);
  emit('perPageChange', newPerPage);
};

const handlePerPageClick = (event: Event) => {
  // Remove readonly temporarily to allow selection
  const target = event.target as HTMLSelectElement;
  target.removeAttribute('readonly');
  target.focus();
  
  // Re-add readonly after a short delay
  setTimeout(() => {
    target.setAttribute('readonly', 'readonly');
  }, 100);
};

const pageNumbers = computed(() => {
  const pages: (number | string)[] = [];
  const current = props.currentPage;
  const last = props.lastPage;
  
  if (last <= 5) {
    // If we have 5 or fewer pages, show all pages
    for (let i = 1; i <= last; i++) {
      pages.push(i);
    }
  } else {
    // ALWAYS show first page
    pages.push(1);
    
    // ALWAYS show ellipsis after first page
    pages.push('...');
    
    // ALWAYS show exactly 3 pages in the middle
    if (current <= 2) {
      // Page 1 or 2: show 2, 3, 4
      pages.push(2, 3, 4);
    } else if (current >= last - 1) {
      // Page last-1 or last: show last-3, last-2, last-1
      pages.push(last - 3, last - 2, last - 1);
    } else {
      // Middle pages: show current-1, current, current+1
      pages.push(current - 1, current, current + 1);
    }
    
    // ALWAYS show ellipsis before last page
    pages.push('...');
    
    // ALWAYS show last page
    pages.push(last);
  }
  
  return pages;
});

const hasPreviousPage = computed(() => props.currentPage > 1);
const hasNextPage = computed(() => props.currentPage < props.lastPage);

const perPageOptions = [5, 10, 25, 50, 100];
</script>

<template>
  <div class="flex items-center justify-between px-2">
    <div class="flex items-center space-x-4">
      <div class="flex-1 text-sm text-muted-foreground">
        Showing {{ from || 0 }} to {{ to || 0 }} of {{ total || 0 }} results
      </div>
      
      <div class="flex items-center space-x-2">
        <span class="text-sm text-muted-foreground">Show:</span>
        <select
          :value="perPage"
          @click="handlePerPageClick"
          @change="handlePerPageChange"
          readonly
          tabindex="-1"
          class="text-sm border border-gray-300 rounded-md px-2 py-1 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer"
        >
          <option v-for="option in perPageOptions" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
        <span class="text-sm text-muted-foreground">per page</span>
      </div>
    </div>
    
    <div class="flex items-center space-x-2">
      <Button
        variant="outline"
        size="sm"
        :disabled="!hasPreviousPage"
        @click="handlePageChange(currentPage - 1)"
        class="hidden sm:flex"
        data-ff-ignore="true"
      >
        <ChevronLeft class="h-4 w-4" />
        Previous
      </Button>
      
      <div class="flex items-center space-x-1">
        <template v-for="(page, index) in pageNumbers" :key="index">
          <Button
            v-if="typeof page === 'number'"
            variant="outline"
            size="sm"
            :class="[
              'h-8 w-8 p-0',
              page === currentPage
                ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                : 'hover:bg-muted'
            ]"
            @click="handlePageChange(page)"
            data-ff-ignore="true"
          >
            {{ page }}
          </Button>
          <span
            v-else
            class="flex h-8 w-8 items-center justify-center text-sm text-muted-foreground"
          >
            <MoreHorizontal class="h-4 w-4" />
          </span>
        </template>
      </div>
      
      <Button
        variant="outline"
        size="sm"
        :disabled="!hasNextPage"
        @click="handlePageChange(currentPage + 1)"
        class="hidden sm:flex"
        data-ff-ignore="true"
      >
        Next
        <ChevronRight class="h-4 w-4" />
      </Button>
    </div>
  </div>
</template>
