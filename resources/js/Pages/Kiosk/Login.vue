<template>
  <div class="kiosk-login-wrapper">
    <!-- Animated Background -->
    <div class="bg-effects">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>
    </div>
    
    <!-- Login Card -->
    <div class="login-card">
      <div class="logo-section">
        <img src="/images/udomslogo.png" alt="UDOMS" class="logo" />
        <h1>Director's Schedule</h1>
        <p>CSIR-SERC Kiosk Mode</p>
      </div>
      
      <form @submit.prevent="submit" class="login-form">
        <div class="form-group">
          <label>Enter Kiosk PIN</label>
          <div class="pin-input-wrapper">
            <input 
              type="password" 
              v-model="form.pin"
              maxlength="6"
              placeholder="••••"
              class="pin-input"
              autofocus
            />
          </div>
          <p v-if="errors.pin" class="error-message">{{ errors.pin }}</p>
        </div>
        
        <button type="submit" class="submit-btn" :disabled="form.processing">
          <span v-if="form.processing">Verifying...</span>
          <span v-else>Access Dashboard</span>
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
        </button>
      </form>
      
      <div class="help-text">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Contact admin for access credentials
      </div>
    </div>
    
    <!-- Footer -->
    <div class="footer-text">
      Unified Director Office Management System
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  errors: { type: Object, default: () => ({}) }
});

const form = reactive({
  pin: '',
  processing: false
});

const submit = () => {
  form.processing = true;
  router.post('/kiosk/authenticate', { pin: form.pin }, {
    onFinish: () => form.processing = false
  });
};
</script>

<style scoped>
.kiosk-login-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #e8eaf6 0%, #f3e5f5 50%, #fce4ec 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
  position: relative;
  overflow: hidden;
}

/* Animated Blobs */
.bg-effects {
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
  width: 500px;
  height: 500px;
  background: linear-gradient(135deg, #e1bee7 0%, #f8bbd9 100%);
  top: -150px;
  right: -150px;
  animation-delay: 0s;
}

.blob-2 {
  width: 600px;
  height: 600px;
  background: linear-gradient(135deg, #b39ddb 0%, #90caf9 100%);
  bottom: -200px;
  left: -200px;
  animation-delay: -7s;
}

.blob-3 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #80deea 0%, #a5d6a7 100%);
  top: 40%;
  left: 60%;
  animation-delay: -14s;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.05); }
  66% { transform: translate(-20px, 20px) scale(0.95); }
}

/* Login Card */
.login-card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(24px);
  border-radius: 32px;
  padding: 48px;
  border: 1px solid rgba(255, 255, 255, 0.6);
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 420px;
  position: relative;
  z-index: 10;
}

.logo-section {
  text-align: center;
  margin-bottom: 32px;
}

.logo {
  width: 80px;
  height: auto;
  margin-bottom: 16px;
}

.logo-section h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #4a148c;
  margin: 0 0 8px;
}

.logo-section p {
  font-size: 0.9rem;
  color: #7b1fa2;
}

/* Form */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #616161;
  margin-bottom: 8px;
  text-align: center;
}

.pin-input-wrapper {
  display: flex;
  justify-content: center;
}

.pin-input {
  width: 200px;
  text-align: center;
  font-size: 2rem;
  letter-spacing: 0.5rem;
  padding: 16px 24px;
  border: 2px solid #e0e0e0;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.7);
  transition: all 0.3s;
  color: #4a148c;
  font-weight: 600;
}

.pin-input:focus {
  outline: none;
  border-color: #7c4dff;
  box-shadow: 0 0 0 4px rgba(124, 77, 255, 0.1);
}

.pin-input::placeholder {
  color: #bdbdbd;
  font-size: 1.5rem;
}

.error-message {
  text-align: center;
  font-size: 0.875rem;
  color: #c62828;
  margin-top: 8px;
}

.submit-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 16px 24px;
  background: linear-gradient(135deg, #7c4dff, #e040fb);
  color: white;
  border: none;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 8px 24px rgba(124, 77, 255, 0.3);
}

.submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(124, 77, 255, 0.4);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.help-text {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 24px;
  font-size: 0.8rem;
  color: #9e9e9e;
}

/* Footer */
.footer-text {
  position: absolute;
  bottom: 24px;
  text-align: center;
  font-size: 0.875rem;
  color: #7b1fa2;
}
</style>
