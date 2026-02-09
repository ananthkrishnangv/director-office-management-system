<template>
  <div class="meeting-request-wrapper">
    <!-- Animated Background -->
    <div class="bg-effects">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
    </div>
    
    <!-- Header -->
    <header class="request-header">
      <div class="header-content">
        <div class="header-left">
          <img src="/images/udomslogo.png" alt="UDOMS" class="logo" />
          <div class="title-group">
            <h1>Unified Director Office</h1>
            <p>Management System</p>
          </div>
        </div>
        
        <div class="header-right">
          <div class="datetime">
            <div class="time text-lg">{{ currentTime }}</div>
            <div class="date text-xs">{{ currentDate }}</div>
          </div>
          <img src="/images/directorprofile.png" alt="Director" class="director-photo w-10 h-10" />
        </div>
      </div>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
      <div class="content-grid">
        <!-- Left: Availability Calendar -->
        <div class="availability-section">
          <div class="glass-card">
            <div class="card-header">
              <div class="header-icon purple">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/></svg>
              </div>
              <div>
                <h2>Director's Availability</h2>
                <p>Select a date to view available time slots</p>
              </div>
            </div>
            
            <!-- Date Navigator -->
            <div class="date-navigator" :class="{'bg-red-50 border border-red-100': isDateBlocked}">
              <button @click="changeDate(-1)" class="nav-btn">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              </button>
              <div class="text-center">
                <div class="selected-date">{{ selectedDateFormatted }}</div>
                <div v-if="isDateBlocked" class="text-[10px] text-red-500 font-bold uppercase tracking-wide">Date Blocked</div>
              </div>
              <button @click="changeDate(1)" class="nav-btn">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </button>
            </div>
            
            <!-- Time Slots -->
            <div class="slots-container">
              <div v-if="loadingSlots" class="loading-state">
                <div class="spinner"></div>
                <p>Loading slots...</p>
              </div>
              
              <div v-else-if="slots.length > 0" class="slots-grid">
                <button 
                  v-for="slot in slots" 
                  :key="slot.time"
                  @click="selectSlot(slot)"
                  :disabled="!slot.available"
                  class="slot-btn"
                  :class="{ 
                    selected: slot.time === form.preferred_time,
                    unavailable: !slot.available 
                  }"
                >
                  {{ slot.time }}
                  <span v-if="!slot.available" class="booked-label">Booked</span>
                </button>
              </div>
              
              <div v-else class="empty-slots">
                <p v-if="isDateBlocked">This date is unavailable for meetings.</p>
                <p v-else>No slots available for this date</p>
              </div>
            </div>
          </div>
          
          <!-- Contact Card -->
          <div class="glass-card contact-card">
            <h3>Need immediate assistance?</h3>
            <p>Contact the Director's office directly for urgent requests.</p>
            <a href="tel:+914422549201" class="phone-link">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
              +91 44 2254 9201
            </a>
          </div>
        </div>
        
        <!-- Right: Request Form -->
        <div class="form-section">
          <div class="glass-card form-card">
            <div class="form-header">
              <h2>Meeting Request Form</h2>
              <p>Submit your request for a meeting with the Director</p>
            </div>
            
            <form @submit.prevent="submit" class="request-form">
              <div class="form-row">
                <div class="form-group">
                  <label>Full Name <span class="required">*</span></label>
                  <input type="text" v-model="form.name" required placeholder="Dr. John Doe" class="form-input" />
                  <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                </div>
                <div class="form-group">
                  <label>Organization <span class="required">*</span></label>
                  <input type="text" v-model="form.organization" required placeholder="IIT Madras" class="form-input" />
                </div>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label>Email Address <span class="required">*</span></label>
                  <input type="email" v-model="form.email" required placeholder="john@example.com" class="form-input" />
                  <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                </div>
                <div class="form-group">
                  <label>Phone Number <span class="required">*</span></label>
                  <input type="tel" v-model="form.phone" required placeholder="+91 98765 43210" class="form-input" />
                </div>
              </div>
              
              <div class="form-group">
                <label>Purpose of Meeting <span class="required">*</span></label>
                <input type="text" v-model="form.purpose" required placeholder="Brief subject (e.g., Research Collaboration)" class="form-input" />
              </div>
              
              <div class="form-group">
                <label>Detailed Description</label>
                <textarea v-model="form.description" rows="3" placeholder="Provide more context..." class="form-input"></textarea>
              </div>
              
              <!-- Selected Slot Summary -->
              <div class="selection-summary">
                <div class="summary-item">
                  <span class="label">Selected Date</span>
                  <span class="value">{{ selectedDateFormatted }}</span>
                </div>
                <div class="divider"></div>
                <div class="summary-item">
                  <span class="label">Selected Time</span>
                  <span class="value" :class="{ placeholder: !form.preferred_time }">
                    {{ form.preferred_time || 'Select a slot on the left' }}
                  </span>
                </div>
              </div>
              
              <button 
                type="submit" 
                :disabled="form.processing || !form.preferred_time" 
                class="submit-btn"
              >
                <span v-if="form.processing">Submitting...</span>
                <span v-else>Submit Meeting Request</span>
              </button>
              
              <p class="privacy-note">By submitting, you agree to our privacy policy.</p>
            </form>
          </div>
        </div>
      </div>
    </main>
    
    <!-- Footer -->
    <footer class="request-footer">
      © 2026 CSIR-SERC. All rights reserved.
    </footer>
    
    <!-- Success Modal -->
    <div v-if="showSuccess" class="modal-overlay">
      <div class="modal-card">
        <div class="success-icon">
          <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <h3>Request Submitted!</h3>
        <p>Your meeting request has been sent. You will receive a confirmation email once approved.</p>
        <button @click="resetForm" class="modal-btn">Submit Another Request</button>
      </div>
    </div>
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
  // Simulate delay for feel, or remove if instant
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
  // Prevent going to past
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

