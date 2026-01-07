<template>
  <div class="login-page">
    <!-- Full-screen Background Image with Overlay -->
    <div class="background-container">
      <img 
        src="/images/CSIR-SERC Main Building.png" 
        alt="CSIR-SERC Main Building" 
        class="background-image"
      />
      <div class="background-overlay"></div>
    </div>

    <!-- Main Content Container -->
    <div class="login-container">
      <!-- Left Side: Director Profile -->
      <div class="director-section">
        <div class="director-card">
          <!-- Director Image -->
          <div class="director-image-wrapper">
            <img 
              src="/images/directorprofile.png" 
              alt="Director CSIR-SERC" 
              class="director-image"
            />
            <div class="image-ring"></div>
          </div>

          <!-- Director Info -->
          <div class="director-info">
            <h2 class="director-name">Dr. N. Anandavalli</h2>
            <p class="director-title">Director, CSIR-SERC</p>
          </div>

          <!-- Divider -->
          <div class="info-divider"></div>

          <!-- Director Bio -->
          <div class="director-bio">
            <p class="bio-text">
              Ph.D in Civil Engineering from Anna University. Fellow of the Indian National Academy of Engineering (INAE). 
              Specialized in Structural Dynamics, Shock Resistant Structures, and Multi-scale Modelling.
            </p>
          </div>

          <!-- Areas of Interest -->
          <div class="expertise-section">
            <h4 class="expertise-title">Areas of Expertise</h4>
            <div class="expertise-tags">
              <span class="tag">Structural Dynamics</span>
              <span class="tag">Shock Resistant Structures</span>
              <span class="tag">Computational Methods</span>
              <span class="tag">Multi-scale Modelling</span>
            </div>
          </div>

          <!-- Awards Badge -->
          <div class="awards-badge">
            <svg class="award-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
            </svg>
            <span>Raman Research Fellow | INAE Fellow</span>
          </div>

          <!-- Appointment Request Link -->
          <a href="/request-meeting" class="appointment-link">
            <svg class="link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Request an Appointment</span>
            <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>
      </div>

      <!-- Right Side: Login Form -->
      <div class="form-section">
        <div class="login-card">
          <!-- Branding -->
          <div class="branding">
            <div class="logo-row">
              <img src="/images/udomslogo.png" alt="UDOMS Logo" class="logo" />
              <div class="logo-divider"></div>
              <img src="/images/csirlogo.jpg" alt="CSIR Logo" class="logo csir-logo" />
            </div>
            <h1 class="app-title">Unified Director Office Management System</h1>
            <p class="app-subtitle">CSIR - Structural Engineering Research Centre</p>
          </div>

          <!-- Form Header -->
          <div class="form-header">
            <h2 class="form-title">Welcome Back</h2>
            <p class="form-subtitle">Please sign in to continue</p>
          </div>

          <!-- Login Form -->
          <form @submit.prevent="submit" class="login-form">
            <!-- Laboratory Selector -->
            <div class="form-group">
              <label class="form-label">Laboratory</label>
              <div class="select-wrapper">
                <select v-model="form.lab_id" class="fluent-select">
                  <option v-for="lab in labs" :key="lab.id" :value="lab.id">
                    {{ lab.lab_code }} - {{ lab.lab_name }}
                  </option>
                </select>
                <div class="select-arrow">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- Email Input -->
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input 
                type="email" 
                v-model="form.email" 
                required 
                class="fluent-input"
                placeholder="name@serc.res.in"
              />
              <p v-if="form.errors.email" class="error-text">{{ form.errors.email }}</p>
            </div>

            <!-- Password Input -->
            <div class="form-group">
              <label class="form-label">Password</label>
              <div class="password-wrapper">
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  v-model="form.password" 
                  required 
                  class="fluent-input"
                  placeholder="••••••••"
                />
                <button 
                  type="button" 
                  @click="showPassword = !showPassword" 
                  class="password-toggle"
                >
                  <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <p v-if="form.errors.password" class="error-text">{{ form.errors.password }}</p>
            </div>

            <!-- Remember Me -->
            <div class="form-options">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.remember" class="checkbox-input" />
                <span class="checkbox-custom"></span>
                <span class="checkbox-text">Remember me</span>
              </label>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit" 
              :disabled="form.processing" 
              class="submit-button"
            >
              <span v-if="form.processing" class="button-loading">
                <svg class="spinner" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4" />
                </svg>
                Signing in...
              </span>
              <span v-else class="button-content">Sign In</span>
            </button>
          </form>

          <!-- Footer -->
          <div class="card-footer">
            <p class="footer-text">© 2026 CSIR-SERC. All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    labs: Array,
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    lab_id: props.labs && props.labs.length > 0 ? props.labs[0].id : '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
  font-family: 'Segoe UI', 'Noto Sans', system-ui, sans-serif;
}

