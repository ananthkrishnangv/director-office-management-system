<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-neutral-800">Tasks</h1>
          <p class="text-neutral-600">{{ stats.pending }} pending, {{ stats.completed }} completed</p>
        </div>
      </div>

      <!-- Add Todo Form -->
      <div class="card">
        <form @submit.prevent="addTodo" class="flex gap-3">
          <input v-model="newTodo.title" type="text" class="input flex-1" placeholder="Add a new task..." required />
          <select v-model="newTodo.priority" class="input w-32">
            <option value="low">Low</option>
            <option value="normal">Normal</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
          <input v-model="newTodo.due_date" type="date" class="input w-40" />
          <button type="submit" class="btn btn-primary">Add</button>
        </form>
      </div>

      <!-- Filters -->
      <div class="flex gap-2">
        <button @click="filter = 'all'" :class="filterBtn('all')">All</button>
        <button @click="filter = 'pending'" :class="filterBtn('pending')">Pending</button>
        <button @click="filter = 'completed'" :class="filterBtn('completed')">Completed</button>
      </div>

      <!-- Todo List -->
      <div class="card space-y-2">
        <div v-if="filteredTodos.length === 0" class="text-center py-8 text-neutral-500">
          No tasks found
        </div>
        <div v-for="todo in filteredTodos" :key="todo.id" 
             class="flex items-center gap-4 p-3 rounded-lg hover:bg-neutral-50 group"
             :class="{ 'opacity-60': todo.is_completed }">
          <input type="checkbox" :checked="todo.is_completed" @change="toggle(todo)" 
                 class="w-5 h-5 rounded border-neutral-300 text-primary-500" />
          <div class="flex-1">
            <div :class="{ 'line-through': todo.is_completed }" class="font-medium text-neutral-800">
              {{ todo.title }}
            </div>
            <div v-if="todo.due_date" class="text-sm text-neutral-500">
              Due: {{ formatDate(todo.due_date) }}
            </div>
          </div>
          <span :class="priorityClass(todo.priority)" class="px-2 py-1 rounded text-xs">
            {{ todo.priority }}
          </span>
          <button @click="deleteTodo(todo)" class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700">
            ✕
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  todos: Array,
  stats: Object,
});

const filter = ref('all');
const newTodo = ref({ title: '', priority: 'normal', due_date: '' });

const filteredTodos = computed(() => {
  if (filter.value === 'pending') return props.todos.filter(t => !t.is_completed);
  if (filter.value === 'completed') return props.todos.filter(t => t.is_completed);
  return props.todos;
});

const filterBtn = (f) => `btn ${filter.value === f ? 'btn-primary' : 'btn-secondary'}`;

const priorityClass = (p) => ({
  urgent: 'bg-red-100 text-red-700',
  high: 'bg-orange-100 text-orange-700',
  normal: 'bg-neutral-100 text-neutral-700',
  low: 'bg-gray-100 text-gray-500',
}[p]);

const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });

const addTodo = async () => {
  await fetch('/api/todos', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(newTodo.value),
  });
  newTodo.value = { title: '', priority: 'normal', due_date: '' };
  location.reload();
};

const toggle = async (todo) => {
  await fetch(`/api/todos/${todo.id}/toggle`, { method: 'POST' });
  todo.is_completed = !todo.is_completed;
};

const deleteTodo = async (todo) => {
  if (confirm('Delete this task?')) {
    await fetch(`/api/todos/${todo.id}`, { method: 'DELETE' });
    location.reload();
  }
};
</script>
