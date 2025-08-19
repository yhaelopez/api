import { vi } from 'vitest';
import { config } from '@vue/test-utils'

// Global test configuration
config.global.stubs = {
  // Stub common components that might not be available in tests
  'router-link': true,
  'router-view': true,
}

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
  Link: {
    name: 'Link',
    template: '<a><slot /></a>',
  },
  router: {
    visit: vi.fn(),
  },
  route: vi.fn((name: string) => `/${name}`),
}))

// Mock Ziggy
vi.mock('ziggy-js', () => ({
  default: vi.fn((name: string) => `/${name}`),
}))
