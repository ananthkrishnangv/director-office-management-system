<template>
  <div class="min-h-screen bg-neutral-50 font-sans text-gray-800 selection:bg-blue-100 selection:text-blue-900">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <img src="/images/udomslogo.png" alt="CSIR" class="h-10 w-auto" />
          <div class="hidden md:block h-8 w-px bg-gray-200"></div>
          <div>
             <h1 class="text-lg font-bold text-gray-900 leading-tight">Public Appointment Portal</h1>
             <p class="text-xs text-gray-500 font-medium">CSIR-Structural Engineering Research Centre</p>
          </div>
        </div>
        
        <div class="flex items-center gap-6">
          <div class="text-right hidden sm:block">
            <div class="text-sm font-semibold text-gray-900 font-mono">{{ currentTime }}</div>
            <div class="text-xs text-gray-500">{{ currentDate }}</div>
          </div>
          <div v-if="director" class="flex items-center gap-3 pl-6 border-l border-gray-200">
             <img src="/images/directorprofile.png" alt="Director" class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-gray-100" />
             <div class="hidden md:block">
                <p class="text-xs font-bold text-gray-900 uppercase tracking-wider">Director's Office</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                  </span>
                  <p class="text-[10px] font-medium text-gray-500">Approving Requests</p>
                </div>
             </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
      
      <!-- Success Message Overlay -->
      <transition name="fade">
        <div v-if="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
          <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center transform transition-all scale-100 border border-gray-100">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-50 mb-6 ring-8 ring-green-50/50">
              <svg class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Request Sent Successfully!</h3>
            <p class="text-gray-500 mb-8 leading-relaxed">Your appointment request has been submitted to the Director's office. You will receive a confirmation email once the request is reviewed and approved.</p>
            <button @click="resetForm" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-lg shadow-blue-600/20 active:scale-[0.98]">
              Book Another Appointment
            </button>
          </div>
        </div>
      </transition>

      <div class="grid lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Column: Calendar & Info -->
        <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
          <!-- Calendar Card -->
          <div class="bg-white rounded-2xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
              <h2 class="text-lg font-bold text-gray-900">Select Date</h2>
              <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-lg p-1 shadow-sm">
                <button @click="changeDate(-1)" class="p-1.5 hover:bg-gray-100 rounded-md transition-colors text-gray-600">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="w-px h-4 bg-gray-200"></div>
                <button @click="changeDate(1)" class="p-1.5 hover:bg-gray-100 rounded-md transition-colors text-gray-600">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
              </div>
            </div>
            
            <div class="p-8 flex flex-col items-center justify-center border-b border-gray-50">
               <div class="text-6xl font-black text-blue-600 mb-2 tracking-tighter">{{ selectedDate.getDate() }}</div>
               <div class="text-gray-900 font-bold text-xl">{{ selectedDate.toLocaleString('default', { month: 'long', year: 'numeric' }) }}</div>
               <div class="text-gray-400 font-medium mt-1 uppercase tracking-widest text-xs">{{ selectedDate.toLocaleDateString('en-US', { weekday: 'long' }) }}</div>
            </div>

            <div class="p-5">
              <div v-if="isDateBlocked" class="bg-red-50 border border-red-100 rounded-xl p-4 flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg shrink-0">
                  <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                  <p class="text-red-900 font-bold text-sm">Unavailable</p>
                  <p class="text-red-600 text-xs">Date is blocked for meetings</p>
                </div>
              </div>
              <div v-else class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 flex items-center gap-3">
                 <div class="p-2 bg-emerald-100 rounded-lg shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                 </div>
                 <div>
                   <p class="text-emerald-900 font-bold text-sm">Available</p>
                   <p class="text-emerald-600 text-xs">{{ slots.filter(s => s.available).length }} time slots open</p>
                 </div>
              </div>
            </div>
          </div>

          <!-- Helper Info -->
          <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-lg shadow-blue-600/20 relative overflow-hidden group">
             <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 transform rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
             </div>
             <h3 class="font-bold text-lg mb-2 relative z-10">Need Assistance?</h3>
             <p class="text-blue-100 text-sm mb-4 relative z-10 leading-relaxed">For urgent inquiries or special requests, please contact the Director's office directly.</p>
             <div class="flex items-center gap-3 text-sm font-medium bg-blue-700/50 p-3 rounded-lg backdrop-blur-sm relative z-10 border border-blue-500/30">
                <svg class="w-4 h-4 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span>+91 44 2254 9201</span>
             </div>
          </div>
        </div>

        <!-- Right Column: Slots & Form -->
        <div class="lg:col-span-8 space-y-8">
           
           <!-- Slots Section -->
           <div class="bg-white rounded-2xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-gray-200">
              <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                 <h2 class="text-lg font-bold text-gray-900">Available Time Slots</h2>
                 <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-md">IST (UTC+05:30)</span>
              </div>
              <div class="p-6">
                 <div v-if="loadingSlots" class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium">Checking availability...</span>
                 </div>
                 
                 <div v-else-if="slots.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <button 
                      v-for="slot in slots" 
                      :key="slot.time"
                      @click="selectSlot(slot)"
                      :disabled="!slot.available"
                      class="relative px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 border text-center group active:scale-[0.98]"
                      :class="{
                        'border-blue-500 bg-blue-600 text-white shadow-md shadow-blue-600/20': form.preferred_time === slot.time,
                        'border-gray-200 hover:border-blue-400 hover:shadow-sm text-gray-700 bg-white hover:text-blue-600': slot.available && form.preferred_time !== slot.time,
                        'border-gray-100 bg-gray-50 text-gray-400 cursor-not-allowed': !slot.available
                      }"
                    >
                      {{ slot.time }}
                      <div v-if="!slot.available" class="hidden group-hover:flex absolute -top-2 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded shadow-sm whitespace-nowrap z-10">
                         Already Booked
                      </div>
                    </button>
                 </div>
                 
                 <div v-else class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-gray-500 font-medium">No time slots available for this date.</p>
                    <p class="text-gray-400 text-sm mt-1">Please try selecting a different date.</p>
                 </div>
              </div>
           </div>

           <!-- Form Section -->
           <div class="bg-white rounded-2xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-gray-200 scroll-mt-24 transition-opacity duration-300" id="requestForm" :class="{'opacity-50 pointer-events-none grayscale': !form.preferred_time}">
              <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                 <h2 class="text-xl font-bold text-gray-900">Meeting Details</h2>
                 <p class="text-sm text-gray-500 mt-1">Please provide accurate information for quick approval.</p>
              </div>
              
              <div class="p-8">
                <form @submit.prevent="submit" class="space-y-8">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="space-y-1.5">
                      <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Full Name</label>
                      <input 
                        v-model="form.name" 
                        type="text" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder-gray-400 text-gray-900 bg-white font-medium"
                        placeholder="e.g. Dr. Jane Doe"
                        required
                      />
                    </div>
                    
                    <div class="space-y-1.5">
                      <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Email Address</label>
                      <input 
                        v-model="form.email" 
                        type="email" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder-gray-400 text-gray-900 bg-white font-medium"
                        placeholder="name@organization.com"
                        required
                      />
                    </div>
                    
                    <div class="space-y-1.5">
                      <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Phone Number</label>
                      <input 
                        v-model="form.phone" 
                        type="tel" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder-gray-400 text-gray-900 bg-white font-medium"
                        placeholder="+91 98765 43210"
                        required
                      />
                    </div>
                    
                    <div class="space-y-1.5">
                      <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Organization / Institute</label>
                      <input 
                        v-model="form.organization" 
                        type="text" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder-gray-400 text-gray-900 bg-white font-medium"
                        placeholder="Institute / Company Name"
                        required
                      />
                    </div>
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Purpose of Meeting</label>
                    <div class="relative">
                        <select 
                          v-model="form.purpose" 
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-gray-900 bg-white font-medium appearance-none"
                          required
                        >
                          <option value="" disabled selected>Select a purpose...</option>
                          <option value="Research Collaboration">Research Collaboration</option>
                          <option value="Official Visit">Official Visit</option>
                          <option value="Student Inquiry">Student Inquiry</option>
                          <option value="Vendor Presentation">Vendor Presentation</option>
                          <option value="Other">Other</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Agenda / Description</label>
                    <textarea 
                      v-model="form.description" 
                      rows="4" 
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder-gray-400 text-gray-900 bg-white font-medium resize-none"
                      placeholder="Please explicitly state the agenda and any other relevant details..."
                    ></textarea>
                  </div>

                  <!-- Summary Box -->
                  <div class="bg-blue-50 rounded-xl p-5 border border-blue-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                     <div class="flex items-center gap-4">
                        <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                           <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                           <p class="text-xs text-blue-500 font-bold uppercase tracking-wider">Confirmed Slot</p>
                           <p class="text-blue-900 font-bold text-lg">{{ selectedDateFormatted }} <span class="mx-1 opacity-50">|</span> {{ form.preferred_time }}</p>
                        </div>
                     </div>
                     <div class="flex gap-3 w-full sm:w-auto">
                        <button 
                          type="button" 
                          @click="resetForm" 
                          class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 border border-gray-200 bg-white rounded-lg hover:bg-gray-50 transition-colors w-full sm:w-auto"
                        >
                          Cancel
                        </button>
                        <button 
                          type="submit" 
                          :disabled="form.processing || !form.preferred_time"
                          class="px-8 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform active:scale-[0.98] w-full sm:w-auto"
                        >
                          {{ form.processing ? 'Submitting...' : 'Confirm Request' }}
                        </button>
                     </div>
                  </div>
                </form>
              </div>
           </div>
        
        </div>
      </div>
    </main>
    
    <footer class="bg-white border-t border-gray-200 mt-12 py-8">
       <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm text-gray-500">&copy; {{ new Date().getFullYear() }} CSIR-Structural Engineering Research Centre. All rights reserved.</p>
            <p class="text-xs text-gray-400 mt-2">Unified Director Office Management System</p>
       </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  director: Object,
  lab: Object,
  blockedDates: { type: Array, default: () => [] },
  existingMeetings: { type: Array, default: () => [] },
});

