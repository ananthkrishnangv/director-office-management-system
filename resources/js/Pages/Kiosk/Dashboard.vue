<template>
  <div class="kiosk-wrapper">
    <!-- Animated Background -->
    <div class="kiosk-bg">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>
    </div>
    
    <!-- Header -->
    <header class="kiosk-header">
      <div class="header-left">
        <img src="/images/udomslogo.png" alt="UDOMS" class="logo" />
        <div class="title-group">
          <h1>Director's Schedule</h1>
          <span class="subtitle">CSIR-SERC</span>
        </div>
      </div>
      
      <div class="header-right">
        <div class="auto-refresh-toggle" @click="toggleAutoRefresh">
          <span>Auto Refresh</span>
          <div class="toggle-switch" :class="{ active: autoRefresh }">
            <div class="toggle-knob"></div>
          </div>
        </div>
        
        <div class="datetime-display">
          <div class="time">{{ currentTime }}</div>
          <div class="date">{{ currentDate }}</div>
        </div>
        
        <button @click="logout" class="logout-btn">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>
    </header>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card stat-purple">
        <div class="stat-icon">
          <svg fill="currentColor" viewBox="0 0 20 20"><path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Today's Meetings</span>
          <span class="stat-value">{{ stats.today_meetings }}</span>
          <span class="stat-date">{{ currentDate }}</span>
        </div>
      </div>
      
      <div class="stat-card stat-pink">
        <div class="stat-icon">
          <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Pending</span>
          <span class="stat-value">{{ stats.pending }}</span>
          <span class="stat-meta positive">Awaiting approval</span>
        </div>
      </div>
      
      <div class="stat-card stat-green">
        <div class="stat-icon">
          <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Completed Today</span>
          <span class="stat-value">{{ stats.completed_today }}</span>
          <span class="stat-meta positive">On track</span>
        </div>
      </div>
      
      <div class="stat-card stat-orange">
        <div class="stat-icon">
          <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Cancelled</span>
          <span class="stat-value">{{ stats.cancelled }}</span>
          <span class="stat-meta">Today</span>
        </div>
      </div>
    </div>
    
    <!-- Main Content Grid -->
    <div class="content-grid">
      <!-- Today's Schedule -->
      <div class="glass-card schedule-card">
        <div class="card-header">
          <h2>Today's Schedule</h2>
          <span class="badge">{{ todayMeetings.length }} meetings</span>
        </div>
        
        <div class="schedule-list" v-if="todayMeetings.length > 0">
          <div v-for="meeting in todayMeetings" :key="meeting.id" class="schedule-item">
            <div class="schedule-time">
              {{ formatTime(meeting.start_time) }}
            </div>
            <div class="schedule-details">
              <h4>{{ meeting.visitor_name || meeting.title }}</h4>
              <p>{{ meeting.purpose || meeting.description }}</p>
            </div>
            <span class="status-badge" :class="meeting.status">{{ meeting.status }}</span>
          </div>
        </div>
        
        <div v-else class="empty-state">
          <svg class="w-16 h-16 mx-auto text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p>No meetings scheduled for today</p>
        </div>
      </div>
      
      <!-- Upcoming Unavailability -->
      <div class="glass-card unavailable-card">
        <div class="card-header">
          <h2>Upcoming Unavailability</h2>
        </div>
        
        <div class="unavailable-list" v-if="blockedDates.length > 0">
          <div v-for="block in blockedDates" :key="block.id" class="unavailable-item">
            <div class="unavailable-date">
              <span class="day">{{ formatDay(block.blocked_date) }}</span>
              <span class="month">{{ formatMonth(block.blocked_date) }}</span>
            </div>
            <div class="unavailable-reason">
              {{ block.block_reason }}
            </div>
          </div>
        </div>
        
        <div v-else class="empty-state small">
          <p>No upcoming blocked dates</p>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <footer class="kiosk-footer">
      Unified Director Office Management System • CSIR-SERC
      <span v-if="autoRefresh" class="refresh-indicator">• Auto-refreshing every 60 seconds</span>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  todayMeetings: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({ today_meetings: 0, pending: 0, completed_today: 0, cancelled: 0 }) },
  blockedDates: { type: Array, default: () => [] },
  currentTime: String,
});

const currentTime = ref('');
const currentDate = ref('');
const autoRefresh = ref(true);
let refreshInterval = null;
let timeInterval = null;

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
};

const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value;
  if (autoRefresh.value) {
    refreshInterval = setInterval(() => router.reload({ preserveScroll: true }), 60000);
  } else if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
};

const logout = () => {
  router.post('/kiosk/logout');
};

const formatTime = (datetime) => {
  if (!datetime) return '';
  return new Date(datetime).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const formatDay = (date) => {
  if (!date) return '';
  return new Date(date).getDate();
};

const formatMonth = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', { month: 'short' });
};

onMounted(() => {
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
  if (autoRefresh.value) {
    refreshInterval = setInterval(() => router.reload({ preserveScroll: true }), 60000);
  }
});

onUnmounted(() => {
  if (timeInterval) clearInterval(timeInterval);
  if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<style scoped>
.kiosk-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #e8eaf6 0%, #f3e5f5 50%, #fce4ec 100%);
  position: relative;
  overflow: hidden;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Animated Blobs */
.kiosk-bg {
  position: fixed;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 0;
}

.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.6;
  animation: float 20s ease-in-out infinite;
}

.blob-1 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #e1bee7 0%, #f8bbd9 100%);
  top: -100px;
  right: -100px;
  animation-delay: 0s;
}

