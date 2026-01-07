<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-neutral-800">Meeting Archive</h1>
          <p class="text-neutral-500 mt-1">Search and manage all meeting records</p>
        </div>
        <div class="mt-4 md:mt-0">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search meetings..."
              class="fluent-input pl-10 w-full md:w-80"
              @input="debouncedSearch"
            />
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Status Tabs -->
      <div class="fluent-card p-1 mb-6">
        <div class="flex flex-wrap gap-1">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="setFilter(tab.key)"
            class="px-4 py-2 rounded-xl text-sm font-medium transition-all"
            :class="currentStatus === tab.key 
              ? 'bg-blue-500 text-white shadow-sm' 
              : 'text-neutral-600 hover:bg-neutral-50'"
          >
            {{ tab.label }}
            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs" :class="currentStatus === tab.key ? 'bg-white/20' : 'bg-neutral-100'">
              {{ counts[tab.key] || 0 }}
            </span>
          </button>
        </div>
      </div>

      <!-- Meetings Table -->
      <div class="fluent-card overflow-hidden">
        <table class="fluent-table">
          <thead>
            <tr>
              <th>Date & Time</th>
              <th>Title</th>
              <th>Visitor</th>
              <th>Type</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="meeting in meetings.data" :key="meeting.id" class="cursor-pointer hover:bg-blue-50/50" @click="openMeetingModal(meeting)">
              <td>
                <div class="font-medium text-neutral-800">{{ formatDate(meeting.scheduled_date) }}</div>
                <div class="text-xs text-neutral-500">{{ meeting.start_time }} - {{ meeting.end_time }}</div>
              </td>
              <td>
                <div class="font-medium text-neutral-800">{{ meeting.title }}</div>
                <div class="text-xs text-neutral-500 truncate max-w-xs">{{ meeting.description }}</div>
              </td>
              <td>
                <div class="font-medium text-neutral-800">{{ meeting.visitor_name }}</div>
                <div class="text-xs text-neutral-500">{{ meeting.visitor_organization }}</div>
              </td>
              <td>
                <span class="badge" :class="getTypeBadge(meeting.meeting_type)">
                  {{ meeting.meeting_type }}
                </span>
              </td>
              <td>
                <span class="badge" :class="getStatusBadge(meeting.status)">
                  {{ meeting.status }}
                </span>
              </td>
              <td>
                <button @click.stop="openMeetingModal(meeting)" class="btn btn-secondary text-xs py-1.5 px-3">
                  View Details
                </button>
              </td>
            </tr>
            <tr v-if="!meetings.data?.length">
              <td colspan="6" class="text-center py-12 text-neutral-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                No meetings found
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="meetings.last_page > 1" class="px-4 py-3 border-t border-neutral-100 flex items-center justify-between">
          <span class="text-sm text-neutral-500">
            Showing {{ meetings.from }} to {{ meetings.to }} of {{ meetings.total }} meetings
          </span>
          <div class="flex gap-2">
            <button 
              v-for="page in meetings.last_page" 
              :key="page"
              @click="goToPage(page)"
              class="px-3 py-1 rounded-lg text-sm"
              :class="page === meetings.current_page ? 'bg-blue-500 text-white' : 'bg-neutral-100 hover:bg-neutral-200'"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Meeting Detail Modal -->
    <div v-if="selectedMeeting" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeMeetingModal">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto animate-scale-in">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-neutral-100 px-6 py-4 flex items-center justify-between">
          <h2 class="text-lg font-bold text-neutral-800">Meeting Details</h2>
          <button @click="closeMeetingModal" class="p-2 hover:bg-neutral-100 rounded-xl transition-colors">
            <svg class="w-5 h-5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
          <!-- Meeting Info -->
          <div class="grid md:grid-cols-2 gap-4">
            <div class="bg-neutral-50 rounded-xl p-4">
              <p class="text-xs text-neutral-500 uppercase font-medium">Date & Time</p>
              <p class="font-semibold text-neutral-800 mt-1">{{ formatDate(selectedMeeting.scheduled_date) }}</p>
              <p class="text-sm text-neutral-600">{{ selectedMeeting.start_time }} - {{ selectedMeeting.end_time }}</p>
            </div>
            <div class="bg-neutral-50 rounded-xl p-4">
              <p class="text-xs text-neutral-500 uppercase font-medium">Status</p>
              <span class="badge mt-2" :class="getStatusBadge(selectedMeeting.status)">{{ selectedMeeting.status }}</span>
            </div>
          </div>

          <div>
            <h3 class="font-semibold text-neutral-800 mb-2">{{ selectedMeeting.title }}</h3>
            <p class="text-neutral-600 text-sm">{{ selectedMeeting.description }}</p>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-neutral-500 uppercase font-medium mb-1">Visitor</p>
              <p class="font-medium text-neutral-800">{{ selectedMeeting.visitor_name }}</p>
              <p class="text-sm text-neutral-500">{{ selectedMeeting.visitor_organization }}</p>
              <p class="text-sm text-neutral-500">{{ selectedMeeting.visitor_email }}</p>
            </div>
            <div>
              <p class="text-xs text-neutral-500 uppercase font-medium mb-1">Contact</p>
              <p class="text-sm text-neutral-600">{{ selectedMeeting.visitor_phone }}</p>
            </div>
          </div>

          <!-- Meeting Notes -->
          <div>
            <label class="text-sm font-semibold text-neutral-700 mb-2 block">Meeting Notes / Minutes</label>
            <textarea
              v-model="meetingForm.notes"
              rows="4"
              class="fluent-input"
              placeholder="Add meeting notes, minutes, or outcomes..."
            ></textarea>
          </div>

          <!-- Document Upload -->
          <div>
            <label class="text-sm font-semibold text-neutral-700 mb-2 block">Documents</label>
            <div class="border-2 border-dashed border-neutral-200 rounded-xl p-6 text-center">
              <input type="file" ref="fileInput" @change="handleFileUpload" class="hidden" accept=".pdf,.doc,.docx,.jpg,.png" />
              <button @click="$refs.fileInput.click()" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Upload Document
              </button>
              <p class="text-xs text-neutral-400 mt-2">PDF, DOC, DOCX, JPG, PNG (max 10MB)</p>
            </div>

            <!-- Uploaded Documents List -->
            <div v-if="selectedMeeting.documents?.length" class="mt-3 space-y-2">
              <div v-for="(doc, index) in selectedMeeting.documents" :key="index" class="flex items-center gap-3 p-2 bg-neutral-50 rounded-lg">
                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm text-neutral-700 flex-1">{{ doc.name }}</span>
                <a :href="`/storage/${doc.path}`" target="_blank" class="text-blue-600 text-xs hover:underline">View</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-white border-t border-neutral-100 px-6 py-4 flex justify-end gap-3">
          <button @click="closeMeetingModal" class="btn btn-secondary">Cancel</button>
          <button @click="saveMeetingDetails" :disabled="isSaving" class="btn btn-primary">
            {{ isSaving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  meetings: Object,
  counts: Object,
  filters: Object,
});

