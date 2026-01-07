<template>
  <div class="kiosk-container">
    <!-- Header -->
    <div class="kiosk-header">
      <div class="flex items-center gap-4">
        <img src="/images/logo.png" alt="UDOMS" class="h-12" onerror="this.style.display='none'" />
        <div>
          <h1 class="text-2xl font-bold text-white">Director's Schedule</h1>
          <p class="text-white/70">{{ currentDateFormatted }}</p>
        </div>
      </div>
      <div class="text-right">
        <div class="text-4xl font-bold text-white">{{ currentTime }}</div>
        <div class="text-white/70">{{ dayOfWeek }}</div>
      </div>
    </div>

    <!-- Calendar View -->
    <div class="kiosk-calendar">
      <!-- Date Navigation -->
      <div class="flex items-center justify-between mb-6">
        <button @click="prevDay" class="kiosk-nav-btn">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <div class="text-center">
          <div class="text-3xl font-bold text-neutral-800">{{ selectedDateFormatted }}</div>
          <div class="text-neutral-500">{{ selectedDayOfWeek }}</div>
        </div>
        <button @click="nextDay" class="kiosk-nav-btn">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>

      <!-- Timeline -->
      <div class="kiosk-timeline">
        <div v-for="hour in hours" :key="hour" class="kiosk-hour">
          <div class="kiosk-hour-label">{{ formatHour(hour) }}</div>
          <div class="kiosk-hour-line">
            <div 
              v-for="meeting in getMeetingsForHour(hour)" 
              :key="meeting.id"
              class="kiosk-meeting"
              :style="{ 
                backgroundColor: meeting.color || getMeetingColor(meeting.meeting_type),
                height: `${getMeetingHeight(meeting)}px`
              }">
              <div class="font-semibold text-white truncate">{{ meeting.title }}</div>
              <div class="text-white/80 text-sm">{{ formatMeetingTime(meeting) }}</div>
              <div v-if="meeting.location" class="text-white/60 text-sm truncate">📍 {{ meeting.location }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Meetings -->
      <div v-if="meetings.length === 0" class="text-center py-12">
        <div class="text-6xl mb-4">📅</div>
        <h2 class="text-2xl font-semibold text-neutral-700">No meetings scheduled</h2>
        <p class="text-neutral-500 mt-2">The Director is available today</p>
      </div>
    </div>

    <!-- Upcoming Meetings Ticker -->
    <div class="kiosk-ticker">
      <div class="ticker-content">
        <span v-for="(meeting, i) in upcomingMeetings" :key="meeting.id" class="ticker-item">
          <span class="ticker-time">{{ formatTime(meeting.start_time) }}</span>
          <span class="ticker-title">{{ meeting.title }}</span>
          <span v-if="i < upcomingMeetings.length - 1" class="ticker-sep">•</span>
        </span>
        <span v-if="upcomingMeetings.length === 0" class="ticker-item">No upcoming meetings today</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const currentTime = ref('');
const selectedDate = ref(new Date());
const meetings = ref([]);

const hours = Array.from({ length: 12 }, (_, i) => i + 8); // 8 AM to 7 PM

const currentDateFormatted = computed(() => 
  new Date().toLocaleDateString('en-IN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
);

const dayOfWeek = computed(() => new Date().toLocaleDateString('en-IN', { weekday: 'long' }));

const selectedDateFormatted = computed(() => 
  selectedDate.value.toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' })
);

const selectedDayOfWeek = computed(() => selectedDate.value.toLocaleDateString('en-IN', { weekday: 'long' }));

const upcomingMeetings = computed(() => 
  meetings.value.filter(m => new Date(m.start_time) > new Date()).slice(0, 5)
);

const updateTime = () => {
  currentTime.value = new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
};

const prevDay = () => {
  selectedDate.value = new Date(selectedDate.value.setDate(selectedDate.value.getDate() - 1));
  loadMeetings();
};

const nextDay = () => {
  selectedDate.value = new Date(selectedDate.value.setDate(selectedDate.value.getDate() + 1));
  loadMeetings();
};

const loadMeetings = async () => {
  const date = selectedDate.value.toISOString().split('T')[0];
  try {
    const response = await fetch(`/api/calendar/date/${date}`);
    const data = await response.json();
    meetings.value = data.meetings || [];
  } catch (e) {
    console.error('Failed to load meetings:', e);
  }
};

const formatHour = (hour) => {
  const h = hour % 12 || 12;
  const ampm = hour < 12 ? 'AM' : 'PM';
  return `${h} ${ampm}`;
};

const formatTime = (dt) => new Date(dt).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });

const formatMeetingTime = (meeting) => `${formatTime(meeting.start_time)} - ${formatTime(meeting.end_time)}`;

const getMeetingsForHour = (hour) => 
  meetings.value.filter(m => new Date(m.start_time).getHours() === hour);

const getMeetingHeight = (meeting) => {
  const duration = (new Date(meeting.end_time) - new Date(meeting.start_time)) / 60000;
  return Math.max(60, duration);
};

const getMeetingColor = (type) => ({
  one_on_one: '#3b82f6', team: '#10b981', external: '#f59e0b', review: '#8b5cf6', official: '#ef4444', personal: '#06b6d4'
}[type] || '#6b7280');

let timeInterval;
let refreshInterval;

onMounted(() => {
  updateTime();
  loadMeetings();
  timeInterval = setInterval(updateTime, 1000);
  refreshInterval = setInterval(loadMeetings, 60000);
});

onUnmounted(() => {
  clearInterval(timeInterval);
  clearInterval(refreshInterval);
});
</script>

<style scoped>
.kiosk-container {
  min-height: 100vh;
  background: linear-gradient(180deg, #1e3a5f 0%, #0f172a 100%);
  display: flex;
  flex-direction: column;
}

.kiosk-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem 3rem;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
}

.kiosk-calendar {
  flex: 1;
  background: white;
  margin: 1.5rem;
  border-radius: 2rem;
  padding: 2rem;
  overflow: auto;
}

.kiosk-nav-btn {
  width: 3rem;
  height: 3rem;
  border-radius: 1rem;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.kiosk-nav-btn:hover {
  background: #e5e7eb;
  transform: scale(1.05);
}

.kiosk-timeline {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.kiosk-hour {
  display: flex;
  align-items: flex-start;
  min-height: 80px;
}

.kiosk-hour-label {
  width: 80px;
  font-size: 0.875rem;
  color: #6b7280;
  padding-top: 0.5rem;
  flex-shrink: 0;
}

.kiosk-hour-line {
  flex: 1;
  border-top: 1px solid #e5e7eb;
  position: relative;
  min-height: 80px;
  padding: 0.25rem;
}

.kiosk-meeting {
  border-radius: 0.75rem;
  padding: 0.75rem 1rem;
  margin-bottom: 0.25rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.kiosk-ticker {
  background: #1e3a5f;
  color: white;
  padding: 1rem 2rem;
  overflow: hidden;
}

.ticker-content {
  display: flex;
  gap: 2rem;
  animation: ticker 30s linear infinite;
}

.ticker-item {
  display: flex;
  gap: 0.5rem;
  white-space: nowrap;
}

.ticker-time {
  font-weight: 600;
  color: #fbbf24;
}

.ticker-title {
  color: white;
}

.ticker-sep {
  color: rgba(255, 255, 255, 0.3);
}

@keyframes ticker {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}
</style>
