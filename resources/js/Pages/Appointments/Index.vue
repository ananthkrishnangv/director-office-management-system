<template>
  <AppLayout>
    <div class="space-y-6 container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-base-content font-heading">Appointments</h1>
          <p class="text-base-content/70 mt-1">Manage and track appointment requests</p>
        </div>
        <Link href="/appointments/create" class="btn btn-primary gap-2">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
           New Appointment
        </Link>
      </div>

      <!-- Filters -->
      <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="form-control">
               <label class="label"><span class="label-text">Status</span></label>
               <select v-model="filters.status" class="select select-bordered w-full">
                 <option value="">All Status</option>
                 <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
               </select>
            </div>
            
            <div class="form-control">
               <label class="label"><span class="label-text">Search</span></label>
               <div class="relative">
                  <input v-model="filters.search" type="text" class="input input-bordered w-full pr-10" placeholder="Search requester..." />
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute right-3 top-3.5 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
               </div>
            </div>

            <div class="form-control">
               <label class="label"><span class="label-text">From Date</span></label>
               <input v-model="filters.date_from" type="date" class="input input-bordered w-full" />
            </div>

            <div class="form-control">
               <label class="label"><span class="label-text">To Date</span></label>
               <input v-model="filters.date_to" type="date" class="input input-bordered w-full" />
            </div>
          </div>
          <div class="flex justify-end mt-4">
             <button @click="resetFilters" class="btn btn-ghost btn-sm">Reset</button>
             <button @click="applyFilters" class="btn btn-primary btn-sm ml-2">Apply</button>
          </div>
        </div>
      </div>

      <!-- Appointments Table -->
      <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="table w-full">
            <thead class="bg-base-200/50">
              <tr>
                <th>Requester</th>
                <th>Purpose</th>
                <th>Date & Time</th>
                <th>Type</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="appointments.data.length === 0">
                 <td colspan="6" class="text-center py-10">
                    <div class="flex flex-col items-center opacity-60">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                       <p>No appointments found</p>
                    </div>
                 </td>
              </tr>
              <tr v-for="apt in appointments.data" :key="apt.id" class="hover group">
                <td>
                  <div class="flex items-center gap-3">
                    <div class="avatar placeholder">
                      <div class="bg-neutral text-neutral-content rounded-full w-10">
                        <span class="text-xs">{{ getInitials(apt.requester_name) }}</span>
                      </div>
                    </div>
                    <div>
                      <div class="font-bold">{{ apt.requester_name }}</div>
                      <div class="text-xs opacity-50">{{ apt.requester_email }}</div>
                    </div>
                  </div>
                </td>
                <td class="max-w-xs">
                   <div class="truncate font-medium" :title="apt.purpose">{{ apt.purpose }}</div>
                   <div class="text-xs opacity-50 truncate">{{ apt.organization || 'External' }}</div>
                </td>
                <td>
                  <div class="font-mono text-sm">{{ formatDate(apt.requested_date) }}</div>
                  <div class="text-xs badge badge-ghost badge-sm mt-0.5">{{ formatTime(apt.requested_start_time) }}</div>
                </td>
                <td>
                  <div class="badge badge-outline">{{ apt.meeting_type }}</div>
                </td>
                <td>
                  <span class="badge font-bold uppercase" :class="getStatusBadge(apt.status)">{{ apt.status }}</span>
                </td>
                <td class="text-right">
                  <div class="flex justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                    <template v-if="apt.status === 'pending'">
                       <button @click="approve(apt)" class="btn btn-success btn-xs text-white" title="Approve">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                       </button>
                       <button @click="reject(apt)" class="btn btn-error btn-xs text-white" title="Reject">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                       </button>
                    </template>
                    <Link :href="`/appointments/${apt.id}`" class="btn btn-ghost btn-xs">View</Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="appointments.links && appointments.links.length > 3" class="flex justify-center mt-6">
         <div class="join">
            <template v-for="(link, index) in appointments.links" :key="index">
               <Link 
                  v-if="link.url" 
                  :href="link.url" 
                  class="join-item btn btn-sm" 
                  :class="{ 'btn-active': link.active }"
                  v-html="link.label"
               />
               <button v-else class="join-item btn btn-sm btn-disabled" v-html="link.label"></button>
            </template>
         </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'; // Added watch
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';
import debounce from 'lodash/debounce'; // Make sure lodash is installed? If not we can simulate or remove. Usually standard in Laravel/Vue setups.

const props = defineProps({
  appointments: Object,
  filters: Object,
  statuses: Object,
});

const filters = ref({ ...props.filters });

// Watch filters for auto-fetch (debounced for search)
watch(filters, debounce(() => {
   router.get('/appointments', filters.value, { preserveState: true, preserveScroll: true, replace: true });
}, 300), { deep: true });

const applyFilters = () => {
   router.get('/appointments', filters.value, { preserveState: true });
};

const resetFilters = () => {
   filters.value = { status: '', search: '', date_from: '', date_to: '' };
   applyFilters();
};

const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
const formatTime = (time) => {
   if (!time) return '';
   const [hours, minutes] = time.split(':');
   const d = new Date();
   d.setHours(hours);
   d.setMinutes(minutes);
   return d.toLocaleTimeString('en-IN', { hour: 'numeric', minute: '2-digit', hour12: true });
};

const getInitials = (name) => {
   return name ? name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : '??';
};

const getStatusBadge = (status) => ({
  pending: 'badge-warning',
  approved: 'badge-success text-white',
  rejected: 'badge-error text-white',
  completed: 'badge-neutral',
  cancelled: 'badge-ghost'
}[status] || 'badge-ghost');

const approve = (apt) => {
  if (confirm(`Approve appointment for ${apt.requester_name}?`)) {
    router.post(`/appointments/${apt.id}/approve`, {}, {
       onSuccess: () => { /* Optional toast */ },
       preserveScroll: true
    });
  }
};

const reject = (apt) => {
  const reason = prompt('Please provide a reason for rejection:');
  if (reason) {
    router.post(`/appointments/${apt.id}/reject`, {
       rejection_reason: reason
    }, {
       onSuccess: () => { /* Optional toast */ },
       preserveScroll: true
    });
  }
};
</script>
