<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-neutral-800">Meetings</h1>
          <p class="text-neutral-500 text-sm mt-1">Manage all scheduled meetings</p>
        </div>
        <Link href="/meetings/create" class="btn btn-primary">
          <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          New Meeting
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="fluent-card p-4 border-l-4 border-l-blue-500">
          <p class="text-xs text-neutral-500 font-medium uppercase tracking-wide">Total</p>
          <p class="text-2xl font-bold text-neutral-800 mt-1">{{ meetings.total || 0 }}</p>
        </div>
        <div class="fluent-card p-4 border-l-4 border-l-green-500">
          <p class="text-xs text-neutral-500 font-medium uppercase tracking-wide">Completed</p>
          <p class="text-2xl font-bold text-neutral-800 mt-1">{{ completedCount }}</p>
        </div>
        <div class="fluent-card p-4 border-l-4 border-l-amber-500">
          <p class="text-xs text-neutral-500 font-medium uppercase tracking-wide">Scheduled</p>
          <p class="text-2xl font-bold text-neutral-800 mt-1">{{ scheduledCount }}</p>
        </div>
        <div class="fluent-card p-4 border-l-4 border-l-red-500">
          <p class="text-xs text-neutral-500 font-medium uppercase tracking-wide">Cancelled</p>
          <p class="text-2xl font-bold text-neutral-800 mt-1">{{ cancelledCount }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="fluent-card p-4">
        <div class="flex flex-wrap gap-4">
          <select v-model="filterStatus" class="fluent-select w-auto min-w-[150px]">
            <option value="">All Statuses</option>
            <option value="scheduled">Scheduled</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <select v-model="filterType" class="fluent-select w-auto min-w-[150px]">
            <option value="">All Types</option>
            <option value="in_person">In Person</option>
            <option value="video">Video Call</option>
            <option value="phone">Phone Call</option>
          </select>
          <input type="date" v-model="filterDate" class="fluent-input w-auto" />
        </div>
      </div>

      <!-- Meetings List -->
      <div class="fluent-card overflow-hidden">
        <div v-if="meetings.data && meetings.data.length > 0">
          <table class="fluent-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="meeting in meetings.data" :key="meeting.id">
                <td>
                  <div>
                    <p class="font-semibold text-neutral-800">{{ meeting.title }}</p>
                    <p class="text-sm text-neutral-500 truncate max-w-xs">{{ meeting.description || 'No description' }}</p>
                  </div>
                </td>
                <td>
                  <span class="capitalize">{{ meeting.meeting_type?.replace('_', ' ') || 'N/A' }}</span>
                </td>
                <td>
                  <div>
                    <p class="font-medium">{{ formatDate(meeting.start_time) }}</p>
                    <p class="text-sm text-neutral-500">{{ formatTime(meeting.start_time) }}</p>
                  </div>
                </td>
                <td>
                  <span class="badge" :class="getStatusClass(meeting.status)">{{ meeting.status }}</span>
                </td>
                <td>
                  <div class="flex gap-2">
                    <Link :href="`/meetings/${meeting.id}`" class="text-blue-600 hover:underline text-sm font-medium">View</Link>
                    <button @click="cancelMeeting(meeting.id)" class="text-red-600 hover:underline text-sm font-medium">Cancel</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div v-else class="empty-state">
          <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <p class="font-medium text-neutral-600">No meetings found</p>
          <p class="text-sm">Create your first meeting to get started</p>
          <Link href="/meetings/create" class="btn btn-primary mt-4">Create Meeting</Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Components/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  meetings: { type: Object, default: () => ({ data: [], total: 0 }) },
  filters: { type: Object, default: () => ({}) },
});

const filterStatus = ref(props.filters.status || '');
const filterType = ref(props.filters.meeting_type || '');
const filterDate = ref(props.filters.date_from || '');

const completedCount = computed(() => props.meetings.data?.filter(m => m.status === 'completed').length || 0);
const scheduledCount = computed(() => props.meetings.data?.filter(m => m.status === 'scheduled').length || 0);
const cancelledCount = computed(() => props.meetings.data?.filter(m => m.status === 'cancelled').length || 0);

const formatDate = (dt) => {
  if (!dt) return '--';
  return new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const formatTime = (dt) => {
  if (!dt) return '--';
  return new Date(dt).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const getStatusClass = (status) => ({
  scheduled: 'badge-scheduled',
  in_progress: 'badge-approved',
  completed: 'bg-gray-100 text-gray-600',
  cancelled: 'badge-rejected',
  pending: 'badge-pending'
}[status] || 'bg-gray-100 text-gray-600');

const cancelMeeting = (id) => {
  if (confirm('Are you sure you want to cancel this meeting?')) {
    router.post(`/meetings/${id}/cancel`);
  }
};
</script>