.blob-2 {
  width: 500px;
  height: 500px;
  background: linear-gradient(135deg, #b39ddb 0%, #90caf9 100%);
  bottom: -150px;
  left: -150px;
  animation-delay: -7s;
}

.blob-3 {
  width: 300px;
  height: 300px;
  background: linear-gradient(135deg, #80deea 0%, #a5d6a7 100%);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation-delay: -14s;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.05); }
  66% { transform: translate(-20px, 20px) scale(0.95); }
}

/* Header */
.kiosk-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 16px 24px;
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  position: relative;
  z-index: 10;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.logo {
  height: 48px;
  width: auto;
}

.title-group h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #4a148c;
  margin: 0;
}

.title-group .subtitle {
  font-size: 0.875rem;
  color: #7b1fa2;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 24px;
}

.auto-refresh-toggle {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #6a1b9a;
}

.toggle-switch {
  width: 44px;
  height: 24px;
  background: #e0e0e0;
  border-radius: 12px;
  position: relative;
  transition: background 0.3s;
}

.toggle-switch.active {
  background: linear-gradient(135deg, #7c4dff, #e040fb);
}

.toggle-knob {
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: left 0.3s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch.active .toggle-knob {
  left: 22px;
}

.datetime-display {
  text-align: right;
}

.datetime-display .time {
  font-size: 1.75rem;
  font-weight: 700;
  color: #4a148c;
  font-family: 'SF Mono', 'Fira Code', monospace;
}

.datetime-display .date {
  font-size: 0.875rem;
  color: #7b1fa2;
}

.logout-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(244, 67, 54, 0.1);
  border: none;
  border-radius: 12px;
  color: #c62828;
  cursor: pointer;
  transition: all 0.2s;
}

.logout-btn:hover {
  background: rgba(244, 67, 54, 0.2);
  transform: scale(1.05);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  position: relative;
  z-index: 10;
}

.stat-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 24px;
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-purple .stat-icon {
  background: linear-gradient(135deg, #e1bee7, #ce93d8);
  color: #6a1b9a;
}

.stat-pink .stat-icon {
  background: linear-gradient(135deg, #f8bbd9, #f48fb1);
  color: #ad1457;
}

.stat-green .stat-icon {
  background: linear-gradient(135deg, #c8e6c9, #a5d6a7);
  color: #2e7d32;
}

.stat-orange .stat-icon {
  background: linear-gradient(135deg, #ffe0b2, #ffcc80);
  color: #e65100;
}

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #616161;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #212121;
  line-height: 1.2;
}

.stat-date {
  font-size: 0.75rem;
  color: #9e9e9e;
}

.stat-meta {
  font-size: 0.75rem;
  color: #9e9e9e;
}

.stat-meta.positive {
  color: #43a047;
}

/* Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  flex: 1;
  position: relative;
  z-index: 10;
}

.glass-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header h2 {
  font-size: 1.125rem;
  font-weight: 700;
  color: #4a148c;
  margin: 0;
}

.badge {
  background: linear-gradient(135deg, #7c4dff, #e040fb);
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Schedule List */
.schedule-list {
  padding: 16px;
  max-height: 400px;
  overflow-y: auto;
}

.schedule-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 16px;
  margin-bottom: 12px;
  border: 1px solid rgba(0, 0, 0, 0.03);
}

.schedule-time {
  font-size: 0.875rem;
  font-weight: 700;
  color: #6a1b9a;
  background: linear-gradient(135deg, #f3e5f5, #e1bee7);
  padding: 8px 14px;
  border-radius: 10px;
  white-space: nowrap;
}

.schedule-details {
  flex: 1;
}

.schedule-details h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #212121;
  margin: 0 0 4px 0;
}

.schedule-details p {
  font-size: 0.875rem;
  color: #757575;
  margin: 0;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.scheduled {
  background: #e3f2fd;
  color: #1565c0;
}

.status-badge.confirmed {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-badge.completed {
  background: #f3e5f5;
  color: #6a1b9a;
}

.status-badge.cancelled {
  background: #ffebee;
  color: #c62828;
}

/* Unavailable List */
.unavailable-list {
  padding: 16px;
}

.unavailable-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px;
  background: linear-gradient(135deg, rgba(255, 138, 128, 0.1), rgba(255, 82, 82, 0.05));
  border-radius: 14px;
  margin-bottom: 10px;
  border: 1px solid rgba(255, 82, 82, 0.1);
}

.unavailable-date {
  background: linear-gradient(135deg, #ff8a80, #ff5252);
  color: white;
  padding: 10px 14px;
  border-radius: 12px;
  text-align: center;
  min-width: 56px;
}

.unavailable-date .day {
  display: block;
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1;
}

.unavailable-date .month {
  display: block;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
}

.unavailable-reason {
  font-size: 0.9rem;
  color: #424242;
  font-weight: 500;
}

/* Empty State */
.empty-state {
  padding: 48px 24px;
  text-align: center;
  color: #9e9e9e;
}

.empty-state.small {
  padding: 32px 24px;
}

.empty-state p {
  margin: 16px 0 0;
  font-size: 0.9rem;
}

/* Footer */
.kiosk-footer {
  text-align: center;
  font-size: 0.875rem;
  color: #7b1fa2;
  padding: 16px;
  position: relative;
  z-index: 10;
}

.refresh-indicator {
  color: #43a047;
}

/* Responsive */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .kiosk-wrapper {
    padding: 16px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .header-left .title-group {
    display: none;
  }
}
</style>