// Time
const currentTime = ref('');
const currentDate = ref('');

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
};

setInterval(updateTime, 1000);
updateTime();

// Date Selection
const selectedDate = ref(new Date());
const showSuccess = ref(false);
const loadingSlots = ref(false);
const slots = ref([]);

const selectedDateFormatted = computed(() => {
  return selectedDate.value.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
});

const isDateBlocked = computed(() => {
  const dateStr = selectedDate.value.toISOString().split('T')[0];
  return props.blockedDates.some(d => d.blocked_date === dateStr);
});

const generateSlots = async () => {
  loadingSlots.value = true;
  await new Promise(r => setTimeout(r, 300));
  
  if (isDateBlocked.value) {
    slots.value = [];
    loadingSlots.value = false;
    return;
  }

  const times = ['09:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM', '02:30 PM', '03:00 PM', '03:30 PM', '04:00 PM', '04:30 PM'];
  const dateStr = selectedDate.value.toISOString().split('T')[0];

  slots.value = times.map(time => {
    // Convert slot time to Date object for comparison
    const slotTime = new Date(`${dateStr} ${time}`);
    // Simple check: is this slot overlapping with any existing meeting?
    // We assume meetings have start/end in ISO string
    
    // Parse time to comparable value (minutes from start of day)
    const [timePart, modifier] = time.split(' ');
    let [hours, minutes] = timePart.split(':');
    if (hours === '12') hours = '00';
    if (modifier === 'PM') hours = parseInt(hours, 10) + 12;
    
    // Create date objects for this slot (30 min duration assumed default)
    const slotStart = new Date(selectedDate.value);
    slotStart.setHours(hours, minutes, 0, 0);
    const slotEnd = new Date(slotStart);
    slotEnd.setMinutes(slotEnd.getMinutes() + 30);

    const isTaken = props.existingMeetings.some(meeting => {
        const meetingStart = new Date(meeting.start);
        const meetingEnd = new Date(meeting.end);
        // Overlap check
        return (slotStart < meetingEnd && slotEnd > meetingStart);
    });

    return {
      time,
      available: !isTaken
    };
  });
  
  loadingSlots.value = false;
};

