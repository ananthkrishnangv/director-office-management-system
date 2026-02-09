<template>
  <AppLayout>
    <div class="space-y-6 max-w-[1600px] mx-auto">
      
      <!-- Welcome Banner -->
      <div class="fluent-card p-4 flex flex-col md:flex-row items-center justify-between gap-4 border-l-4 border-l-blue-500">
        <div class="flex items-center gap-4">
          <div class="relative">
            <img 
              src="/images/directorprofile.png" 
              alt="Director" 
              class="w-16 h-16 rounded-full border-4 border-white shadow-lg object-cover"
            />
            <div class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
          </div>
          <div>
            <p class="text-xs text-neutral-500 font-medium">Hello,</p>
            <h1 class="text-xl font-bold text-neutral-800">{{ $page.props.auth.user.name }}</h1>
            <p class="text-sm text-neutral-500">{{ $page.props.auth.user.role === 'director' ? 'Director, CSIR-SERC' : $page.props.auth.user.role }}</p>
            <div class="flex items-center gap-4 mt-1 text-xs text-neutral-500">
              <span class="flex items-center gap-1">
                <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/></svg>
                {{ currentDate }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/></svg>
                {{ currentTime }}
              </span>
            </div>
          </div>
        </div>
        
        <!-- Mini Clock Widget -->
        <div class="hidden lg:flex items-center gap-6">
          <div class="bg-blue-50 rounded-xl p-3 text-center min-w-[100px]">
            <div class="text-2xl font-bold text-blue-600 font-mono">{{ currentTime }}</div>
            <div class="text-[10px] text-blue-400 uppercase tracking-wide">Current Time</div>
          </div>
        </div>
      </div>

      <!-- Stats Cards Row -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="stat in stats" :key="stat.label" class="fluent-card p-3 border-l-4" :class="stat.borderColor">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-[10px] text-neutral-500 font-medium uppercase tracking-wide">{{ stat.label }}</p>
              <p class="text-2xl font-bold text-neutral-800 mt-1">{{ stat.value }}</p>
            </div>
            <div class="p-1.5 rounded-lg" :class="stat.iconBg">
              <component :is="stat.icon" class="w-4 h-4" :class="stat.iconColor" />
            </div>
          </div>
          <div class="flex items-center gap-1 mt-1 text-[10px]">
            <span :class="stat.trend >= 0 ? 'text-green-600' : 'text-red-600'" class="flex items-center gap-0.5">
              <svg v-if="stat.trend >= 0" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"/></svg>
              <svg v-else class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"/></svg>
              {{ Math.abs(stat.trend) }}%
            </span>
            <span class="text-neutral-400">from last week</span>
          </div>
        </div>
      </div>

      <!-- Main Content: Calendar + Today's Schedule -->
      <div class="grid lg:grid-cols-12 gap-6">
        
        <!-- Calendar Section -->
        <div class="lg:col-span-7 fluent-card p-4">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
              <select v-model="calendarView" class="text-xs border border-neutral-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="month">Month</option>
                <option value="week">Week</option>
              </select>
              <div class="flex items-center gap-1">
                <button @click="prevMonth" class="p-1 hover:bg-neutral-100 rounded-lg transition-colors">
                  <svg class="w-4 h-4 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="font-semibold text-neutral-800 min-w-[120px] text-center text-sm">{{ calendarTitle }}</span>
                <button @click="nextMonth" class="p-1 hover:bg-neutral-100 rounded-lg transition-colors">
                  <svg class="w-4 h-4 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Calendar Grid -->
          <div class="grid grid-cols-7 gap-1 mb-1">
            <div v-for="day in weekDays" :key="day" class="text-center text-[10px] font-semibold text-neutral-500 py-1 uppercase tracking-wide">
              {{ day }}
            </div>
          </div>
          <div class="grid grid-cols-7 gap-1">
            <div 
              v-for="date in calendarDays" 
              :key="date.key"
              @click="!date.isBlocked && selectDate(date)"
              @mouseenter="(e) => handleMouseEnter(e, date)"
              @mouseleave="handleMouseLeave"
              class="aspect-square p-0.5 rounded-lg cursor-pointer transition-all hover:bg-neutral-50 flex flex-col items-center justify-center relative border border-transparent"
              :class="{
                'bg-blue-500 text-white hover:bg-blue-600': date.isSelected && !date.isBlocked,
                'bg-blue-50': date.isToday && !date.isSelected && !date.isBlocked,
                'text-neutral-300': !date.isCurrentMonth,
                'bg-neutral-100 cursor-not-allowed opacity-60': date.isBlocked,
                'border-red-100': date.isBlocked
              }"
            >
              <span class="text-xs font-medium" :class="{'text-red-400': date.isBlocked && !date.isSelected}">{{ date.day }}</span>
              <div v-if="date.isBlocked" class="hidden"></div>
              <div v-else-if="date.events.length > 0" class="flex gap-0.5 mt-0.5">
                <span 
                  v-for="(event, i) in date.events.slice(0, 3)" 
                  :key="i" 
                  class="w-1 h-1 rounded-full"
                  :class="getEventDotColor(event.extendedProps?.meeting_type || 'meeting')"
                ></span>
              </div>
            </div>
          </div>

          <!-- Hover Popup -->
          <div 
            v-if="hoveredDate" 
            class="fixed z-50 bg-white shadow-xl rounded-xl p-3 border border-neutral-200 w-64 pointer-events-none transform -translate-x-1/2 transition-all duration-200"
            :style="{ top: `${hoverPosition.y}px`, left: `${hoverPosition.x}px` }"
          >
            <div class="text-xs font-bold text-neutral-800 border-b border-neutral-100 pb-1 mb-1">
              {{ formatDate(hoveredDate.date) }}
              <span v-if="hoveredDate.isBlocked" class="block text-[10px] text-red-500 font-normal mt-0.5">
                BLOCKED: Unavailable
              </span>
            </div>
            
            <div v-if="hoveredEvents.length > 0" class="space-y-1">
              <div v-for="(event, i) in hoveredEvents" :key="i" class="text-[10px]">
                  <div class="font-semibold text-blue-600 truncate">{{ event.title }}</div>
                  <div class="text-neutral-500">{{ formatTime(event.start) }}</div>
              </div>
            </div>
            <div v-else-if="!hoveredDate.isBlocked" class="text-[10px] text-neutral-400 italic">
              No meetings
            </div>
          </div>
        </div>

        <!-- Today's Schedule -->
        <div class="lg:col-span-5 fluent-card p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="font-semibold text-neutral-800">{{ selectedDateTitle }}</h3>
            </div>
            <button @click="closeSchedule" class="p-1 hover:bg-neutral-100 rounded-lg transition-colors">
              <svg class="w-5 h-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          
          <div class="space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar">
            <div v-if="todaysMeetings.length === 0" class="text-center py-12 text-neutral-400">
              <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <p>No meetings scheduled</p>
            </div>
            
            <div 
              v-for="meeting in todaysMeetings" 
              :key="meeting.id" 
              class="flex items-start gap-4 p-4 bg-neutral-50 rounded-xl border border-neutral-100 hover:shadow-sm transition-all"
            >
              <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm" :class="getAvatarColor(meeting.id)">
                {{ getInitials(meeting.title) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <h4 class="font-semibold text-neutral-800 truncate">{{ meeting.title }}</h4>
                    <p class="text-sm text-neutral-500 truncate">{{ meeting.description || 'No description' }}</p>
                  </div>
                  <span 
                    class="px-2 py-1 rounded text-xs font-bold whitespace-nowrap"
                    :class="getStatusBadge(meeting.status)"
                  >
                    {{ getStatusLabel(meeting.status) }}
                  </span>
                </div>
                <div class="flex items-center gap-4 mt-2 text-xs text-neutral-500">
                  <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ formatTime(meeting.start_time) }}
                  </span>
                </div>
              </div>
              <div class="flex gap-1">
                <button class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Reschedule">
                  <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
                <button class="p-1.5 hover:bg-white rounded-lg transition-colors" title="More">
                  <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Approvals Section -->
      <div class="fluent-card p-6 border-l-4 border-l-amber-500">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-lg font-bold text-neutral-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/></svg>
              Pending Approvals
            </h3>
            <p class="text-sm text-neutral-500 mt-1">Meeting requests awaiting your approval</p>
          </div>
          <span class="bg-amber-100 text-amber-700 text-sm font-bold px-3 py-1 rounded-full">
            {{ pending_appointments.length }} Pending
          </span>
        </div>

        <div v-if="pending_appointments.length === 0" class="text-center py-8 text-neutral-400 border-2 border-dashed border-neutral-100 rounded-xl">
          <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <p>All caught up! No pending requests.</p>
        </div>

        <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div 
            v-for="apt in pending_appointments" 
            :key="apt.id" 
            class="bg-white border border-neutral-200 rounded-xl p-4 hover:shadow-md transition-all"
          >
            <div class="flex items-start gap-3 mb-3">
              <div class="w-10 h-10 rounded-full bg-neutral-100 flex items-center justify-center text-neutral-600 font-bold text-sm">
                {{ getInitials(apt.requester_name || apt.visitor_name) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <span 
                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide"
                    :class="apt.source === 'public' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                  >
                    {{ apt.source === 'public' ? 'Public' : 'Internal' }}
                  </span>
                </div>
                <h4 class="font-semibold text-neutral-800 mt-1 truncate">{{ apt.requester_name || apt.visitor_name }}</h4>
                <p class="text-sm text-neutral-500 truncate">{{ apt.purpose }}</p>
              </div>
            </div>
            
            <div class="flex items-center gap-4 text-xs text-neutral-500 mb-4">
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ formatDate(apt.requested_date || apt.appointment_time) }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ formatTime(apt.requested_start_time || apt.appointment_time) }}
              </span>
            </div>
            
            <div class="flex gap-2">
              <button 
                @click="approveRequest(apt.id)" 
                class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-1"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Approve
              </button>
              <button 
                @click="rejectRequest(apt.id)" 
                class="flex-1 bg-white border border-red-200 text-red-600 hover:bg-red-50 py-2 rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-1"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Reject
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Today's Schedule Section (Timeline Style) -->
      <div class="fluent-card p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-bold text-neutral-800">Today's Schedule</h3>
          <div class="flex items-center gap-2">
            <button class="p-1.5 hover:bg-neutral-100 rounded-lg transition-colors">
              <svg class="w-5 h-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button class="p-1.5 hover:bg-neutral-100 rounded-lg transition-colors">
              <svg class="w-5 h-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <div class="flex gap-4 overflow-x-auto pb-2 custom-scrollbar">
          <div 
            v-for="meeting in todays_meetings" 
            :key="meeting.id" 
            class="flex-shrink-0 w-64 bg-white border border-neutral-200 rounded-xl p-4 hover:shadow-md transition-all"
          >
            <div class="flex items-center justify-between mb-3">
              <span class="text-xs font-mono font-medium text-neutral-500 bg-neutral-100 px-2 py-1 rounded">
                {{ formatTime(meeting.start_time) }}
              </span>
              <span 
                class="px-2 py-1 rounded text-xs font-bold"
                :class="getStatusBadge(meeting.status)"
              >
                {{ getStatusLabel(meeting.status) }}
              </span>
            </div>
            <h4 class="font-semibold text-neutral-800 mb-1">{{ meeting.title }}</h4>
            <p class="text-sm text-neutral-500 line-clamp-2">{{ meeting.description || 'No description' }}</p>
          </div>
          
          <div v-if="todays_meetings.length === 0" class="text-center py-8 text-neutral-400 w-full">
            <p>No meetings scheduled for today</p>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Components/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  stats_data: Object,
  todays_meetings: { type: Array, default: () => [] },
  pending_appointments: { type: Array, default: () => [] },
  blocked_dates: { type: Array, default: () => [] },
  monthly_meetings: { type: Array, default: () => [] },
});

