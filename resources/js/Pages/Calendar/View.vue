<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-neutral-800">Calendar</h1>
          <p class="text-neutral-600">View and manage your schedule</p>
        </div>
        <div class="flex gap-2">
          <button @click="viewMode = 'month'" :class="viewBtn('month')">Month</button>
          <button @click="viewMode = 'week'" :class="viewBtn('week')">Week</button>
          <button @click="viewMode = 'day'" :class="viewBtn('day')">Day</button>
        </div>
      </div>

      <!-- Calendar Navigation -->
      <div class="card">
        <div class="flex justify-between items-center mb-6">
          <button @click="prevPeriod" class="btn btn-secondary">← Previous</button>
          <h2 class="text-xl font-semibold">{{ currentPeriodLabel }}</h2>
          <button @click="nextPeriod" class="btn btn-secondary">Next →</button>
        </div>

        <!-- Month View -->
        <div v-if="viewMode === 'month'" class="grid grid-cols-7 gap-px bg-neutral-200 rounded-lg overflow-hidden">
          <div v-for="day in weekDays" :key="day" class="bg-neutral-100 p-2 text-center text-sm font-medium text-neutral-600">
            {{ day }}
          </div>
          <div v-for="date in calendarDates" :key="date.key" 
               class="bg-white min-h-[100px] p-2"
               :class="{ 'bg-primary-50': date.isToday, 'opacity-50': !date.isCurrentMonth }">
            <div class="text-sm font-medium" :class="{ 'text-primary-600': date.isToday }">{{ date.day }}</div>
            <div class="mt-1 space-y-1">
              <div v-for="event in date.events" :key="event.id" 
                   class="text-xs p-1 rounded truncate"
                   :style="{ backgroundColor: event.color + '20', borderLeft: `3px solid ${event.color}` }">
                {{ event.title }}
              </div>
            </div>
          </div>
        </div>

        <!-- Day View -->
        <div v-if="viewMode === 'day'" class="space-y-2">
          <div v-for="hour in dayHours" :key="hour" class="flex border-b border-neutral-100 py-2">
            <div class="w-20 text-sm text-neutral-500">{{ hour }}:00</div>
            <div class="flex-1 min-h-[60px] rounded bg-neutral-50 p-1">
              <div v-for="event in getEventsForHour(hour)" :key="event.id"
                   class="p-2 rounded mb-1"
                   :style="{ backgroundColor: event.color, color: 'white' }">
                <div class="font-medium">{{ event.title }}</div>
                <div class="text-sm opacity-80">{{ formatTime(event.start) }} - {{ formatTime(event.end) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="card">
        <h3 class="font-medium text-neutral-800 mb-3">Meeting Types</h3>
        <div class="flex flex-wrap gap-4">
          <div v-for="(color, type) in meetingColors" :key="type" class="flex items-center gap-2">
            <div class="w-4 h-4 rounded" :style="{ backgroundColor: color }"></div>
            <span class="text-sm text-neutral-600">{{ formatType(type) }}</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  initialView: { type: String, default: 'month' },
  initialDate: String,
});

const viewMode = ref(props.initialView);
const currentDate = ref(new Date(props.initialDate || Date.now()));
const events = ref([]);

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const dayHours = Array.from({ length: 12 }, (_, i) => i + 8); // 8 AM to 7 PM

const meetingColors = {
  office_meeting: '#0078d4',
  boardroom: '#107c10',
  online: '#8764b8',
  site_visit: '#ffb900',
  lab_visit: '#00b7c3',
};

const currentPeriodLabel = computed(() => {
  const opts = viewMode.value === 'month' 
    ? { month: 'long', year: 'numeric' }
    : viewMode.value === 'week'
    ? { month: 'short', day: 'numeric', year: 'numeric' }
    : { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
  return currentDate.value.toLocaleDateString('en-IN', opts);
});

const calendarDates = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const dates = [];
  
  // Days from previous month
  for (let i = 0; i < firstDay.getDay(); i++) {
    const d = new Date(year, month, -i);
    dates.unshift({ key: d.toISOString(), day: d.getDate(), isCurrentMonth: false, isToday: false, events: [] });
  }
  
  // Current month days
  for (let day = 1; day <= lastDay.getDate(); day++) {
    const d = new Date(year, month, day);
    const today = new Date();
    dates.push({
      key: d.toISOString(),
      day,
      isCurrentMonth: true,
      isToday: d.toDateString() === today.toDateString(),
      events: events.value.filter(e => new Date(e.start).toDateString() === d.toDateString()),
    });
  }
  
  return dates;
});

const prevPeriod = () => {
  if (viewMode.value === 'month') currentDate.value.setMonth(currentDate.value.getMonth() - 1);
  else if (viewMode.value === 'week') currentDate.value.setDate(currentDate.value.getDate() - 7);
  else currentDate.value.setDate(currentDate.value.getDate() - 1);
  currentDate.value = new Date(currentDate.value);
  loadEvents();
};

const nextPeriod = () => {
  if (viewMode.value === 'month') currentDate.value.setMonth(currentDate.value.getMonth() + 1);
  else if (viewMode.value === 'week') currentDate.value.setDate(currentDate.value.getDate() + 7);
  else currentDate.value.setDate(currentDate.value.getDate() + 1);
  currentDate.value = new Date(currentDate.value);
  loadEvents();
};

const viewBtn = (mode) => `btn ${viewMode.value === mode ? 'btn-primary' : 'btn-secondary'}`;

const formatTime = (datetime) => new Date(datetime).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
const formatType = (type) => type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

const getEventsForHour = (hour) => events.value.filter(e => new Date(e.start).getHours() === hour);

const loadEvents = async () => {
  const start = new Date(currentDate.value);
  start.setDate(1);
  const end = new Date(start);
  end.setMonth(end.getMonth() + 1);
  
  try {
    const res = await fetch(`/api/calendar/events?start=${start.toISOString()}&end=${end.toISOString()}`);
    events.value = await res.json();
  } catch (e) {
    console.error('Failed to load events', e);
  }
};

onMounted(loadEvents);
</script>
