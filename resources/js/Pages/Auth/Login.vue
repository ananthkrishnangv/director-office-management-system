<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-500 to-primary-700 py-12 px-4">
    <div class="max-w-md w-full">
      <!-- Logo and Header -->
      <div class="text-center mb-8">
        <div class="w-20 h-20 bg-white rounded-full mx-auto flex items-center justify-center shadow-lg mb-4">
          <svg class="w-12 h-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Director Office Management</h1>
        <p class="text-primary-100 mt-2">CSIR-SERC</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white rounded-xl shadow-2xl p-8">
        <h2 class="text-xl font-semibold text-neutral-800 mb-6">Sign In</h2>
        
        <form @submit.prevent="submit">
          <div class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Email</label>
              <input v-model="form.email" type="email" class="input" placeholder="Enter your email" required />
              <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Password</label>
              <input v-model="form.password" type="password" class="input" placeholder="Enter your password" required />
              <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
              <label class="flex items-center">
                <input v-model="form.remember" type="checkbox" class="rounded border-neutral-300 text-primary-500 focus:ring-primary-500" />
                <span class="ml-2 text-sm text-neutral-600">Remember me</span>
              </label>
            </div>

            <button type="submit" :disabled="form.processing" class="w-full btn btn-primary py-3 text-lg">
              <span v-if="form.processing">Signing in...</span>
              <span v-else>Sign In</span>
            </button>
          </div>
        </form>
      </div>

      <p class="text-center text-primary-100 mt-6 text-sm">
        Government of India - Council of Scientific &amp; Industrial Research
      </p>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>
