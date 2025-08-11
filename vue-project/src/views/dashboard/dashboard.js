import { ref, onMounted } from 'vue'
import apiClient from '@/Config/axios'

export function useDashboard() {
    const user = ref(null)
    const tasks = ref([])
    const comments = ref({})
    const commentInput = ref('')
    const selectedTask = ref(null)
    const notifications = ref([])

    const fetchTasks = async () => {
        if (!user.value?.group_ids || user.value.group_ids.length === 0) {
            tasks.value = []
            console.warn('User chưa có group_ids hoặc chưa thuộc nhóm nào.')
            return
        }

        try {
            const res = await apiClient.post(`/groups/tasks/all`, {
                group_ids: user.value.group_ids
            })

            console.log('Dữ liệu tasks nhận được từ API:', res.data);
            tasks.value = res.data.map(task => ({
                ...task,
                assigned_user_name: task.assignee?.name || 'Không rõ',
                assigned_user_id: task.assignee?.id || null,
                group_name: task.group?.name || 'Không rõ nhóm'
            }))
        } catch (err) {
            console.error('Lỗi khi lấy công việc nhóm:', err)
            tasks.value = []
        }
    }

    const fetchNotifications = async () => {
        try {
            const res = await apiClient.get('/notifications')
            notifications.value = res.data
        } catch (err) {
            notifications.value = []
        }
    }

    const completeTask = async (task) => {
        if (!task) return
        try {
            await apiClient.put(`/tasks/${task.id}`, { status: 'completed' })
            await fetchTasks()
        } catch (err) {
            alert('Lỗi cập nhật: ' + (err.response?.data?.message || 'Lỗi hệ thống'))
        }
    }

    const isExpired = (date) => {
        if (!date) return false
        const now = new Date()
        const deadline = new Date(date)
        return (deadline - now) / (1000 * 60 * 60 * 24) <= 2 && deadline > now
    }

    const openTaskDetail = async (task) => {
        selectedTask.value = task
        commentInput.value = ''
        await fetchComments(task.id)
    }

    const fetchComments = async (taskId) => {
        try {
            const res = await apiClient.get(`/tasks/${taskId}/comments`)
            comments.value[taskId] = res.data
        } catch (err) {
            comments.value[taskId] = []
        }
    }

    const addComment = async (taskId, content) => {
        if (!content.trim()) return
        try {
            await apiClient.post(`/tasks/${taskId}/comments`, {
                content,
                user_id: user.value?.id
            })
            // Cập nhật danh sách bình luận ngay lập tức
            if (!comments.value[taskId]) {
                comments.value[taskId] = []
            }
            comments.value[taskId].push({
                id: Date.now(), // tạm thời tạo id giả
                content,
                user_name: user.value?.name || 'Bạn',
            })
            commentInput.value = ''
            // Cập nhật lại danh sách công việc
            await fetchTasks()
            await fetchNotifications()
            // Có thể gọi fetchComments nếu muốn đồng bộ lại từ server
            // await fetchComments(taskId)
        } catch (err) {
            alert('Lỗi gửi bình luận')
        }
    }

    const openCreateGroup = () => window.location.href = '/groups'
    const openTaskForm = () => alert('Chức năng giao việc chưa được triển khai!')

    onMounted(async () => {
        try {
            const response = await apiClient.get('/user')
            user.value = response.data
            console.log('User hiện tại:', user.value)
            console.log('Group ID của user:', user.value?.group_id)
            await fetchTasks()
            await fetchNotifications()
        } catch (err) {
            user.value = null
            tasks.value = []
            notifications.value = []
        }
    })

    return {
        user,
        tasks,
        fetchTasks,
        fetchNotifications,
        notifications,
        completeTask,
        comments,
        commentInput,
        selectedTask,
        openTaskDetail,
        addComment,
        handleAddComment: () => addComment(selectedTask.value?.id, commentInput.value),
        isExpired,
        openCreateGroup,
        openTaskForm
    }
}
