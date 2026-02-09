<template>
  <AppLayout>
    <div class="max-w-3xl mx-auto space-y-6">
      <!-- Page Header -->
      <div class="flex items-center gap-4">
        <a href="/meetings" class="p-2 hover:bg-neutral-100 rounded-xl transition-colors">
          <svg class="w-6 h-6 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </a>
        <div>
          <h1 class="text-2xl font-bold text-neutral-800 font-heading">Schedule Meeting</h1>
          <p class="text-neutral-500 mt-1">Create a new meeting or appointment</p>
        </div>
      </div>

      <!-- Meeting Form -->
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Basic Info Card -->
        <div class="card bg-base-100 shadow-xl border border-base-200 p-6">
          <h2 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
            <span class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </span>
            Meeting Details
          </h2>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Title *</label>
              <input v-model="form.title" type="text" required class="input input-bordered w-full" placeholder="Enter meeting title" />
              <p v-if="errors.title" class="text-error text-sm mt-1">{{ errors.title }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Description</label>
              <textarea v-model="form.description" rows="3" class="textarea textarea-bordered w-full" placeholder="Describe the purpose of the meeting"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Meeting Type *</label>
                <select v-model="form.meeting_type" required class="select select-bordered w-full">
                  <option value="">Select type</option>
                  <option value="one_on_one">One on One</option>
                  <option value="team">Team Meeting</option>
                  <option value="external">External Visitor</option>
                  <option value="review">Review Meeting</option>
                  <option value="official">Official</option>
                  <option value="personal">Personal</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Priority</label>
                <select v-model="form.priority" class="select select-bordered w-full">
                  <option value="normal">Normal</option>
                  <option value="low">Low</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Date & Time Card -->
        <div class="card bg-base-100 shadow-xl border border-base-200 p-6">
          <h2 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
            <span class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </span>
            Date & Time
          </h2>

          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <input type="checkbox" v-model="form.is_all_day" id="allDay" class="checkbox checkbox-primary" />
              <label for="allDay" class="text-sm text-neutral-700">All day event</label>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Start Date & Time *</label>
                <input v-model="form.start_time" :type="form.is_all_day ? 'date' : 'datetime-local'" required class="input input-bordered w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">End Date & Time *</label>
                <input v-model="form.end_time" :type="form.is_all_day ? 'date' : 'datetime-local'" required class="input input-bordered w-full" />
              </div>
            </div>
          </div>
        </div>

        <!-- Location Card -->
        <div class="card bg-base-100 shadow-xl border border-base-200 p-6">
          <h2 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
            <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </span>
            Location
          </h2>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Location/Venue</label>
              <input v-model="form.location" type="text" class="input input-bordered w-full" placeholder="e.g., Director's Office, Conference Room" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Room Number</label>
                <input v-model="form.room_number" type="text" class="input input-bordered w-full" placeholder="e.g., 101" />
              </div>
              <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Online Meeting Link</label>
                <input v-model="form.online_link" type="url" class="input input-bordered w-full" placeholder="https://meet.google.com/..." />
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Card -->
        <div class="card bg-base-100 shadow-xl border border-base-200 p-6">
          <h2 class="text-lg font-semibold text-neutral-800 mb-4 flex items-center gap-2">
            <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </span>
            Additional Notes
          </h2>

          <textarea v-model="form.notes" rows="4" class="textarea textarea-bordered w-full" placeholder="Any additional notes or preparation required..."></textarea>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-3">
          <a href="/meetings" class="btn btn-neutral btn-outline">Cancel</a>
          <button type="submit" class="btn btn-primary text-white" :disabled="processing">
            <span v-if="processing" class="flex items-center gap-2">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              Scheduling...
            </span>
            <span v-else>Schedule Meeting</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import AppLayout from '@/Components/AppLayout.vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
  directors: { type: Array, default: () => [] }
});

const page = usePage();
const processing = ref(false);
const errors = ref({});

const form = reactive({
  director_id: page.props.auth?.user?.role === 'director' ? page.props.auth.user.id : (props.directors[0]?.id || ''),
  title: '',
  description: '',
  meeting_type: '',
  priority: 'normal',
  start_time: '',
  end_time: '',
  is_all_day: false,
  location: '',
  room_number: '',
  online_link: '',
  notes: '',
  is_private: false,
  color: ''
});

const submitForm = () => {
  processing.value = true;
  errors.value = {};
  
  router.post('/meetings', form, {
    onSuccess: () => {
      processing.value = false;
    },
    onError: (errs) => {
      errors.value = errs;
      processing.value = false;
    }
  });
};
</script>
