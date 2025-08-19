import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import UserTable from './UserTable.vue';
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
  {
    id: 2,
    name: 'Jane Smith',
    email: 'jane@example.com',
    created_at: '2024-01-02T00:00:00.000000Z',
    updated_at: '2024-01-02T00:00:00.000000Z',
  },
];

describe('UserTable', () => {
  it('renders users correctly', () => {
    const wrapper = mount(UserTable, {
      props: {
        users: mockUsers,
      },
    });

    expect(wrapper.find('table').exists()).toBe(true);
    expect(wrapper.findAll('tbody tr')).toHaveLength(2);
    expect(wrapper.text()).toContain('John Doe');
    expect(wrapper.text()).toContain('jane@example.com');
  });

  it('shows loading state', () => {
    const wrapper = mount(UserTable, {
      props: {
        users: [],
        loading: true,
      },
    });

    expect(wrapper.find('.animate-spin').exists()).toBe(true);
    expect(wrapper.find('table').exists()).toBe(false);
  });

  it('shows empty state when no users', () => {
    const wrapper = mount(UserTable, {
      props: {
        users: [],
        loading: false,
      },
    });

    expect(wrapper.text()).toContain('No users found');
    expect(wrapper.find('table').exists()).toBe(false);
  });

  it('emits select event when user row is clicked', async () => {
    const wrapper = mount(UserTable, {
      props: {
        users: mockUsers,
      },
    });

    const firstRow = wrapper.findAll('tbody tr')[0];
    await firstRow.trigger('click');

    expect(wrapper.emitted('select')).toBeTruthy();
    expect(wrapper.emitted('select')?.[0]).toEqual([mockUsers[0]]);
  });

  it('displays verified status correctly', () => {
    const wrapper = mount(UserTable, {
      props: {
        users: mockUsers,
      },
    });

    const verifiedUser = wrapper.findAll('tbody tr')[0];
    expect(verifiedUser.text()).toContain('Verified');
    expect(verifiedUser.find('.bg-green-100').exists()).toBe(true);
  });

  it('displays pending status correctly', () => {
    const wrapper = mount(UserTable, {
      props: {
        users: mockUsers,
      },
    });

    const pendingUser = wrapper.findAll('tbody tr')[1];
    expect(pendingUser.find('.bg-yellow-100').exists()).toBe(true);
  });
});
