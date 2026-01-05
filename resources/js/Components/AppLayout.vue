<template>
  <div class="min-h-screen bg-neutral-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 bg-neutral-800 text-white shadow-xl z-50">
      <!-- Logo -->
      <div class="p-4 border-b border-neutral-700">
        <h1 class="text-lg font-bold">Director Office</h1>
        <p class="text-neutral-400 text-sm">CSIR-SERC</p>
      </div>

      <!-- Navigation -->
      <nav class="p-4 space-y-1">
        <a href="/dashboard" class="nav-link" :class="{ active: isActive('/dashboard') }">
          <span class="text-xl">📊</span>
          <span>Dashboard</span>
        </a>
        <a href="/appointments" class="nav-link" :class="{ active: isActive('/appointments') }">
          <span class="text-xl">📋</span>
          <span>Appointments</span>
        </a>
        <a href="/meetings" class="nav-link" :class="{ active: isActive('/meetings') }">
          <span class="text-xl">📅</span>
          <span>Meetings</span>
        </a>
        <a href="/calendar" class="nav-link" :class="{ active: isActive('/calendar') }">
          <span class="text-xl">🗓️</span>
          <span>Calendar</span>
        </a>
        <a href="/todos" class="nav-link" :class="{ active: isActive('/todos') }">
          <span class="text-xl">✅</span>
          <span>Tasks</span>
        </a>
        <a href="/journal" class="nav-link" :class="{ active: isActive('/journal') }">
          <span class="text-xl">📝</span>
          <span>Journal</span>
        </a>
      </nav>

      <!-- User Info -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-neutral-700">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center font-bold">
            {{ userInitials }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-medium truncate">{{ $page.props.auth?.user?.name }}</div>
            <div class="text-neutral-400 text-sm">{{ $page.props.auth?.user?.role }}</div>
          </div>
        </div>
        <form @submit.prevent="logout" class="mt-3">
          <button type="submit" class="w-full btn bg-neutral-700 text-white hover:bg-neutral-600 text-sm">
            Sign Out
          </button>
        </form>
      </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64">
      <!-- Top Bar -->
      <header class="bg-white border-b border-neutral-200 px-6 py-4 sticky top-0 z-40">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-semibold text-neutral-800">{{ pageTitle }}</h1>
          <div class="flex items-center gap-4">
            <span class="text-neutral-500">{{ currentDate }}</span>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();

const userInitials = computed(() => {
  const name = page.props.auth?.user?.name || 'U';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const pageTitle = computed(() => {
  const path = window.location.pathname;
  const titles = {
    '/dashboard': 'Dashboard',
    '/appointments': 'Appointments',
    '/meetings': 'Meetings',
    '/calendar': 'Calendar',
    '/todos': 'Tasks',
    '/journal': 'Journal',
  };
  return titles[path] || 'Director Office';
});

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-IN', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
});

const isActive = (path) => window.location.pathname.startsWith(path);

const logout = () => {
  router.post('/logout');
};
</script>

<style scoped>
.nav-link {
  @apply flex items-center gap-3 px-4 py-3 rounded-lg text-neutral-300 hover:bg-neutral-700 transition-colors;
}
.nav-link.active {
  @apply bg-primary-500 text-white;
}
</style>
