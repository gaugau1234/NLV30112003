<script setup>
import './dashboard.css'
import Layout from '@/components/FrontEnd/Layout.vue'
import { useDashboard } from './dashboard.js'

const {
    user,
    tasks,
    comments,
    selectedTask,
    commentInput,
    fetchTasks,
    openTaskDetail,
    completeTask,
    addComment,
    handleAddComment,
    isExpired
} = useDashboard()
</script>

<template>
    <Layout>
        <template #main-content>
            <div class="dashboard-container">
                <div class="dashboard-header">
                    <h2>Dashboard nhóm</h2>
                    <div class="name">Xin chào, {{ user?.name || '' }}</div>
                </div>

                <section class="stats-summary">
                    <div class="stat-card">Tổng công việc: {{ tasks.length }}</div>
                    <div class="stat-card">Đang làm: {{tasks.filter(t => t.status !== 'completed').length}}</div>
                    <div class="stat-card">Đã hoàn thành: {{tasks.filter(t => t.status === 'completed').length}}</div>
                </section>

                <section class="task-list">
                    <h3>Danh sách công việc nhóm</h3>
                    <ul>
                        <li v-for="task in tasks" :key="task.id" :class="{ expired: isExpired(task.due_date) }"
                            @click="openTaskDetail(task)" class="task-item">
                            <strong>{{ task.title }}</strong>
                            <span v-if="isExpired(task.due_date)" class="warning">⚠️ Sắp hết hạn</span>
                            <div class="meta">
                                Trạng thái: <em>{{ task.status === 'completed' ? 'Đã hoàn thành' : 'Đang làm' }}</em> —
                                Người thực hiện: <strong>{{ task.assigned_user_name }}</strong> —
                                Nhóm: <strong>{{ task.group_name }}</strong>
                            </div>
                            <button v-if="user?.id === task.assigned_user_id && task.status !== 'completed'"
                                @click.stop="completeTask(task)">
                                ✅ Hoàn thành
                            </button>
                        </li>
                    </ul>
                </section>

                <section v-if="selectedTask" class="task-detail-modal" style="display: flex; gap: 20px;">
                    <div class="task-detail-info" style="flex: 2; border-right: 1px solid #ccc; padding-right: 20px;">
                        <h4> Chi tiết công việc: {{ selectedTask.title }}</h4>
                        <p><strong>Hạn hoàn thành:</strong> {{ selectedTask.due_date || 'Chưa đặt' }}</p>
                        <p>
                            <strong>Trạng thái:</strong>
                            <span>{{ selectedTask.status === 'completed' ? 'Đã hoàn thành' : 'Đang xử lý' }}</span>
                        </p>
                        <p><strong>Người thực hiện:</strong> {{ selectedTask.assigned_user_name }}</p>
                    </div>
                    <!--    comment -->
                    <div class="comments-section"
                        style="flex: 1; max-height: 300px; overflow-y: auto; padding-left: 20px;">
                        <h5>💬 Bình luận</h5>
                        <ul>
                            <li v-for="c in comments[selectedTask.id] || []" :key="c.id">
                                <strong>{{ c.user_name }}:</strong> {{ c.content }}
                            </li>
                        </ul>
                        <input v-model="commentInput" placeholder="Nhập bình luận..." />
                        <button @click="handleAddComment">Gửi bình luận</button>
                    </div>

                    <button @click="selectedTask = null" style="height: 30px; align-self: flex-start;">Đóng</button>
                </section>
            </div>
        </template>
    </Layout>
</template>