// Time
const currentTime = ref('');
const currentDate = ref('');
let timer;

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onUnmounted(() => clearInterval(timer));

// Stats
const stats = computed(() => [
  { label: "Today's Meetings", value: props.stats_data?.today_meetings || 0, trend: 20, borderColor: 'border-l-blue-500', iconBg: 'bg-blue-50', iconColor: 'text-blue-500', icon: 'CalendarIcon' },
  { label: 'Pending Approval', value: props.stats_data?.pending_appointments || 0, trend: 0, borderColor: 'border-l-amber-500', iconBg: 'bg-amber-50', iconColor: 'text-amber-500', icon: 'ClockIcon' },
  { label: 'This Week', value: props.stats_data?.this_week_meetings || 0, trend: 15, borderColor: 'border-l-green-500', iconBg: 'bg-green-50', iconColor: 'text-green-500', icon: 'ChartBarIcon' },
  { label: 'This Month', value: props.stats_data?.this_month_meetings || 0, trend: 10, borderColor: 'border-l-purple-500', iconBg: 'bg-purple-50', iconColor: 'text-purple-500', icon: 'ChartPieIcon' },
]);

// Calendar
const calendarView = ref('month');
const selectedDate = ref(new Date());
const currentMonth = ref(new Date());
const weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

// Hover state
const hoveredDate = ref(null);
const hoverPosition = ref({ x: 0, y: 0 });
const hoveredEvents = ref([]);

