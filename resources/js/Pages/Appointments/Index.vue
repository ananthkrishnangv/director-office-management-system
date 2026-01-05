<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-neutral-800">Appointments</h1>
          <p class="text-neutral-600">Manage appointment requests</p>
        </div>
        <a href="/appointments/create" class="btn btn-primary">+ New Appointment</a>
      </div>

      <!-- Filters -->
      <div class="card">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <select v-model="filters.status" class="input">
            <option value="">All Status</option>
            <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
          </select>
          <input v-model="filters.search" type="text" class="input" placeholder="Search..." />
          <input v-model="filters.date_from" type="date" class="input" />
          <input v-model="filters.date_to" type="date" class="input" />
        </div>
      </div>

      <!-- Appointments Table -->
      <div class="card overflow-hidden p-0">
        <table class="w-full">
          <thead class="bg-neutral-50 border-b">
            <tr>
              <th class="text-left p-4 font-medium text-neutral-600">Requester</th>
              <th class="text-left p-4 font-medium text-neutral-600">Purpose</th>
              <th class="text-left p-4 font-medium text-neutral-600">Date & Time</th>
              <th class="text-left p-4 font-medium text-neutral-600">Type</th>
              <th class="text-left p-4 font-medium text-neutral-600">Status</th>
              <th class="text-left p-4 font-medium text-neutral-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100">
            <tr v-for="apt in appointments.data" :key="apt.id" class="hover:bg-neutral-50">
              <td class="p-4">
                <div class="font-medium text-neutral-800">{{ apt.requester_name }}</div>
                <div class="text-sm text-neutral-500">{{ apt.requester_email }}</div>
              </td>
              <td class="p-4 max-w-xs truncate">{{ apt.purpose }}</td>
              <td class="p-4">
                <div>{{ formatDate(apt.requested_date) }}</div>
                <div class="text-sm text-neutral-500">{{ apt.requested_start_time }}</div>
              </td>
              <td class="p-4">
                <span class="badge bg-neutral-100 text-neutral-700">{{ apt.meeting_type }}</span>
              </td>
              <td class="p-4">
                <span :class="getStatusClass(apt.status)">{{ apt.status }}</span>
              </td>
              <td class="p-4">
                <div class="flex gap-2">
                  <button v-if="apt.status === 'pending'" @click="approve(apt)" class="text-green-600 hover:underline text-sm">Approve</button>
                  <button v-if="apt.status === 'pending'" @click="reject(apt)" class="text-red-600 hover:underline text-sm">Reject</button>
                  <a :href="`/appointments/${apt.id}`" class="text-primary-500 hover:underline text-sm">View</a>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="appointments.links" class="flex justify-center gap-1">
        <template v-for="link in appointments.links" :key="link.label">
          <a v-if="link.url" :href="link.url" class="px-3 py-1 rounded" :class="{ 'bg-primary-500 text-white': link.active, 'bg-neutral-100': !link.active }" v-html="link.label"></a>
        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  appointments: Object,
  filters: Object,
  statuses: Object,
});

const filters = ref(props.filters || {});

const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });

const getStatusClass = (status) => ({
  pending: 'badge badge-pending',
  approved: 'badge badge-approved',
  rejected: 'badge badge-rejected',
  completed: 'badge badge-completed',
}[status] || 'badge');

const approve = async (apt) => {
  if (confirm('Approve this appointment?')) {
    await fetch(`/appointments/${apt.id}/approve`, { method: 'POST' });
    location.reload();
  }
};

const reject = async (apt) => {
  const reason = prompt('Rejection reason:');
  if (reason) {
    await fetch(`/appointments/${apt.id}/reject`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ rejection_reason: reason }),
    });
    location.reload();
  }
};
</script>
