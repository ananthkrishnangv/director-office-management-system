<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto">
      <div class="card">
        <h1 class="text-2xl font-bold text-neutral-800 mb-6">Request Appointment</h1>
        
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Requester Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Full Name *</label>
              <input v-model="form.requester_name" type="text" class="input" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Email *</label>
              <input v-model="form.requester_email" type="email" class="input" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Phone</label>
              <input v-model="form.requester_phone" type="tel" class="input" />
            </div>
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Organization</label>
              <input v-model="form.requester_organization" type="text" class="input" />
            </div>
          </div>

          <!-- Appointment Details -->
          <div>
            <label class="block text-sm font-medium text-neutral-700 mb-1">Purpose *</label>
            <input v-model="form.purpose" type="text" class="input" required />
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="3" class="input"></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Meeting Type *</label>
              <select v-model="form.meeting_type" class="input" required>
                <option v-for="(label, value) in meetingTypes" :key="value" :value="value">{{ label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Priority</label>
              <select v-model="form.priority" class="input">
                <option value="low">Low</option>
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Preferred Date *</label>
              <input v-model="form.requested_date" type="date" class="input" :min="minDate" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Start Time *</label>
              <input v-model="form.requested_start_time" type="time" class="input" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Duration (min)</label>
              <select v-model="form.duration_minutes" class="input">
                <option value="15">15 minutes</option>
                <option value="30">30 minutes</option>
                <option value="45">45 minutes</option>
                <option value="60">1 hour</option>
              </select>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" :disabled="form.processing" class="btn btn-primary flex-1">
              {{ form.processing ? 'Submitting...' : 'Submit Request' }}
            </button>
            <a href="/appointments" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  meetingTypes: Object,
  directors: Array,
});

const form = useForm({
  director_id: props.directors?.[0]?.id || 1,
  requester_name: '',
  requester_email: '',
  requester_phone: '',
  requester_organization: '',
  purpose: '',
  description: '',
  meeting_type: 'office_meeting',
  requested_date: '',
  requested_start_time: '10:00',
  duration_minutes: 30,
  priority: 'normal',
});

const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

const submit = () => {
  form.post('/appointments', {
    onSuccess: () => form.reset(),
  });
};
</script>
