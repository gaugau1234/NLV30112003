<script setup>
import Layout from '@/components/FrontEnd/Layout.vue';
import './Notifications.css';
import { ref, onMounted } from 'vue'
import apiClient from '@/Config/axios'

const notifications = ref([])

const fetchNotifications = async () => {
  try {
    const res = await apiClient.get('/notifications')
    notifications.value = res.data
  } catch (err) {
    notifications.value = []
  }
}

const removeNotification = (id) => {
  notifications.value = notifications.value.filter(n => n.id !== id)
}

onMounted(() => {
  fetchNotifications()
})

</script>
<template>
    <layout>
        <template #main-content>
            <div class="notifications-page">
                <h2> Thông Báo Mới</h2>
                <ul>
                    <li v-for="item in notifications" :key="item.id">
                        <div class="notice" :class="{ 'expiring-task': item.is_task_expiring }">
                            <h3 v-if="item.is_task_expiring">Công việc sắp hết hạn</h3>
                            <h3 v-else>Thông báo</h3>
                            <p>{{ item.message }}</p>
                            <small>{{ new Date(item.created_at).toLocaleString() }}</small>
                            <button @click="removeNotification(item.id)"> Xóa</button>
                        </div>
                    </li>
                </ul>
            </div>

        </template>
    </layout>
</template>
