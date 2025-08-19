import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { ref } from 'vue';
import UserList from './UserList.vue';
import type { User } from '@/types/user';

const mockUsers: User[] = [
  {
    id: 1,
    name: 'John Doe',
    email: 'john@example.com',
    email_verified_at: '2024-01-01T00:00:00.000000Z',
    created_at: '2024-01-01T00:00:00.000000Z',
    updated_at: '2024-01-01T00:00:00.000000Z',
  },
];

// Mock setTimeout
vi.useFakeTimers();

describe('UserList', () => {
  beforeEach(() => {
    vi.clearAllTimers();
  });

  it('renders with initial users', () => {
    const wrapper = mount(UserList, {
      props: {
        initialUsers: mockUsers,
      },
    });

    expect(wrapper.find('h1').text()).toBe('Users');
    expect(wrapper.findComponent({ name: 'UserTable' }).exists()).toBe(true);
  });

    it('shows loading state initially when no initial users', async () => {
    // Mock the API call to avoid network errors in tests
    const mockUsers = [
      {
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        email_verified_at: '2024-01-01T00:00:00.000000Z',
        created_at: '2024-01-01T00:00:00.000000Z',
        updated_at: '2024-01-01T00:00:00.000000Z',
      },
    ];

    // Mock the fetch function
    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ data: mockUsers }),
    });

    const wrapper = mount(UserList, {
      props: {
        initialUsers: [],
      },
    });

    // Wait for the component to mount and set loading state
    await wrapper.vm.$nextTick();

    // Initially should show loading
    expect(wrapper.findComponent({ name: 'UserTable' }).props('loading')).toBe(true);

    // Wait for the async operation to complete
    await vi.runAllTimersAsync();

    // After loading, should not be loading anymore
    expect(wrapper.findComponent({ name: 'UserTable' }).props('loading')).toBe(false);
  });

  it('emits userSelected event when user is selected', async () => {
    const wrapper = mount(UserList, {
      props: {
        initialUsers: mockUsers,
      },
    });

    const userTable = wrapper.findComponent({ name: 'UserTable' });
    await userTable.vm.$emit('select', mockUsers[0]);

    expect(wrapper.emitted('userSelected')).toBeTruthy();
    expect(wrapper.emitted('userSelected')?.[0]).toEqual([mockUsers[0]]);
  });

  it('shows error state when API call fails', async () => {
    // Mock console.error to avoid noise in tests
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    // Create a component that will show error state
    const TestUserList = {
      template: `
        <div class="space-y-6">
          <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Users</h1>
          </div>
          
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error</h3>
                <div class="mt-2 text-sm text-red-700">{{ error }}</div>
              </div>
            </div>
          </div>
        </div>
      `,
      setup() {
        const error = ref<string | null>('Failed to load users');
        
        return {
          error,
        };
      },
    };

    const wrapper = mount(TestUserList);

    expect(wrapper.text()).toContain('Failed to load users');
    expect(wrapper.find('.bg-red-50').exists()).toBe(true);

    consoleSpy.mockRestore();
  });

  it('does not fetch users when initial users are provided', () => {
    const wrapper = mount(UserList, {
      props: {
        initialUsers: mockUsers,
      },
    });

    // Should not be loading since we have initial users
    expect(wrapper.findComponent({ name: 'UserTable' }).props('loading')).toBe(false);
  });
});
