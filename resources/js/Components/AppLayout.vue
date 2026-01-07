<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Sidebar -->
    <aside 
      class="fixed inset-y-0 left-0 z-40 bg-white border-r border-neutral-200 transition-all duration-300 flex flex-col"
      :class="[isSidebarCollapsed ? 'w-20' : 'w-64', isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center px-4 border-b border-neutral-100">
        <div class="flex items-center gap-3 overflow-hidden">
          <img src="/images/udomslogo.png" alt="UDOMS" class="w-10 h-10 object-contain flex-shrink-0" />
          <div class="flex flex-col transition-opacity duration-300" :class="{ 'opacity-0 w-0': isSidebarCollapsed }">
            <span class="font-bold text-neutral-800 text-sm leading-tight whitespace-nowrap">Director Office</span>
            <span class="text-[10px] text-neutral-500 whitespace-nowrap">Management System</span>
          </div>
        </div>
      </div>

      <!-- User Profile (Collapsed Mode) -->
      <div v-if="isSidebarCollapsed" class="p-3 border-b border-neutral-100 flex justify-center">
        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm relative group cursor-pointer">
          {{ userInitials }}
          <div class="absolute left-14 top-0 bg-neutral-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap transition-opacity z-50">
            {{ $page.props.auth.user.name }}
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar">
        <Link 
          v-for="item in navigation" 
          :key="item.name" 
          :href="item.href"
          :class="[
            'nav-item group relative',
            isActive(item.href) ? 'nav-item-active' : 'nav-item-inactive',
            isSidebarCollapsed ? 'justify-center px-0' : ''
          ]"
        >
          <component :is="item.icon" class="w-5 h-5 flex-shrink-0 transition-colors" :class="isActive(item.href) ? 'text-blue-600' : 'text-neutral-400 group-hover:text-neutral-600'" />
          
          <span 
            v-if="!isSidebarCollapsed" 
            class="font-medium truncate"
          >
            {{ item.name }}
          </span>

          <!-- Tooltip for collapsed mode -->
          <div 
            v-if="isSidebarCollapsed"
            class="absolute left-16 bg-neutral-800 text-white text-xs font-medium px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50"
          >
            {{ item.name }}
          </div>
        </Link>
      </nav>

      <!-- Bottom Actions -->
      <div class="p-3 border-t border-neutral-100 space-y-1">
        <button 
          @click="isSidebarCollapsed = !isSidebarCollapsed" 
          class="w-full flex items-center gap-3 px-3 py-2 text-neutral-500 hover:bg-neutral-50 rounded-xl transition-colors hidden lg:flex"
          :class="{ 'justify-center': isSidebarCollapsed }"
        >
          <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': isSidebarCollapsed }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
          </svg>
          <span v-if="!isSidebarCollapsed" class="text-sm font-medium">Collapse</span>
        </button>

        <Link 
          href="/logout" 
          method="post" 
          as="button" 
          class="w-full flex items-center gap-3 px-3 py-2 text-slate-600 hover:bg-slate-50 rounded-xl transition-colors"
          :class="{ 'justify-center': isSidebarCollapsed }"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span v-if="!isSidebarCollapsed" class="text-sm font-medium">Sign Out</span>
        </Link>
      </div>
    </aside>

    <!-- Mobile Overlay -->
    <div 
      v-if="isMobileMenuOpen" 
      class="fixed inset-0 bg-black/20 z-30 lg:hidden backdrop-blur-sm"
      @click="isMobileMenuOpen = false"
    ></div>

    <!-- Main Content -->
    <main 
      class="flex-1 min-h-screen transition-all duration-300 flex flex-col"
      :class="[isSidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64']"
    >
      <!-- Top Bar -->
      <header class="h-16 bg-white border-b border-neutral-200 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-20">
        <div class="flex items-center gap-4">
          <!-- Mobile Menu Button -->
          <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="lg:hidden p-2 hover:bg-neutral-100 rounded-lg">
            <svg class="w-6 h-6 text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <!-- Page Title -->
          <h1 class="text-lg font-semibold text-neutral-800">{{ pageTitle }}</h1>
        </div>

        <div class="flex items-center gap-3">
          <!-- Auto Refresh Toggle -->
          <div class="hidden md:flex items-center gap-2">
            <span class="text-xs text-neutral-500">Auto Refresh</span>
            <button 
              @click="toggleAutoRefresh"
              class="w-10 h-5 rounded-full transition-colors p-0.5"
              :class="autoRefresh ? 'bg-green-500' : 'bg-neutral-200'"
            >
              <div 
                class="w-4 h-4 bg-white rounded-full shadow transition-transform"
                :class="autoRefresh ? 'translate-x-5' : 'translate-x-0'"
              ></div>
            </button>
          </div>

          <!-- User Menu -->
          <div class="flex items-center gap-3 pl-3 border-l border-neutral-200">
            <div class="hidden md:block text-right">
              <p class="text-sm font-semibold text-neutral-800">{{ $page.props.auth.user.name }}</p>
              <p class="text-xs text-neutral-500 capitalize">{{ $page.props.auth.user.role }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
              {{ userInitials }}
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="flex-1 p-4 lg:p-6">
        <slot />
      </div>

      <!-- Footer -->
      <footer class="py-4 px-6 text-center text-xs text-neutral-400 border-t border-neutral-100 bg-white">
        © 2026 CSIR-SERC | Unified Director Office Management System
      </footer>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

// Icons as simple components
const HomeIcon = { template: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>' };
const CalendarIcon = { template: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' };
const UsersIcon = { template: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' };
const ClipboardIcon = { template: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>' };
const BookIcon = { template: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>' };
const CheckCircleIcon = { template: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' };
const UserIcon = { template: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>' };

const page = usePage();
const isSidebarCollapsed = ref(false);
const isMobileMenuOpen = ref(false);
const autoRefresh = ref(false);
let refreshInterval = null;

const userInitials = computed(() => {
  const name = page.props.auth.user?.name || '';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const userRole = computed(() => page.props.auth.user?.role || 'staff');
const isPA = computed(() => userRole.value === 'pa');
const isDirector = computed(() => userRole.value === 'director');

const pageTitle = computed(() => {
  const routeName = page.url;
  const titles = {
    '/dashboard': 'Dashboard',
    '/appointments': 'Appointments',
    '/meetings': 'Meetings',
    '/calendar': 'Calendar',
    '/todos': 'Tasks',
    '/journal': 'Journal',
    '/archive': 'Archive',
    '/blocked-dates': 'Availability',
  };
  return titles[routeName] || 'Dashboard';
});

// Check if current URL matches a navigation item
const isActive = (href) => {
  const currentPath = page.url.split('?')[0];
  if (href === '/dashboard') {
    return currentPath === '/dashboard';
  }
  return currentPath.startsWith(href);
};

// Auto-refresh logic
const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value;
  if (autoRefresh.value) {
    refreshInterval = setInterval(() => {
      router.reload({ preserveScroll: true });
    }, 60000);
  } else if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
};

// Navigation items - hide Profile for PA
const navigation = computed(() => {
  const items = [
    { name: 'Dashboard', href: '/dashboard', route: 'dashboard', icon: HomeIcon },
    { name: 'Appointments', href: '/appointments', route: 'appointments.*', icon: UsersIcon },
    { name: 'Meetings', href: '/meetings', route: 'meetings.*', icon: CalendarIcon },
    { name: 'Calendar', href: '/calendar', route: 'calendar.*', icon: CalendarIcon },
    { name: 'Availability', href: '/blocked-dates', route: 'blocked-dates.*', icon: ClipboardIcon },
    { name: 'Archive', href: '/archive', route: 'archive.*', icon: BookIcon },
    { name: 'Tasks', href: '/todos', route: 'todos.*', icon: CheckCircleIcon },
    { name: 'Journal', href: '/journal', route: 'journal.*', icon: BookIcon },
  ];
  
  // Only show Profile for director, not PA
  if (!isPA.value) {
    items.push({ name: 'Profile', href: '/profile', route: 'profile.*', icon: UserIcon });
  }
  
  return items;
});
</script>

<style scoped>
/* Custom scrollbar for navigation */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #d1d5db;
}
</style>