/* Background Styles */
.background-container {
  position: fixed;
  inset: 0;
  z-index: 0;
}

.background-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

.background-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(240, 245, 250, 0.92) 0%,
    rgba(224, 235, 245, 0.88) 50%,
    rgba(240, 245, 250, 0.92) 100%
  );
  backdrop-filter: blur(8px);
}

/* Main Container */
.login-container {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 1000px;
  display: flex;
  gap: 2rem;
  animation: fadeIn 0.6s ease-out;
}

/* Director Section - Left Side */
.director-section {
  flex: 1;
  display: none;
}

@media (min-width: 900px) {
  .director-section {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.director-card {
  text-align: center;
  padding: 2.5rem 2rem;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(12px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.8);
  box-shadow: 0 8px 32px rgba(0, 60, 120, 0.08);
  max-width: 380px;
}

.director-image-wrapper {
  position: relative;
  display: inline-block;
  margin-bottom: 1.25rem;
}

.director-image {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid white;
  box-shadow: 0 8px 24px rgba(0, 90, 158, 0.15);
}

.image-ring {
  position: absolute;
  inset: -6px;
  border-radius: 50%;
  border: 2px solid rgba(0, 120, 212, 0.3);
  animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.05); opacity: 0.7; }
}

.director-info {
  margin-bottom: 1rem;
}

.director-name {
  font-size: 1.375rem;
  font-weight: 700;
  color: #1a365d;
  margin-bottom: 0.25rem;
}

.director-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #0078d4;
  margin-bottom: 0.125rem;
}

.director-designation {
  font-size: 0.8125rem;
  color: #5a6d80;
}

.info-divider {
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, #0078d4, #00bcf2);
  border-radius: 2px;
  margin: 1.25rem auto;
}

.director-bio {
  margin-bottom: 1.25rem;
}

.bio-text {
  font-size: 0.8125rem;
  line-height: 1.6;
  color: #4a5568;
}

.expertise-section {
  margin-bottom: 1.25rem;
}

.expertise-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: #5a6d80;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.75rem;
}

.expertise-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: center;
}

.tag {
  padding: 0.375rem 0.75rem;
  background: rgba(0, 120, 212, 0.08);
  color: #0078d4;
  font-size: 0.6875rem;
  font-weight: 500;
  border-radius: 12px;
  border: 1px solid rgba(0, 120, 212, 0.15);
}

.awards-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 152, 0, 0.1));
  border-radius: 20px;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.award-icon {
  width: 18px;
  height: 18px;
  color: #f59e0b;
}

.awards-badge span {
  font-size: 0.75rem;
  font-weight: 600;
  color: #b45309;
}

/* Form Section - Right Side */
.form-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-card {
  width: 100%;
  max-width: 420px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 2.5rem;
  box-shadow: 
    0 20px 50px rgba(0, 60, 120, 0.1),
    0 0 0 1px rgba(255, 255, 255, 0.8);
}

.branding {
  text-align: center;
  margin-bottom: 1.75rem;
}

.logo-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.logo {
  height: 44px;
  width: auto;
  object-fit: contain;
}

