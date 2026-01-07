<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-neutral-800">Tasks</h1>
          <p class="text-neutral-500 text-sm mt-1">Manage your daily tasks and reminders</p>
        </div>
      </div>

      <!-- Add Task Form -->
      <div class="fluent-card p-6">
        <h3 class="font-semibold text-neutral-800 mb-4">Add New Task</h3>
        <form @submit.prevent="addTask" class="flex flex-col md:flex-row gap-4">
          <input 
            type="text" 
            v-model="newTask.title" 
            placeholder="What needs to be done?" 
            class="fluent-input flex-1"
            required
          />
          <select v-model="newTask.priority" class="fluent-select w-auto min-w-[120px]">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
          <input 
            type="date" 
            v-model="newTask.due_date" 
            class="fluent-input w-auto"
          />
          <button type="submit" class="btn btn-primary" :disabled="!newTask.title">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Task
          </button>
        </form>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="fluent-card p-4 border-l-4 border-l-blue-500">
          <p class="text-xs text-neutral-500 font-medium uppercase tracking-wide">Total</p>
          <p class="text-2xl font-bold text-neutral-800 mt-1">{{ todos.length }}</p>
        </div>
        <div class="fluent-card p-4 border-l-4 border-l-green-500">
          <p class="text-xs text-neutral-500 font-medium uppercase tracking-wide">Completed</p>
          <p class="text-2xl font-bold text-neutral-800 mt-1">{{ completedCount }}</p>
        </div>
        <div class="fluent-card p-4 border-l-4 border-l-amber-500">
          <p class="text-xs text-neutral-500 font-medium uppercase tracking-wide">Pending</p>
          <p class="text-2xl font-bold text-neutral-800 mt-1">{{ pendingCount }}</p>
        </div>
        <div class="fluent-card p-4 border-l-4 border-l-red-500">
          <p class="text-xs text-neutral-500 font-medium uppercase tracking-wide">High Priority</p>
          <p class="text-2xl font-bold text-neutral-800 mt-1">{{ highPriorityCount }}</p>
        </div>
      </div>

      <!-- Task List -->
      <div class="fluent-card">
        <div v-if="todos.length > 0" class="divide-y divide-neutral-100">
          <div 
            v-for="todo in todos" 
            :key="todo.id" 
            class="flex items-center gap-4 p-4 hover:bg-neutral-50 transition-colors"
            :class="{ 'opacity-50': todo.is_completed }"
          >
            <button 
              @click="toggleComplete(todo)" 
              class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors flex-shrink-0"
              :class="todo.is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-neutral-300 hover:border-green-500'"
            >
              <svg v-if="todo.is_completed" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </button>
            
            <div class="flex-1 min-w-0">
              <p 
                class="font-medium text-neutral-800" 
                :class="{ 'line-through text-neutral-400': todo.is_completed }"
              >
                {{ todo.title }}
              </p>
              <div class="flex items-center gap-3 mt-1 text-xs text-neutral-500">
                <span v-if="todo.due_date" class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  {{ formatDate(todo.due_date) }}
                </span>
              </div>
            </div>
            
            <span 
              class="px-2.5 py-1 rounded-full text-xs font-bold"
              :class="getPriorityClass(todo.priority)"
            >
              {{ todo.priority }}
            </span>
            
            <button @click="deleteTask(todo.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>
        
        <div v-else class="empty-state">
          <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
          <p class="font-medium text-neutral-600">No tasks yet</p>
          <p class="text-sm">Add a task above to get started</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Components/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  todos: { type: Array, default: () => [] },
});

const newTask = ref({
  title: '',
  priority: 'medium',
  due_date: ''
});

const completedCount = computed(() => props.todos.filter(t => t.is_completed).length);
const pendingCount = computed(() => props.todos.filter(t => !t.is_completed).length);
const highPriorityCount = computed(() => props.todos.filter(t => t.priority === 'high' && !t.is_completed).length);

const formatDate = (dt) => {
  if (!dt) return '';
  return new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const getPriorityClass = (priority) => ({
  high: 'bg-red-100 text-red-700',
  medium: 'bg-amber-100 text-amber-700',
  low: 'bg-green-100 text-green-700'
}[priority] || 'bg-gray-100 text-gray-600');

const addTask = () => {
  if (!newTask.value.title) return;
  router.post('/todos', newTask.value, {
    onSuccess: () => {
      newTask.value = { title: '', priority: 'medium', due_date: '' };
    }
  });
};

const toggleComplete = (todo) => {
  router.put(`/todos/${todo.id}`, { is_completed: !todo.is_completed });
};

const deleteTask = (id) => {
  if (confirm('Delete this task?')) {
    router.delete(`/todos/${id}`);
  }
};
</script>