// Re-generate if props change (though typically they are static for the page load, but good practice)
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
  // Reset date to today
  selectedDate.value = new Date();
  form.preferred_date = selectedDate.value.toISOString().split('T')[0];
  generateSlots();
};
</script>

<style scoped>
.meeting-request-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #e3f2fd 0%, #e8eaf6 50%, #ede7f6 100%);
  position: relative;
  overflow-x: hidden;
}

/* Background Blobs */
.bg-effects {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.5;
  animation: float 20s ease-in-out infinite;
}

.blob-1 {
  width: 500px;
  height: 500px;
  background: linear-gradient(135deg, #90caf9 0%, #80deea 100%);
  top: -100px;
  right: -100px;
}

.blob-2 {
  width: 600px;
  height: 600px;
  background: linear-gradient(135deg, #b39ddb 0%, #ce93d8 100%);
  bottom: -200px;
  left: -200px;
  animation-delay: -10s;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.05); }
  66% { transform: translate(-20px, 20px) scale(0.95); }
}

/* Header */
.request-header {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.5);
  position: sticky;
  top: 0;
  z-index: 50;
}

.header-content {
  margin: 0 auto;
  padding: 12px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  font-size: 1.25rem;
  font-weight: 700;
  color: #1565c0;
  margin: 0;
}

.title-group p {
  font-size: 0.8rem;
  color: #42a5f5;
  margin: 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 24px;
}

.datetime {
  text-align: right;
}

.datetime .time {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1565c0;
  font-family: monospace;
}

.datetime .date {
  font-size: 0.8rem;
  color: #64b5f6;
}

.director-photo {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 3px solid rgba(33, 150, 243, 0.2);
  object-fit: cover;
}

/* Main Content */
.main-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px;
  position: relative;
  z-index: 10;
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr 1.4fr;
  gap: 32px;
}

/* Glass Cards */
.glass-card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.6);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
}

