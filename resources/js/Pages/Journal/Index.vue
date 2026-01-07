<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-neutral-800">Journal</h1>
          <p class="text-neutral-500 text-sm mt-1">Record daily notes and reflections</p>
        </div>
      </div>

      <!-- Add Entry Form -->
      <div class="fluent-card p-6">
        <h3 class="font-semibold text-neutral-800 mb-4">New Journal Entry</h3>
        <form @submit.prevent="addEntry" class="space-y-4">
          <div>
            <input 
              type="text" 
              v-model="newEntry.title" 
              placeholder="Entry title..." 
              class="fluent-input"
              required
            />
          </div>
          <div>
            <textarea 
              v-model="newEntry.content" 
              placeholder="What's on your mind today?" 
              rows="4"
              class="fluent-input"
              required
            ></textarea>
          </div>
          <div class="flex flex-wrap gap-4">
            <select v-model="newEntry.mood" class="fluent-select w-auto min-w-[150px]">
              <option value="">Select Mood</option>
              <option value="happy">😊 Happy</option>
              <option value="productive">💪 Productive</option>
              <option value="neutral">😐 Neutral</option>
              <option value="stressed">😓 Stressed</option>
              <option value="tired">😴 Tired</option>
            </select>
            <button type="submit" class="btn btn-primary" :disabled="!newEntry.title || !newEntry.content">
              <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              Add Entry
            </button>
          </div>
        </form>
      </div>

      <!-- Entries List -->
      <div class="space-y-4">
        <div v-if="entries.length > 0">
          <div 
            v-for="entry in entries" 
            :key="entry.id" 
            class="fluent-card p-6 hover:shadow-md transition-all"
          >
            <div class="flex items-start justify-between gap-4 mb-3">
              <div>
                <h3 class="font-semibold text-neutral-800 text-lg">{{ entry.title }}</h3>
                <div class="flex items-center gap-3 mt-1 text-sm text-neutral-500">
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ formatDate(entry.created_at) }}
                  </span>
                  <span v-if="entry.mood" class="px-2 py-0.5 bg-neutral-100 rounded-full text-xs font-medium">
                    {{ getMoodEmoji(entry.mood) }} {{ entry.mood }}
                  </span>
                </div>
              </div>
              <button @click="deleteEntry(entry.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
            <p class="text-neutral-600 whitespace-pre-line">{{ entry.content }}</p>
          </div>
        </div>
        
        <div v-else class="fluent-card">
          <div class="empty-state">
            <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p class="font-medium text-neutral-600">No journal entries yet</p>
            <p class="text-sm">Start writing to capture your thoughts</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Components/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  entries: { type: Array, default: () => [] },
});

const newEntry = ref({
  title: '',
  content: '',
  mood: ''
});

const formatDate = (dt) => {
  if (!dt) return '';
  return new Date(dt).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
};

const getMoodEmoji = (mood) => ({
  happy: '😊',
  productive: '💪',
  neutral: '😐',
  stressed: '😓',
  tired: '😴'
}[mood] || '');

const addEntry = () => {
  if (!newEntry.value.title || !newEntry.value.content) return;
  router.post('/journal', newEntry.value, {
    onSuccess: () => {
      newEntry.value = { title: '', content: '', mood: '' };
    }
  });
};

const deleteEntry = (id) => {
  if (confirm('Delete this journal entry?')) {
    router.delete(`/journal/${id}`);
  }
};
</script>
