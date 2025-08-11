<script setup>
import Layout from '@/components/FrontEnd/Layout.vue'
import { ref, onMounted } from 'vue'
import apiClient from '@/Config/axios'
import './Report.css'

const performance = ref([])

const groupIdsInput = ref('') // comma separated group IDs

const exportTaskReport = async () => {
    try {
        let groupIds = []
        if (groupIdsInput.value.trim() !== '') {
            groupIds = groupIdsInput.value.split(',').map(id => id.trim())
        } else {
            alert('Vui lòng nhập ID nhóm để xuất báo cáo')
            return
        }
        // Assuming export by group IDs is supported by backend, else keep old logic
        const response = await apiClient.get('/reports/tasks/export', {
            responseType: 'blob',
            params: { group_ids: groupIds }
        })
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'tasks_report.csv')
        document.body.appendChild(link)
        link.click()
        link.remove()
    } catch (err) {
        alert('Lỗi khi xuất báo cáo')
    }
}

const fetchGroupPerformance = async () => {
    try {
        let groupIds = []
        if (groupIdsInput.value.trim() !== '') {
            groupIds = groupIdsInput.value.split(',').map(id => id.trim())
        } else {
            alert('Vui lòng nhập ID nhóm để lấy đánh giá hiệu suất nhóm')
            return
        }
        const res = await apiClient.post('/reports/groups/performance-by-id', {
            group_ids: groupIds
        })
        performance.value = res.data
    } catch (err) {
        alert('Lỗi khi lấy đánh giá hiệu suất nhóm')
    }
}

onMounted(() => {
    // Do not fetch automatically without group_ids input
    // fetchGroupPerformance()
})
</script>

<template>
    <Layout>
        <template #main-content>
            <div class="report-page">
                <h2>Xuất báo cáo công việc & </h2>

                <h2> Đánh giá hiệu suất nhóm</h2>

                <div>
                    <label for="groupIdsInput">Nhập group_ids (phân cách bằng dấu phẩy):</label>
                    <input id="groupIdsInput" v-model="groupIdsInput" placeholder="vd: 1,2,3" />
                    <button @click="fetchGroupPerformance">Lấy đánh giá hiệu suất nhóm</button>
                </div>

                <table v-if="performance.length > 0">
                    <thead>
                        <tr>
                            <th>Group name</th>
                            <th>Tổng công việc</th>
                            <th>Công việc hoàn thành</th>
                            <th>Tỷ lệ hoàn thành (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="perf in performance" :key="perf.group_id">
                            <td>{{ perf.group_name }}</td>
                            <td>{{ perf.total_tasks }}</td>
                            <td>{{ perf.completed_tasks }}</td>
                            <td>{{ perf.completion_rate }}</td>
                        </tr>
                    </tbody>
                </table>
                <button @click="exportTaskReport">Xuất file </button>
            </div>
        </template>
    </Layout>
</template>