/* Availability Section */
.availability-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.card-header {
  display: flex;
  gap: 12px;
  padding: 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.header-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.header-icon svg {
  width: 24px;
  height: 24px;
}

.header-icon.purple {
  background: linear-gradient(135deg, #bbdefb, #90caf9);
  color: #1565c0;
}

.card-header h2 {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1565c0;
  margin: 0;
}

.card-header p {
  font-size: 0.875rem;
  color: #90a4ae;
  margin: 4px 0 0;
}

/* Date Navigator */
.date-navigator {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 20px 24px;
  padding: 8px;
  background: linear-gradient(135deg, #e3f2fd, #ede7f6);
  border-radius: 16px;
}

.nav-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border: none;
  border-radius: 12px;
  color: #1565c0;
  cursor: pointer;
  transition: all 0.2s;
}

.nav-btn:hover {
  background: #1565c0;
  color: white;
}

.selected-date {
  font-weight: 600;
  color: #1565c0;
}

/* Slots */
.slots-container {
  padding: 0 16px 16px;
}

.slots-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.slot-btn {
  padding: 14px 16px;
  background: white;
  border: 2px solid #e3f2fd;
  border-radius: 14px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #424242;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.slot-btn:hover:not(:disabled) {
  border-color: #1565c0;
  background: #e3f2fd;
}

.slot-btn.selected {
  background: linear-gradient(135deg, #1565c0, #42a5f5);
  border-color: transparent;
  color: white;
  box-shadow: 0 4px 16px rgba(21, 101, 192, 0.3);
}

.slot-btn.unavailable {
  background: #f5f5f5;
  border-color: #eeeeee;
  color: #bdbdbd;
  cursor: not-allowed;
  text-decoration: line-through;
}

.booked-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  text-decoration: none;
}

.loading-state {
  text-align: center;
  padding: 48px;
  color: #90a4ae;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e3f2fd;
  border-top-color: #1565c0;
  border-radius: 50%;
  margin: 0 auto 16px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-slots {
  text-align: center;
  padding: 48px;
  color: #90a4ae;
  background: #fafafa;
  border-radius: 16px;
  border: 2px dashed #e0e0e0;
}

/* Contact Card */
.contact-card {
  padding: 24px;
  background: linear-gradient(135deg, rgba(187, 222, 251, 0.5), rgba(225, 190, 231, 0.3));
}

.contact-card h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #1565c0;
  margin: 0 0 8px;
}

.contact-card p {
  font-size: 0.875rem;
  color: #5c6bc0;
  margin: 0 0 16px;
}

.phone-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  font-weight: 700;
  color: #1565c0;
  text-decoration: none;
}

.phone-link:hover {
  text-decoration: underline;
}

/* Form Section */
.form-card {
  overflow: hidden;
}

.form-header {
  padding: 16px;
  background: linear-gradient(135deg, #1565c0, #42a5f5);
  color: white;
}

.form-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 4px;
}

.form-header p {
  font-size: 0.875rem;
  opacity: 0.9;
  margin: 0;
}

.request-form {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #424242;
  margin-bottom: 8px;
}

.required {
  color: #e53935;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 2px solid #e0e0e0;
  border-radius: 14px;
  font-size: 0.9rem;
  transition: all 0.2s;
  background: white;
}

.form-input:focus {
  outline: none;
  border-color: #1565c0;
  box-shadow: 0 0 0 4px rgba(21, 101, 192, 0.1);
}

.form-input::placeholder {
  color: #bdbdbd;
}

textarea.form-input {
  resize: vertical;
  min-height: 80px;
}

.field-error {
  font-size: 0.75rem;
  color: #e53935;
  margin-top: 6px;
}

/* Selection Summary */
.selection-summary {
  display: flex;
  align-items: center;
  gap: 24px;
  padding: 16px 20px;
  background: linear-gradient(135deg, #e3f2fd, #ede7f6);
  border-radius: 16px;
}

.summary-item .label {
  display: block;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #78909c;
  margin-bottom: 4px;
}

.summary-item .value {
  font-weight: 600;
  color: #1565c0;
}

.summary-item .value.placeholder {
  color: #b0bec5;
  font-style: italic;
  font-weight: 400;
}

.divider {
  width: 1px;
  height: 40px;
  background: #b0bec5;
}

/* Submit Button */
.submit-btn {
  width: 100%;
  padding: 18px 24px;
  background: linear-gradient(135deg, #1565c0, #42a5f5);
  color: white;
  border: none;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 8px 24px rgba(21, 101, 192, 0.3);
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(21, 101, 192, 0.4);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.privacy-note {
  text-align: center;
  font-size: 0.75rem;
  color: #9e9e9e;
  margin: 0;
}

/* Footer */
.request-footer {
  text-align: center;
  padding: 24px;
  font-size: 0.8rem;
  color: #78909c;
  position: relative;
  z-index: 10;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
}

.modal-card {
  background: white;
  border-radius: 32px;
  padding: 48px;
  max-width: 420px;
  width: 100%;
  text-align: center;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
  animation: scaleIn 0.3s ease-out;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}

.success-icon {
  width: 80px;
  height: 80px;
  background: #e8f5e9;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
}

.success-icon svg {
  width: 40px;
  height: 40px;
  color: #43a047;
}

.modal-card h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #212121;
  margin: 0 0 12px;
}

.modal-card p {
  font-size: 0.9rem;
  color: #757575;
  margin: 0 0 32px;
}

.modal-btn {
  padding: 16px 32px;
  background: linear-gradient(135deg, #1565c0, #42a5f5);
  color: white;
  border: none;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.modal-btn:hover {
  transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .header-content {
    padding: 12px 16px;
  }
  
  .title-group {
    display: none;
  }
  
  .main-content {
    padding: 16px;
  }
}
</style>