const page = usePage();
const searchQuery = ref(props.filters?.search || '');
const currentStatus = ref(props.filters?.status || 'all');
const selectedMeeting = ref(null);
const meetingForm = ref({ notes: '' });
const isSaving = ref(false);
const fileInput = ref(null);

const tabs = [
  { key: 'all', label: 'All Meetings' },
  { key: 'approved', label: 'Approved' },
  { key: 'completed', label: 'Completed' },
  { key: 'cancelled', label: 'Cancelled' },
  { key: 'rejected', label: 'Rejected' },
  { key: 'walkin', label: 'Walk-in' },
];

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/archive', { status: currentStatus.value, search: searchQuery.value }, { preserveState: true });
  }, 300);
};

const setFilter = (status) => {
  currentStatus.value = status;
  router.get('/archive', { status, search: searchQuery.value }, { preserveState: true });
};

const goToPage = (page) => {
  router.get('/archive', { status: currentStatus.value, search: searchQuery.value, page }, { preserveState: true });
};

const openMeetingModal = (meeting) => {
  selectedMeeting.value = meeting;
  meetingForm.value.notes = meeting.notes || '';
};

const closeMeetingModal = () => {
  selectedMeeting.value = null;
};

const saveMeetingDetails = () => {
  isSaving.value = true;
  router.put(`/archive/${selectedMeeting.value.id}`, meetingForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      isSaving.value = false;
      closeMeetingModal();
    },
    onError: () => {
      isSaving.value = false;
    },
  });
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const formData = new FormData();
  formData.append('document', file);
  
  router.post(`/archive/${selectedMeeting.value.id}/upload`, formData, {
    preserveScroll: true,
    onSuccess: () => {
      event.target.value = '';
    },
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-IN', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

const getStatusBadge = (status) => ({
  pending: 'badge-pending',
  approved: 'badge-approved',
  completed: 'badge-approved',
  rejected: 'badge-rejected',
  cancelled: 'bg-gray-100 text-gray-700',
}[status] || 'bg-neutral-100 text-neutral-700');

const getTypeBadge = (type) => ({
  scheduled: 'badge-scheduled',
  walkin: 'bg-orange-100 text-orange-700',
  emergency: 'bg-red-100 text-red-700',
}[type] || 'bg-neutral-100 text-neutral-700');
</script>