.csir-logo {
  background: white;
  padding: 3px;
  border-radius: 6px;
}

.logo-divider {
  width: 1px;
  height: 32px;
  background: #d1d9e0;
}

.app-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1a365d;
  margin-bottom: 0.25rem;
}

.app-subtitle {
  font-size: 0.75rem;
  color: #5a6d80;
  font-weight: 500;
}

.form-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.form-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a365d;
  margin-bottom: 0.25rem;
}

.form-subtitle {
  font-size: 0.875rem;
  color: #64748b;
}

/* Form Styles */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.form-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #374151;
}

.fluent-input {
  width: 100%;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.9375rem;
  color: #1e293b;
  transition: all 0.2s ease;
}

.fluent-input::placeholder {
  color: #94a3b8;
}

.fluent-input:focus {
  outline: none;
  border-color: #0078d4;
  background: white;
  box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.12);
}

/* Select Styles */
.select-wrapper {
  position: relative;
}

.fluent-select {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.9375rem;
  color: #1e293b;
  appearance: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.fluent-select:focus {
  outline: none;
  border-color: #0078d4;
  background: white;
  box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.12);
}

.select-arrow {
  position: absolute;
  right: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #64748b;
}

.select-arrow svg {
  width: 18px;
  height: 18px;
}

/* Password Wrapper */
.password-wrapper {
  position: relative;
}

.password-wrapper .fluent-input {
  padding-right: 2.75rem;
}

.password-toggle {
  position: absolute;
  right: 0.625rem;
  top: 50%;
  transform: translateY(-50%);
  padding: 0.375rem;
  background: transparent;
  border: none;
  cursor: pointer;
  color: #64748b;
  transition: color 0.2s ease;
}

.password-toggle:hover {
  color: #0078d4;
}

.password-toggle svg {
  width: 18px;
  height: 18px;
}

/* Checkbox Styles */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  cursor: pointer;
}

.checkbox-input {
  display: none;
}

.checkbox-custom {
  width: 18px;
  height: 18px;
  border: 1.5px solid #d1d5db;
  border-radius: 5px;
  position: relative;
  transition: all 0.2s ease;
}

.checkbox-input:checked + .checkbox-custom {
  background: #0078d4;
  border-color: #0078d4;
}

.checkbox-input:checked + .checkbox-custom::after {
  content: '';
  position: absolute;
  left: 5px;
  top: 2px;
  width: 5px;
  height: 9px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.checkbox-text {
  font-size: 0.8125rem;
  color: #4b5563;
  font-weight: 500;
}

/* Submit Button */
.submit-button {
  width: 100%;
  padding: 0.875rem;
  background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 0.5rem;
  box-shadow: 0 4px 14px rgba(0, 120, 212, 0.25);
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(0, 120, 212, 0.35);
}

.submit-button:active:not(:disabled) {
  transform: translateY(0);
}

.submit-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.button-content,
.button-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.spinner {
  width: 18px;
  height: 18px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Error Text */
.error-text {
  font-size: 0.75rem;
  color: #dc2626;
  font-weight: 500;
  margin-top: 0.25rem;
}

/* Footer */
.card-footer {
  margin-top: 1.5rem;
  padding-top: 1.25rem;
  border-top: 1px solid #e5e7eb;
  text-align: center;
}

.footer-text {
  font-size: 0.6875rem;
  color: #9ca3af;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Adjustments */
@media (max-width: 899px) {
  .login-card {
    padding: 2rem;
  }
}

/* Appointment Link */
.appointment-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  margin-top: 1.25rem;
  padding: 0.875rem 1rem;
  background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
  color: white;
  text-decoration: none;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 14px rgba(0, 120, 212, 0.25);
}

.appointment-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 120, 212, 0.35);
}

.appointment-link .link-icon {
  width: 18px;
  height: 18px;
}

.appointment-link .arrow-icon {
  width: 16px;
  height: 16px;
  margin-left: auto;
}
</style>
