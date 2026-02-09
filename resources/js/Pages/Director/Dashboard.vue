<template>
  <AppLayout>
    <div class="container mx-auto max-w-7xl p-4 space-y-6" data-theme="corporate">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-base-content">Dashboard</h1>
          <p class="text-sm text-base-content/70">Welcome back, {{ $page.props.auth.user.name }}</p>
        </div>
        <div class="stats shadow bg-base-100">
           <div class="stat p-2 place-items-center">
             <div class="stat-title text-xs uppercase tracking-wider">Current Time</div>
             <div class="stat-value text-lg font-mono">{{ currentTime }}</div>
           </div>
        </div>
      </div>

      <!-- Stats Row (DaisyUI) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="stat in stats" :key="stat.label" class="stats shadow bg-base-100 border border-base-200">
           <div class="stat p-4">
             <div class="stat-figure text-primary">
               <!-- Icon Switch -->
               <svg v-if="stat.icon === 'calendar'" class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
               <svg v-if="stat.icon === 'clock'" class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
               <svg v-if="stat.icon === 'check'" class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
               <svg v-if="stat.icon === 'users'" class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
             </div>
             <div class="stat-title text-xs font-bold uppercase">{{ stat.label }}</div>
             <div class="stat-value text-2xl text-primary">{{ stat.value }}</div>
             <div class="stat-desc font-medium" :class="stat.trendColor">{{ stat.tag }}</div>
           </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Upcoming Schedule (Card + Compact Table) -->
        <div class="lg:col-span-8 card bg-base-100 shadow-xl border border-base-200">
          <div class="card-body p-4">
             <div class="flex justify-between items-center mb-4">
                <h2 class="card-title text-lg">Upcoming Schedule</h2>
                <div class="flex gap-2">
                   <button class="btn btn-xs btn-outline">Export</button>
                   <button class="btn btn-xs btn-primary">Calendar</button>
                </div>
             </div>

             <div class="overflow-x-auto">
                <table class="table table-xs w-full">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Detail</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="upcomingMeetings.length === 0">
                      <td colspan="5" class="text-center py-8 text-base-content/50">No upcoming appointments</td>
                    </tr>
                    <tr v-for="meeting in upcomingMeetings" :key="meeting.id" class="hover">
                      <td class="font-bold whitespace-nowrap">
                         <div class="flex flex-col">
                            <span class="text-lg">{{ getDay(meeting.start) }}</span>
                            <span class="text-[10px] uppercase opacity-70">{{ getMonthShort(meeting.start) }}</span>
                         </div>
                      </td>
                      <td class="whitespace-nowrap">
                         <div class="badge badge-ghost badge-sm font-mono">
                            {{ formatTime(meeting.start_time) }} - {{ formatTime(meeting.end_time) }}
                         </div>
                      </td>
                      <td>
                         <div class="font-bold truncate max-w-[200px]">{{ meeting.title }}</div>
                         <div class="text-[10px] opacity-70 truncate max-w-[200px]">{{ meeting.description || 'No description' }}</div>
                      </td>
                      <td>
                         <div class="badge badge-xs font-bold uppercase" :class="getStatusBadge(meeting.status)">
                            {{ meeting.status }}
                         </div>
                      </td>
                      <td>
                         <button class="btn btn-ghost btn-xs btn-circle" aria-label="Details">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                         </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
             </div>
          </div>
        </div>

        <!-- Right: Mini Calendar & Pending -->
        <div class="lg:col-span-4 space-y-6">
           
           <!-- Mini Calendar -->
           <div class="card bg-base-100 shadow-xl border border-base-200">
              <div class="card-body p-4">
                 <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-base">{{ calendarTitle }}</h3>
                    <div class="join">
                       <button @click="prevMonth" class="join-item btn btn-xs btn-ghost">«</button>
                       <button @click="nextMonth" class="join-item btn btn-xs btn-ghost">»</button>
                    </div>
                 </div>
                 
                 <div class="grid grid-cols-7 text-center text-[10px] font-bold opacity-50 mb-1">
                    <span v-for="day in weekDays" :key="day">{{ day }}</span>
                 </div>
                 
                 <div class="grid grid-cols-7 gap-1 text-center text-xs">
                    <div 
                       v-for="date in calendarDays" 
                       :key="date.key"
                       class="aspect-square flex flex-col items-center justify-center rounded cursor-pointer hover:bg-base-200 transition-colors relative"
                       :class="{
                          'opacity-30': !date.isCurrentMonth,
                          'bg-primary text-primary-content font-bold shadow-md': date.isToday,
                          'bg-base-200 text-base-content/50 cursor-not-allowed': date.isBlocked
                       }"
                    >
                       <span>{{ date.day }}</span>
                       <div v-if="!date.isToday && !date.isBlocked && date.events.length" class="flex gap-0.5 absolute bottom-1">
                          <span class="w-1 h-1 rounded-full bg-primary"></span>
                       </div>
                    </div>
                 </div>
              </div>
           </div>

           <!-- Pending Requests -->
           <div class="card bg-base-100 shadow-xl border border-base-200">
              <div class="card-body p-4">
                 <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-base flex gap-2 items-center">
                       Pending Requests
                       <div class="badge badge-warning badge-sm">{{ pending_appointments.length }}</div>
                    </h3>
                 </div>
                 
                 <div class="max-h-[300px] overflow-y-auto space-y-2">
                    <div v-if="pending_appointments.length === 0" class="text-center py-4 text-xs opacity-50">No pending requests</div>
                    
                    <div v-for="apt in pending_appointments" :key="apt.id" class="p-2 border border-base-200 rounded-lg bg-base-50 hover:bg-base-100 transition-all">
                       <div class="flex justify-between items-start">
                          <div>
                             <div class="font-bold text-sm">{{ apt.visitor_name }}</div>
                             <div class="text-[10px] opacity-70">{{ formatDate(apt.appointment_time) }}</div>
                          </div>
                          <div class="badge badge-xs uppercase font-bold" :class="apt.source === 'public' ? 'badge-secondary' : 'badge-neutral'">
                             {{ apt.source === 'public' ? 'WEB' : 'INT' }}
                          </div>
                       </div>
                       <div class="flex gap-2 mt-2">
                          <button @click="approveRequest(apt.id)" class="btn btn-xs btn-success flex-1 text-white">Approve</button>
                          <button @click="rejectRequest(apt.id)" class="btn btn-xs btn-error flex-1 text-white">Reject</button>
                       </div>
                    </div>
                 </div>
              </div>
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
  stats: Object,
  todays_meetings: { type: Array, default: () => [] },
  pending_appointments: { type: Array, default: () => [] },
  blocked_dates: { type: Array, default: () => [] },
  monthly_meetings: { type: Array, default: () => [] },
});

