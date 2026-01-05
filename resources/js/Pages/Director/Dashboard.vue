<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header with Ticker -->
      <div class="bg-primary-500 text-white rounded-lg p-4 overflow-hidden">
        <div class="ticker">
          <div class="ticker-content flex gap-8">
            <span v-for="notification in notifications" :key="notification.id" class="flex items-center gap-2">
              <span v-if="notification.type === 'appointment'" class="text-yellow-300">📅</span>
              <span v-else class="text-green-300">🔔</span>
              {{ notification.message }}
            </span>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card border-primary-500">
          <div class="text-3xl font-bold text-primary-500">{{ stats.pending_appointments }}</div>
          <div class="text-neutral-600 text-sm">Pending Requests</div>
        </div>
        <div class="stat-card border-success-500">
          <div class="text-3xl font-bold text-success-500">{{ stats.today_meetings }}</div>
          <div class="text-neutral-600 text-sm">Today's Meetings</div>
        </div>
        <div class="stat-card border-accent-500">
          <div class="text-3xl font-bold text-accent-500">{{ stats.this_week_meetings }}</div>
          <div class="text-neutral-600 text-sm">This Week</div>
        </div>
        <div class="stat-card border-warning-500">
          <div class="text-3xl font-bold text-warning-500">{{ stats.approved_this_month }}</div>
          <div class="text-neutral-600 text-sm">Approved (Month)</div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Today's Meetings -->
        <div class="lg:col-span-2 card">
          <h2 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
            <span class="text-2xl">📅</span> Today's Schedule
          </h2>
          <div v-if="todayMeetings.length === 0" class="text-neutral-500 text-center py-8">
            No meetings scheduled for today
          </div>
          <div v-else class="space-y-3">
            <div v-for="meeting in todayMeetings" :key="meeting.id" 
                 class="border-l-4 p-4 bg-neutral-50 rounded-r-lg"
                 :class="`meeting-${meeting.meeting_type}`">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="font-medium text-neutral-800">{{ meeting.title }}</h3>
                  <p class="text-sm text-neutral-600">{{ formatTime(meeting.start_time) }} - {{ formatTime(meeting.end_time) }}</p>
                  <p v-if="meeting.location" class="text-sm text-neutral-500">📍 {{ meeting.location }}</p>
                </div>
                <span :class="getStatusBadge(meeting.status)">{{ meeting.status }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Appointments -->
        <div class="card">
          <h2 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
            <span class="text-2xl">⏳</span> Pending Approvals
          </h2>
          <div v-if="pendingAppointments.length === 0" class="text-neutral-500 text-center py-8">
            No pending appointments
          </div>
          <div v-else class="space-y-3">
            <div v-for="apt in pendingAppointments" :key="apt.id" class="p-3 bg-neutral-50 rounded-lg">
              <div class="font-medium text-neutral-800">{{ apt.requester_name }}</div>
              <div class="text-sm text-neutral-600">{{ apt.purpose }}</div>
              <div class="text-xs text-neutral-500 mt-1">{{ formatDate(apt.requested_date) }}</div>
              <div class="flex gap-2 mt-2">
                <button @click="approveAppointment(apt.id)" class="text-xs btn btn-success px-2 py-1">Approve</button>
                <button @click="rejectAppointment(apt.id)" class="text-xs btn btn-danger px-2 py-1">Reject</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions & Todos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="card">
          <h2 class="text-lg font-semibold text-neutral-800 mb-4">Quick Actions</h2>
          <div class="grid grid-cols-2 gap-3">
            <a href="/appointments/create" class="p-4 bg-primary-50 rounded-lg text-center hover:bg-primary-100 transition">
              <div class="text-2xl mb-1">➕</div>
              <div class="text-sm font-medium text-primary-700">New Appointment</div>
            </a>
            <a href="/meetings/create" class="p-4 bg-success-50 rounded-lg text-center hover:bg-green-100 transition">
              <div class="text-2xl mb-1">📅</div>
              <div class="text-sm font-medium text-success-500">Schedule Meeting</div>
            </a>
            <a href="/calendar" class="p-4 bg-accent-50 rounded-lg text-center hover:bg-purple-100 transition">
              <div class="text-2xl mb-1">🗓️</div>
              <div class="text-sm font-medium text-accent-500">View Calendar</div>
            </a>
            <a href="/calendar/kiosk" class="p-4 bg-warning-50 rounded-lg text-center hover:bg-yellow-100 transition">
              <div class="text-2xl mb-1">📺</div>
              <div class="text-sm font-medium text-warning-500">Kiosk Mode</div>
            </a>
          </div>
        </div>

        <!-- Todos -->
        <div class="card">
          <h2 class="text-lg font-semibold text-neutral-800 mb-4 flex justify-between items-center">
            <span>My Tasks</span>
            <a href="/todos" class="text-sm text-primary-500 hover:underline">View All</a>
          </h2>
          <div v-if="todos.length === 0" class="text-neutral-500 text-center py-8">
            No pending tasks
          </div>
          <div v-else class="space-y-2">
            <div v-for="todo in todos" :key="todo.id" class="flex items-center gap-3 p-2 hover:bg-neutral-50 rounded">
              <input type="checkbox" :checked="todo.is_completed" @change="toggleTodo(todo.id)" 
                     class="rounded border-neutral-300 text-primary-500" />
              <span :class="{ 'line-through text-neutral-400': todo.is_completed }">{{ todo.title }}</span>
              <span :class="getPriorityBadge(todo.priority)" class="ml-auto text-xs">{{ todo.priority }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  stats: Object,
  todayMeetings: Array,
  upcomingMeetings: Array,
  pendingAppointments: Array,
  todos: Array,
  notifications: Array,
});

const formatTime = (datetime) => {
  return new Date(datetime).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
};

const getStatusBadge = (status) => `badge badge-${status}`;
const getPriorityBadge = (priority) => ({
  urgent: 'text-red-600 font-bold',
  high: 'text-orange-600',
  normal: 'text-neutral-600',
  low: 'text-neutral-400',
}[priority]);

const approveAppointment = async (id) => {
  await fetch(`/api/appointments/${id}/approve`, { method: 'POST' });
  location.reload();
};

const rejectAppointment = async (id) => {
  const reason = prompt('Rejection reason:');
  if (reason) {
    await fetch(`/api/appointments/${id}/reject`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ rejection_reason: reason }),
    });
    location.reload();
  }
};

const toggleTodo = async (id) => {
  await fetch(`/api/todos/${id}/toggle`, { method: 'POST' });
};
</script>