const changeDate = (days) => {
  const newDate = new Date(selectedDate.value);
  newDate.setDate(newDate.getDate() + days);
  if (newDate >= new Date().setHours(0,0,0,0)) {
    selectedDate.value = newDate;
    form.preferred_date = newDate.toISOString().split('T')[0];
    form.preferred_time = '';
    generateSlots();
  }
};

const selectSlot = (slot) => {
  if (slot.available) {
    form.preferred_time = slot.time;
  }
};

onMounted(() => {
  generateSlots();
});

// Re-generate if props change
watch(() => props.existingMeetings, generateSlots);

// Form
const form = useForm({
  name: '',
  email: '',
  phone: '',
  organization: '',
  purpose: '',
  description: '',
  preferred_date: new Date().toISOString().split('T')[0],
  preferred_time: '',
  duration: 30
});

const submit = () => {
  form.post('/api/public/meeting-request', {
    onSuccess: () => {
      showSuccess.value = true;
    },
  });
};

const resetForm = () => {
  showSuccess.value = false;
  form.reset();
  form.preferred_time = '';
  selectedDate.value = new Date();
  form.preferred_date = selectedDate.value.toISOString().split('T')[0];
  generateSlots();
};
</script>

<style scoped>
/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent; 
}
::-webkit-scrollbar-thumb {
  background: #cbd5e1; 
  border-radius: 6px;
}
::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; 
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