// Gradient Stats
const stats = computed(() => [
  { 
      label: "Total Requests", 
      value: props.stats?.today_meetings || 0, 
      tag: '+12%', 
      gradient: 'bg-gradient-to-br from-blue-500 to-indigo-600', 
      icon: 'calendar' 
  },
  { 
      label: "Pending Action", 
      value: props.stats?.pending_appointments || 0, 
      tag: 'Urgent', 
      gradient: 'bg-gradient-to-br from-orange-400 to-pink-500', 
      icon: 'clock' 
  },
  { 
      label: "Approved", 
      value: props.stats?.this_month_meetings || 0, 
      tag: 'This Month', 
      gradient: 'bg-gradient-to-br from-emerald-400 to-teal-500', 
      icon: 'check' 
  },
  { 
      label: "Visitors", 
      value: "142", 
      tag: 'Active', 
      gradient: 'bg-gradient-to-br from-violet-500 to-purple-600', 
      icon: 'users' 
  },
]);

// Upcoming Schedule Logic (Flattened from monthly_meetings or passed prop)
const upcomingMeetings = computed(() => {
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Local midnight
    
    return (props.monthly_meetings || [])
        .filter(m => new Date(m.start) >= today)
        .sort((a, b) => new Date(a.start) - new Date(b.start))
        .slice(0, 5); // Show top 5
});

// Mini Calendar
const currentMonth = ref(new Date());
const weekDays = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];

const calendarTitle = computed(() => {
  return currentMonth.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const calendarDays = computed(() => {
  const year = currentMonth.value.getFullYear();
  const month = currentMonth.value.getMonth();
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const days = [];
  
  let startDay = firstDay.getDay() - 1;
  if (startDay < 0) startDay = 6;
  
  // Previous month days fill
  for (let i = startDay - 1; i >= 0; i--) {
    days.push({ key: `prev-${i}`, day: '', isCurrentMonth: false, isToday: false, events: [], isBlocked: false });
  }
  
  // Current month days
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const d = new Date(year, month, i);
    // Construct Local YYYY-MM-DD
    const dateStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    
    const isToday = d.getTime() === today.getTime();
    
    // Check events and blocked dates
    const events = (props.monthly_meetings || []).filter(m => m.start && m.start.startsWith(dateStr));
    const isBlocked = (props.blocked_dates || []).some(bd => bd.blocked_date === dateStr);
    
    days.push({
      key: `curr-${i}`,
      day: i,
      date: d,
      isCurrentMonth: true,
      isToday,
      events,
      isBlocked
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

// Utils
const formatTime = (dt) => {
  if (!dt) return '--:--';
  return new Date(dt).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (dt) => {
  if (!dt) return '--';
  return new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const getMonthShort = (dt) => {
    if (!dt) return 'JAN';
    return new Date(dt).toLocaleString('en-US', { month: 'short' }).toUpperCase();
};

const getDay = (dt) => {
    if (!dt) return '01';
    return new Date(dt).getDate();
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getAvatarColor = (id) => {
  const colors = ['bg-blue-500', 'bg-purple-500', 'bg-emerald-500', 'bg-rose-500', 'bg-cyan-500'];
  return colors[id % colors.length];
};

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
  height: 4px;
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
