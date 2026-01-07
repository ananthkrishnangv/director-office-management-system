<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto">
      <!-- Page Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-neutral-800">Director's Availability</h1>
          <p class="text-neutral-500 mt-1">Block dates when the Director is unavailable</p>
        </div>
        <button @click="showAddModal = true" class="btn btn-primary">
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Block Date
        </button>
      </div>

      <!-- Blocked Dates List -->
      <div class="fluent-card overflow-hidden">
        <div class="p-4 border-b border-neutral-200">
          <h2 class="font-semibold text-neutral-700">Upcoming Blocked Dates</h2>
        </div>
        
        <div v-if="blockedDates.length">
          <div 
            v-for="date in blockedDates" 
            :key="date.id"
            class="flex items-center justify-between p-4 border-b border-neutral-100 last:border-0"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
              </div>
              <div>
                <p class="font-medium text-neutral-800">{{ formatDate(date.blocked_date) }}</p>
                <p class="text-sm text-neutral-500 capitalize">{{ formatReason(date.block_reason) }}</p>
                <p v-if="date.notes" class="text-xs text-neutral-400 mt-1">{{ date.notes }}</p>
              </div>
            </div>
            <button @click="removeBlockedDate(date.id)" class="btn btn-danger text-xs py-1.5 px-3">
              Remove
            </button>
          </div>
        </div>

        <div v-else class="p-12 text-center text-neutral-400">
          <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p>No blocked dates scheduled</p>
          <p class="text-sm mt-1">Click "Block Date" to add unavailability</p>
        </div>
      </div>

      <!-- Block Reasons Legend -->
      <div class="mt-6 fluent-card p-4">
        <h3 class="font-semibold text-neutral-700 mb-3">Block Reasons</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
            <span class="text-sm text-neutral-600">On Tour</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-red-500"></span>
            <span class="text-sm text-neutral-600">Urgent Meeting</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
            <span class="text-sm text-neutral-600">Scheduled Meeting</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-green-500"></span>
            <span class="text-sm text-neutral-600">On Leave</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Block Date Modal -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showAddModal = false">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md animate-scale-in">
        <div class="p-6 border-b border-neutral-100">
          <h2 class="text-lg font-bold text-neutral-800">Block Date</h2>
          <p class="text-sm text-neutral-500 mt-1">Mark a date as unavailable for meetings</p>
        </div>

        <form @submit.prevent="submitBlockDate" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-neutral-700 mb-1">Date</label>
            <input 
              type="date" 
              v-model="form.blocked_date" 
              :min="today"
              class="fluent-input"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 mb-1">Reason</label>
            <select v-model="form.reason" class="fluent-select" required>
              <option value="">Select reason...</option>
              <option value="on_tour">On Tour</option>
              <option value="urgent_meeting">Urgent Meeting</option>
              <option value="scheduled_meeting">Scheduled Meeting</option>
              <option value="on_leave">On Leave</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 mb-1">Notes (Optional)</label>
            <textarea 
              v-model="form.notes" 
              class="fluent-input" 
              rows="2"
              placeholder="Additional details..."
            ></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button type="button" @click="showAddModal = false" class="btn btn-secondary">Cancel</button>
            <button type="submit" :disabled="!form.blocked_date || !form.reason" class="btn btn-primary">
              Block Date
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  blockedDates: {
    type: Array,
    default: () => [],
  },
});

const showAddModal = ref(false);
const today = new Date().toISOString().split('T')[0];

const form = ref({
  blocked_date: '',
  reason: '',
  notes: '',
});

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-IN', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
};

const formatReason = (reason) => {
  if (!reason) return '';
  return reason.replace(/_/g, ' ');
};

const submitBlockDate = () => {
  router.post('/blocked-dates', form.value, {
    preserveScroll: true,
    onSuccess: () => {
      showAddModal.value = false;
      form.value = { blocked_date: '', reason: '', notes: '' };
    },
  });
};

const removeBlockedDate = (id) => {
  if (confirm('Are you sure you want to remove this blocked date?')) {
    router.delete(`/blocked-dates/${id}`, { preserveScroll: true });
  }
};
</script>
