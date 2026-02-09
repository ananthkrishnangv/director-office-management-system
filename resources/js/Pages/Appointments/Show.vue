<template>
  <AppLayout>
    <div class="space-y-6 container mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-base-content font-heading">Appointment Details</h1>
          <p class="text-base-content/70 mt-1">View and manage appointment request</p>
        </div>
        <Link href="/appointments" class="btn btn-ghost gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
          Back to List
        </Link>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 gap-6">
        <!-- Status Card -->
        <div class="card bg-base-100 shadow-xl border border-base-200">
          <div class="card-body">
             <div class="flex justify-between items-center">
                <div>
                   <h2 class="card-title text-base-content">Status</h2>
                   <p class="text-base-content/70 text-sm">Current state of the request</p>
                </div>
                <div class="badge badge-lg" :class="getStatusBadge(appointment.status)">{{ appointment.status }}</div>
             </div>
             
             <!-- Actions (Only if Pending) -->
             <div v-if="appointment.status === 'pending'" class="divider"></div>
             <div v-if="appointment.status === 'pending'" class="flex gap-4 justify-end">
                <button @click="reject" class="btn btn-error text-white">Reject Request</button>
                <button @click="approve" class="btn btn-success text-white">Approve Request</button>
             </div>
          </div>
        </div>

        <!-- Requester Details -->
        <div class="card bg-base-100 shadow-xl border border-base-200">
          <div class="card-body">
            <h2 class="card-title text-base-content mb-4">Requester Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label class="label text-base-content/70 font-semibold text-xs uppercase tracking-wide">Name</label>
                  <div class="text-lg font-medium">{{ appointment.requester_name }}</div>
               </div>
               <div>
                  <label class="label text-base-content/70 font-semibold text-xs uppercase tracking-wide">Email</label>
                  <div class="text-lg">{{ appointment.requester_email }}</div>
               </div>
               <div>
                  <label class="label text-base-content/70 font-semibold text-xs uppercase tracking-wide">Phone</label>
                  <div class="text-lg">{{ appointment.requester_phone || 'N/A' }}</div>
               </div>
               <div>
                  <label class="label text-base-content/70 font-semibold text-xs uppercase tracking-wide">Organization</label>
                  <div class="text-lg">{{ appointment.requester_organization || 'Internal' }}</div>
               </div>
            </div>
          </div>
        </div>

        <!-- Meeting Details -->
        <div class="card bg-base-100 shadow-xl border border-base-200">
          <div class="card-body">
            <h2 class="card-title text-base-content mb-4">Meeting Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label class="label text-base-content/70 font-semibold text-xs uppercase tracking-wide">Purpose</label>
                  <div class="text-lg font-medium">{{ appointment.purpose }}</div>
               </div>
               <div>
                  <label class="label text-base-content/70 font-semibold text-xs uppercase tracking-wide">Type</label>
                  <div class="badge badge-outline mt-1">{{ appointment.meeting_type }}</div>
               </div>
               <div>
                  <label class="label text-base-content/70 font-semibold text-xs uppercase tracking-wide">Requested Date</label>
                  <div class="text-lg font-mono">{{ formatDate(appointment.requested_date) }}</div>
               </div>
               <div>
                  <label class="label text-base-content/70 font-semibold text-xs uppercase tracking-wide">Time Slot</label>
                  <div class="text-lg font-mono">{{ formatTime(appointment.requested_start_time) }} - {{ formatTime(appointment.requested_end_time) }}</div>
               </div>
               <div class="md:col-span-2">
                  <label class="label text-base-content/70 font-semibold text-xs uppercase tracking-wide">Description/Notes</label>
                  <div class="p-4 bg-base-200 rounded-xl whitespace-pre-wrap">{{ appointment.description || 'No additional details provided.' }}</div>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  appointment: Object,
});

const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
const formatTime = (time) => {
   if (!time) return 'N/A';
   const [hours, minutes] = time.split(':');
   const d = new Date();
   d.setHours(hours);
   d.setMinutes(minutes);
   return d.toLocaleTimeString('en-IN', { hour: 'numeric', minute: '2-digit', hour12: true });
};

const getStatusBadge = (status) => ({
  pending: 'badge-warning',
  approved: 'badge-success text-white',
  rejected: 'badge-error text-white',
  completed: 'badge-neutral',
  cancelled: 'badge-ghost'
}[status] || 'badge-ghost');

const approve = () => {
  if (confirm(`Approve appointment for ${props.appointment.requester_name}?`)) {
    router.post(`/appointments/${props.appointment.id}/approve`, {}, {
       onSuccess: () => { /* toast handled by layout */ }
    });
  }
};

const reject = () => {
  const reason = prompt('Please provide a reason for rejection:');
  if (reason) {
    router.post(`/appointments/${props.appointment.id}/reject`, {
       rejection_reason: reason
    }, {
       onSuccess: () => { /* toast handled by layout */ }
    });
  }
};
</script>
