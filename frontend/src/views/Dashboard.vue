<template>
  <div class="p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>
    <div class="bg-white rounded-lg shadow p-6">
      <p>Hoş geldiniz! Dashboard sayfası buraya gelecek.</p>
      <button @click="logout" class="mt-4 bg-red-600 text-white px-4 py-2 rounded">
        Çıkış Yap
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const logout = () => {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>