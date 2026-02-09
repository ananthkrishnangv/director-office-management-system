<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-neutral-800">Profile Settings</h1>
        <p class="text-neutral-500 mt-1">Manage your account information and preferences</p>
      </div>

      <div class="grid gap-6">
        <!-- Profile Information Card -->
        <div class="card bg-base-100 shadow-xl border border-base-200 p-6">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-semibold text-neutral-800">Profile Information</h2>
              <p class="text-sm text-neutral-500">Update your name, email, and contact details</p>
            </div>
          </div>

          <form @submit.prevent="updateProfile" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-5">
              <!-- Name -->
              <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Full Name</span></label>
                <input 
                  type="text" 
                  v-model="profileForm.name" 
                  class="input input-bordered w-full"
                  placeholder="Your full name"
                />
                <p v-if="profileForm.errors.name" class="text-error text-xs mt-1">{{ profileForm.errors.name }}</p>
              </div>

              <!-- Email -->
              <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Email Address</span></label>
                <input 
                  type="email" 
                  v-model="profileForm.email" 
                  class="input input-bordered w-full"
                  placeholder="your.email@serc.res.in"
                />
                <p v-if="profileForm.errors.email" class="text-error text-xs mt-1">{{ profileForm.errors.email }}</p>
              </div>

              <!-- Phone -->
              <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Phone Number</span></label>
                <input 
                  type="tel" 
                  v-model="profileForm.phone" 
                  class="input input-bordered w-full"
                  placeholder="+91 9876543210"
                />
                <p v-if="profileForm.errors.phone" class="text-error text-xs mt-1">{{ profileForm.errors.phone }}</p>
              </div>

              <!-- Department -->
              <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Department</span></label>
                <input 
                  type="text" 
                  v-model="profileForm.department" 
                  class="input input-bordered w-full"
                  placeholder="Your department"
                />
                <p v-if="profileForm.errors.department" class="text-error text-xs mt-1">{{ profileForm.errors.department }}</p>
              </div>
            </div>

            <div class="flex justify-end pt-2">
              <button 
                type="submit" 
                :disabled="profileForm.processing"
                class="btn btn-primary text-white"
              >
                <svg v-if="profileForm.processing" class="w-4 h-4 animate-spin mr-2" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" class="opacity-25" />
                  <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75" />
                </svg>
                {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>

          <!-- Success Message -->
          <div v-if="showSuccess" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="text-sm text-green-700 font-medium">Profile updated successfully!</span>
          </div>
        </div>

        <!-- Password Change Card -->
        <div class="fluent-card p-6">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-semibold text-neutral-800">Change Password</h2>
              <p class="text-sm text-neutral-500">Update your password to keep your account secure</p>
            </div>
          </div>

          <form @submit.prevent="updatePassword" class="space-y-5">
            <div class="grid md:grid-cols-3 gap-5">
              <!-- Current Password -->
              <div class="form-group">
                <label class="form-label">Current Password</label>
                <input 
                  type="password" 
                  v-model="passwordForm.current_password" 
                  class="fluent-input"
                  placeholder="••••••••"
                />
                <p v-if="passwordForm.errors.current_password" class="error-text">{{ passwordForm.errors.current_password }}</p>
              </div>

              <!-- New Password -->
              <div class="form-group">
                <label class="form-label">New Password</label>
                <input 
                  type="password" 
                  v-model="passwordForm.password" 
                  class="fluent-input"
                  placeholder="••••••••"
                />
                <p v-if="passwordForm.errors.password" class="error-text">{{ passwordForm.errors.password }}</p>
              </div>

              <!-- Confirm Password -->
              <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input 
                  type="password" 
                  v-model="passwordForm.password_confirmation" 
                  class="fluent-input"
                  placeholder="••••••••"
                />
              </div>
            </div>

            <div class="flex justify-end pt-2">
              <button 
                type="submit" 
                :disabled="passwordForm.processing"
                class="btn btn-primary"
              >
                <svg v-if="passwordForm.processing" class="w-4 h-4 animate-spin mr-2" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" class="opacity-25" />
                  <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75" />
                </svg>
                {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
              </button>
            </div>
          </form>

          <!-- Password Success Message -->
          <div v-if="showPasswordSuccess" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="text-sm text-green-700 font-medium">Password updated successfully!</span>
          </div>
        </div>

        <!-- Account Info Card -->
        <div class="fluent-card p-6">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl bg-neutral-100 flex items-center justify-center text-neutral-600">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-semibold text-neutral-800">Account Information</h2>
              <p class="text-sm text-neutral-500">Your account details and role</p>
            </div>
          </div>

          <div class="grid md:grid-cols-3 gap-4">
            <div class="bg-neutral-50 rounded-xl p-4">
              <p class="text-xs text-neutral-500 uppercase tracking-wide font-medium">Role</p>
              <p class="text-sm font-semibold text-neutral-800 mt-1 capitalize">{{ $page.props.auth.user.role }}</p>
            </div>
            <div class="bg-neutral-50 rounded-xl p-4">
              <p class="text-xs text-neutral-500 uppercase tracking-wide font-medium">Laboratory</p>
              <p class="text-sm font-semibold text-neutral-800 mt-1">{{ $page.props.auth.user.lab?.lab_name || 'N/A' }}</p>
            </div>
            <div class="bg-neutral-50 rounded-xl p-4">
              <p class="text-xs text-neutral-500 uppercase tracking-wide font-medium">Last Login</p>
              <p class="text-sm font-semibold text-neutral-800 mt-1">{{ formatDate($page.props.auth.user.last_login_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';

const page = usePage();
const user = page.props.auth.user;

const showSuccess = ref(false);
const showPasswordSuccess = ref(false);

const profileForm = useForm({
  name: user.name || '',
  email: user.email || '',
  phone: user.phone || '',
  department: user.department || '',
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updateProfile = () => {
  profileForm.put('/profile', {
    preserveScroll: true,
    onSuccess: () => {
      showSuccess.value = true;
      setTimeout(() => showSuccess.value = false, 3000);
    },
  });
};

const updatePassword = () => {
  passwordForm.put('/profile/password', {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
      showPasswordSuccess.value = true;
      setTimeout(() => showPasswordSuccess.value = false, 3000);
    },
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'Never';
  return new Date(dateStr).toLocaleDateString('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
