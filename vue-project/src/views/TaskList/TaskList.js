import { ref, onMounted, watch } from 'vue'
import apiClient from '@/Config/axios'
import { useGroup } from '@/composables/useGroup'

export function useTaskList() {
    const tasks = ref([])
    const { groups, groupMembers, loadGroups } = useGroup()
    const selectedGroupId = ref(null)
    const selectedMemberId = ref(null)
    const newTaskTitle = ref('')
    const showAddForm = ref(false)
    const user = ref(null)

    // Fetch user info and groups on mount
    const fetchUserAndGroups = async () => {
        try {
            const userRes = await apiClient.get('/user')
            user.value = userRes.data

            await loadGroups()

            // Fetch group members for selectedGroupId to get roles
            if (selectedGroupId.value) {
                const res = await apiClient.get(`/groups/${selectedGroupId.value}/members`)
                groupMembers.value[selectedGroupId.value] = res.data
            }

            await fetchTaskList()
        } catch (error) {
            console.error('Error fetching user or groups:', error)
        }
    }

    // Fetch task list filtered by selectedGroupId or user's group
    const fetchTaskList = async () => {
        try {
            let params = {}
            if (selectedGroupId.value) {
                params.group_id = selectedGroupId.value
            }
            // If no selectedGroupId, do not send group_id param to fetch all tasks assigned to user
            const res = await apiClient.get('/tasks', { params })
            // Map assigned_to to user name from groupMembers
            tasks.value = res.data.map(task => {
                const members = groupMembers.value[task.group_id] || []
                const assignedUser = members.find(m => m.id === task.assigned_to)
                return {
                    ...task,
                    assigned_user_name: assignedUser ? assignedUser.name : 'Không rõ',
                    deadline: task.due_date || null
                }
            })
        } catch (error) {
            console.error('Error fetching tasks:', error)
            tasks.value = []
        }
    }

    // Fetch group members when selectedGroupId changes
    watch(selectedGroupId, async (groupId) => {
        if (!groupId) return
        try {
            if (!groupMembers.value[groupId]) {
                const res = await apiClient.get(`/groups/${groupId}/members`)
                groupMembers.value[groupId] = res.data
            }
            // Reset selected member when group changes
            if (groupMembers.value[groupId] && groupMembers.value[groupId].length > 0) {
                selectedMemberId.value = groupMembers.value[groupId][0].id
            } else {
                selectedMemberId.value = null
            }
            // Refresh task list for new group
            await fetchTaskList()
        } catch (error) {
            console.error('Error fetching group members:', error)
        }
    })

    onMounted(fetchUserAndGroups)

    // Selected task detail
    const selectedTask = ref(null)

    // Fetch detailed info for selected task
    const fetchTaskDetail = async (taskId) => {
        if (!taskId) {
            selectedTask.value = null
            return
        }
        try {
            const res = await apiClient.get(`/tasks/${taskId}`)
            selectedTask.value = {
                ...res.data,
                deadline: res.data.due_date || null
            }
        } catch (error) {
            alert('Không thể tải chi tiết công việc.')
            selectedTask.value = null
        }
    }

    // Set selected task and fetch its detail
    const setSelectedTask = async (task) => {
        if (!task || !task.id) {
            selectedTask.value = null
            return
        }
        // Only allow selecting tasks assigned to the current user
        if (task.assigned_to !== user.value?.id) {
            alert('Bạn chỉ có thể xem chi tiết công việc được giao cho bạn.')
            return
        }
        await fetchTaskDetail(task.id)
    }

    // Toggle task status between completed and pending
    const toggleTask = async (task) => {
        const newStatus = task.status === 'completed' ? 'pending' : 'completed'
        try {
            await apiClient.put(`/tasks/${task.id}`, { status: newStatus })
            await fetchTaskList()
            if (selectedTask.value && selectedTask.value.id === task.id) {
                await fetchTaskDetail(task.id)
            }
        } catch (error) {
            console.error('Error toggling task status:', error)
        }
    }

    // Remove a task
    const removeTask = async (id) => {
        try {
            await apiClient.delete(`/tasks/${id}`)
            await fetchTaskList()
            if (selectedTask.value && selectedTask.value.id === id) {
                selectedTask.value = null
            }
        } catch (error) {
            console.error('Error removing task:', error)
        }
    }

    // File upload state
    const uploadedFile = ref(null)
    const setUploadedFile = (file) => {
        uploadedFile.value = file
    }

    // Submit file for a task
    const submitFile = async (task) => {
        if (!uploadedFile.value || !task) return
        if (user.value?.id !== task.assigned_to) {
            alert('Bạn không có quyền nộp file cho công việc này.')
            return
        }
        const formData = new FormData()
        formData.append('file', uploadedFile.value)
        try {
            await apiClient.post(`/tasks/${task.id}/submit-file`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            uploadedFile.value = null
            await fetchTaskDetail(task.id)
            await fetchTaskList()
        } catch (error) {
            console.error('Error submitting file:', error)
        }
    }

    // Mark task as completed and submit file if uploaded
    const completeTask = async (task) => {
        if (!task) return
        if (user.value?.id !== task.assigned_to) {
            alert('Bạn không có quyền hoàn thành công việc này.')
            return
        }
        try {
            if (uploadedFile.value) {
                const formData = new FormData()
                formData.append('file', uploadedFile.value)
                await apiClient.post(`/tasks/${task.id}/submit-file`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                uploadedFile.value = null
            }
            await apiClient.put(`/tasks/${task.id}`, { status: 'completed' })
            await apiClient.post('/notifications', {
                user_id: task.created_by,
                message: `Công việc "${task.title}" đã được hoàn thành bởi ${user.value?.name}.`
            })
            await fetchTaskDetail(task.id)
            await fetchTaskList()
            alert('Hoàn thành công việc thành công.')
        } catch (error) {
            console.error('Error completing task:', error)
            alert('Có lỗi xảy ra khi hoàn thành công việc.')
        }
    }

    // New task deadline state
    const newTaskDeadline = ref('')
    const setNewTaskDeadline = (val) => {
        newTaskDeadline.value = val
    }

    // Add new task (keep existing logic)
    const addTask = async () => {
        if (!newTaskTitle.value.trim() || !selectedGroupId.value || !selectedMemberId.value) return
        try {
            await apiClient.post('/tasks', {
                title: newTaskTitle.value,
                status: 'pending',
                group_id: selectedGroupId.value,
                assigned_to: selectedMemberId.value,
                created_by: user.value?.id,
                created_at: new Date().toISOString(),
                due_date: newTaskDeadline.value // đổi tên trường thành due_date
            })
            newTaskTitle.value = ''
            selectedGroupId.value = null
            selectedMemberId.value = null
            newTaskDeadline.value = ''
            showAddForm.value = false
            await fetchTaskList()
        } catch (error) {
            console.error('Error adding task:', error)
        }
    }

    // Close task detail modal
    const closeTaskDetail = () => {
        selectedTask.value = null
        uploadedFile.value = null
    }

    return {
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
    }
}
