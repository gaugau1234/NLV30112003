// GroupsManager.js
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/Config/axios'

export function GroupsManager() {
  const user = ref(null)
  const groups = ref([])
  const groupMembers = ref({})
  const selectedGroupId = ref(null)
  const showCreateModal = ref(false)
  const showRenameModal = ref(false)
  const newGroupName = ref('')
  const renameGroupName = ref('')
  const inviteEmail = ref('')

  onMounted(async () => {
    const userRes = await apiClient.get('/user')
    user.value = userRes.data
    const groupsRes = await apiClient.get('/groups')
    groups.value = groupsRes.data
  })

  const addGroup = async () => {
    if (!newGroupName.value.trim()) return
    await apiClient.post('/groups', {
      name: newGroupName.value.trim(),
      created_by: user.value?.id
    })
    groups.value = (await apiClient.get('/groups')).data
    showCreateModal.value = false
    newGroupName.value = ''
  }

  const selectGroup = async (id) => {
    selectedGroupId.value = id
    const res = await apiClient.get(`/groups/${id}/members`)
    groupMembers.value[id] = res.data
    const group = groups.value.find(g => g.id === id)
    renameGroupName.value = group?.name || ''
  }

  const clearSelection = () => {
    selectedGroupId.value = null
  }

  const isGroupOwner = computed(() => {
    const group = groups.value.find(g => g.id === selectedGroupId.value)
    return group?.created_by === user.value?.id
  })

  const addMemberById = async () => {
    const userId = prompt('Nhập ID thành viên:')
    if (!userId || !selectedGroupId.value || !isGroupOwner.value) return
    await apiClient.post(`/groups/${selectedGroupId.value}/members`, { user_id: userId })
    const res = await apiClient.get(`/groups/${selectedGroupId.value}/members`)
    groupMembers.value[selectedGroupId.value] = res.data
  }
  
  const addMemberByEmail = async () => {
    if (!inviteEmail.value.trim() || !selectedGroupId.value || !isGroupOwner.value) return
    await apiClient.post(`/groups/${selectedGroupId.value}/add-by-email`, {
      email: inviteEmail.value.trim()
    })
    inviteEmail.value = ''
    const res = await apiClient.get(`/groups/${selectedGroupId.value}/members`)
    groupMembers.value[selectedGroupId.value] = res.data
  }

  const removeMember = async (memberId) => {
    if (!isGroupOwner.value || !selectedGroupId.value || memberId === user.value.id) return
    await apiClient.delete(`/groups/${selectedGroupId.value}/members/${memberId}`)
    const res = await apiClient.get(`/groups/${selectedGroupId.value}/members`)
    groupMembers.value[selectedGroupId.value] = res.data
  }

  const renameGroup = async () => {
    if (!renameGroupName.value.trim()) return
    await apiClient.put(`/groups/${selectedGroupId.value}`, {
      name: renameGroupName.value.trim()
    })
    groups.value = (await apiClient.get('/groups')).data
    showRenameModal.value = false
  }

  const deleteGroup = async () => {
    const confirmed = confirm('Bạn có chắc muốn xoá nhóm này?')
    if (!confirmed || !selectedGroupId.value || !isGroupOwner.value) return
    await apiClient.delete(`/groups/${selectedGroupId.value}`)
    groups.value = (await apiClient.get('/groups')).data
    selectedGroupId.value = null
  }

  return {
    user, groups, groupMembers, selectedGroupId,
    showCreateModal, showRenameModal, newGroupName, renameGroupName, inviteEmail,
    addGroup, selectGroup, clearSelection, isGroupOwner,
    addMemberById, addMemberByEmail, removeMember,
    renameGroup, deleteGroup
  }
}