const calendarTitle = computed(() => {
  return currentMonth.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const selectedDateTitle = computed(() => {
  return selectedDate.value.toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
});

// Filter meetings for the selected date
const selectedDateMeetings = computed(() => {
    const dateStr = selectedDate.value.toISOString().split('T')[0];
    return props.monthly_meetings.filter(m => m.start.startsWith(dateStr));
});

const calendarDays = computed(() => {
  const year = currentMonth.value.getFullYear();
  const month = currentMonth.value.getMonth();
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const days = [];
  
  let startDay = firstDay.getDay() - 1;
  if (startDay < 0) startDay = 6;
  
  // Previous month days
  for (let i = startDay - 1; i >= 0; i--) {
    const d = new Date(year, month, -i);
    days.push({
      key: `prev-${i}`,
      day: d.getDate(),
      date: d,
      isCurrentMonth: false,
      isToday: false,
      isSelected: false,
      events: [],
      isBlocked: false
    });
  }
  
  // Current month days
  const today = new Date();
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const d = new Date(year, month, i);
    const dateStr = d.toISOString().split('T')[0];
    const isToday = d.toDateString() === today.toDateString();
    const isSelected = d.toDateString() === selectedDate.value.toDateString();
    
    // Real events
    const events = props.monthly_meetings.filter(m => m.start.startsWith(dateStr));
    
    // Blocked status
    const isBlocked = props.blocked_dates.some(bd => bd.blocked_date === dateStr);
    
    days.push({
      key: `curr-${i}`,
      day: i,
      date: d,
      isCurrentMonth: true,
      isToday,
      isSelected,
      events,
      isBlocked
    });
  }
  
  // Next month days
  const remaining = 42 - days.length;
  for (let i = 1; i <= remaining; i++) {
    const d = new Date(year, month + 1, i);
    days.push({
      key: `next-${i}`,
      day: i,
      date: d,
      isCurrentMonth: false,
      isToday: false,
      isSelected: false,
      events: [],
      isBlocked: false
    });
  }
  
  return days;
});

const prevMonth = () => {
  currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
};

const nextMonth = () => {
  currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
};

const selectDate = (date) => {
  selectedDate.value = date.date;
};

const closeSchedule = () => {};

// Hover Logic
const handleMouseEnter = (event, date) => {
    if ((date.events && date.events.length > 0) || date.isBlocked) {
        hoveredDate.value = date;
        const rect = event.target.getBoundingClientRect();
        hoverPosition.value = {
            x: rect.left + window.scrollX + (rect.width / 2),
            y: rect.top + window.scrollY - 10 
        };
        hoveredEvents.value = date.events;
    }
};

const handleMouseLeave = () => {
    hoveredDate.value = null;
    hoveredEvents.value = [];
};


// Helpers
const formatTime = (dt) => {
  if (!dt) return '--:--';
  return new Date(dt).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (dt) => {
  if (!dt) return '--';
  return new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getAvatarColor = (id) => {
  const colors = ['bg-blue-500', 'bg-purple-500', 'bg-green-500', 'bg-orange-500', 'bg-teal-500'];
  return colors[id % colors.length];
};

const getEventDotColor = (type) => ({
  meeting: 'bg-blue-500',
  appointment: 'bg-blue-500',
  task: 'bg-green-500'
}[type] || 'bg-blue-500');

const getStatusBadge = (status) => ({
  scheduled: 'bg-blue-100 text-blue-700',
  in_progress: 'bg-green-100 text-green-700',
  completed: 'bg-gray-100 text-gray-600',
  cancelled: 'bg-red-100 text-red-600',
  pending: 'bg-amber-100 text-amber-700'
}[status] || 'bg-gray-100 text-gray-600');

const getStatusLabel = (status) => ({
  scheduled: 'Scheduled',
  in_progress: 'In Progress',
  completed: 'Completed',
  cancelled: 'Cancelled',
  pending: 'Pending'
}[status] || status);

const approveRequest = (id) => {
  if (confirm('Approve this appointment request?')) {
    router.post(`/appointments/${id}/approve`);
  }
};

const rejectRequest = (id) => {
  if (confirm('Reject this appointment request?')) {
    router.post(`/appointments/${id}/reject`, { rejection_reason: 'Request rejected by Director' });
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
