<script setup>
import Layout from '@/components/FrontEnd/Layout.vue'
import './TaskList.css'
import { useTaskList } from './TaskList.js'
import { ref } from 'vue'

const {
    tasks,
    groups,
    groupMembers,
    selectedGroupId,
    selectedMemberId,
    newTaskTitle,
    showAddForm,
    user,
    fetchTaskList,
    toggleTask,
    removeTask,
    addTask,
    selectedTask,
    setSelectedTask,
    newTaskDeadline,
    setNewTaskDeadline,
    submitFile,
    completeTask,
    uploadedFile,
    setUploadedFile,
    closeTaskDetail
} = useTaskList()

const fileInput = ref(null)
const isDragOver = ref(false)

function isExpired(task) {
    if (!task?.deadline) return false
    const deadlineDate = new Date(task.deadline)
    const now = new Date()
    return deadlineDate < now
}

function handleFileDrop(event) {
    const files = event.dataTransfer.files
    if (files.length > 0) {
        setUploadedFile(files[0])
    }
    isDragOver.value = false
}

function handleDragEnter() {
    isDragOver.value = true
}

function handleDragLeave() {
    isDragOver.value = false
}
</script>

<template>
    <Layout>
        <template #main-content>
            <div class="task-list">
                <h2>Danh sách công việc</h2>

                <!-- Nút thêm -->
                <div class="showaddform" style="text-align: right;">
                    <button @click="showAddForm = !showAddForm">
                        {{ showAddForm ? 'Đóng' : 'Thêm công việc' }}
                    </button>
                </div>

                <!-- Form thêm công việc -->
                <div v-if="showAddForm" class="task-form-card" @click.stop>
                    <button class="close-form-btn close" @click="showAddForm = false">×</button>
                    <input v-model="newTaskTitle" placeholder="Tên công việc..." />
                    <!-- Chọn nhóm -->
                    <label for="nhom"> Chọn nhóm</label>
                    <select v-model="selectedGroupId">

                        <option name="nhom" disabled value="">Chọn nhóm</option>
                        <option v-for="group in groups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                    <!-- Chọn thành viên -->
                    <select v-if="selectedGroupId && groupMembers[selectedGroupId]" v-model="selectedMemberId">
                        <option disabled value="">Giao cho</option>
                        <option v-for="member in groupMembers[selectedGroupId]" :key="member.id" :value="member.id">
                            {{ member.name }} ({{ member.email }})
                        </option>
                    </select>
                    <!-- Thời hạn -->
                    <input type="date" v-model="newTaskDeadline" />
                    <button class="addtask" @click="addTask">Giao việc</button>
                </div>
                <transition name="fade">
                    <div v-if="showAddForm" class="modal-backdrop" @click="showAddForm = false"></div>
                </transition>





                <!-- Danh sách công việc -->
                <ul class="task-list-ul">
                    <li v-for="task in tasks" :key="task.id" class="task-item" @click="setSelectedTask(task)">
                        <div class="task-content">
                            <div class="task-title">{{ task.title }}</div>
                            <div class="task-deadline" v-if="task.deadline">
                                Hạn: {{ new Date(task.deadline).toLocaleDateString() }}
                            </div>
                            <div class="task-deadline" v-else>
                                Không có hạn
                            </div>
                        </div>
                        <button
                            v-if="groupMembers[selectedGroupId]?.find(m => m.id === user.id)?.role === 'admin' && selectedTask?.assigned_to !== user.id"
                            class="remove-btn" @click.stop="removeTask(task.id)"></button>
                    </li>
                </ul>





                <!-- Chi tiết công việc -->
                <div v-if="selectedTask" class="task-detail-overlay" @click.self="closeTaskDetail">
                    <div class="task-detail-card">
                        <h3>{{ selectedTask.title }}</h3>
                        <p><strong>Thời gian giao việc:</strong> {{ selectedTask.created_at ? new
                            Date(selectedTask.created_at).toLocaleString() : 'Không rõ' }}</p>
                        <p><strong>Thời hạn:</strong> {{ selectedTask.deadline ? new
                            Date(selectedTask.deadline).toLocaleDateString() : 'Chưa đặt' }}</p>
                        <p><strong>Người được giao:</strong>
                            {{groupMembers[selectedTask.group_id]?.find(m => m.id === selectedTask.assigned_to)?.name
                                || 'Không rõ'}}
                        </p>

                        <p><strong>Trạng thái:</strong>
                            <span v-if="selectedTask.status === 'completed'" class="status-completed">Đã hoàn
                                thành</span>
                            <span v-else-if="isExpired(selectedTask)" class="status-expired">Đã hết hạn</span>
                            <span v-else class="status-pending">Đang xử lý</span>
                        </p>

                        <div class="task-actions"
                            v-if="selectedTask.status !== 'completed' && !isExpired(selectedTask) && selectedTask.assigned_to === user.id">
                            <div class="file-drop-area" :class="{ 'drag-over': isDragOver }"
                                @drop.prevent="handleFileDrop" @dragover.prevent @dragenter.prevent="handleDragEnter"
                                @dragleave.prevent="handleDragLeave" @click="fileInput.click()">
                                <p v-if="!uploadedFile">Kéo thả file vào đây hoặc click để chọn file</p>
                                <p v-else>Đã chọn file: {{ uploadedFile.name }}</p>
                                <input type="file" style="display:none" ref="fileInput"
                                    @change="e => setUploadedFile(e.target.files[0])" />
                            </div>
                            <button @click="completeTask(selectedTask)">Hoàn thành</button>
                        </div>

                        <button class="close-btn" @click="closeTaskDetail">Đóng</button>
                    </div>
                </div>
            </div>
        </template>
    </Layout>
</template>
