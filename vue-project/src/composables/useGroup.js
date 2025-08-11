import { ref } from 'vue'
import apiClient from '@/Config/axios'

const groups = ref([])
const groupMembers = ref({})
let groupsLoaded = false

async function loadGroups() {
  if (groupsLoaded) {
    return { groups: groups.value, groupMembers: groupMembers.value }
  }
  try {
    const groupsRes = await apiClient.get('/groups')
    groups.value = groupsRes.data

    for (const group of groups.value) {
      try {
        const res = await apiClient.get('/groups/' + group.id + '/members')
        groupMembers.value[group.id] = res.data
      } catch (error) {
        console.error('Error fetching members for group ' + group.id + ':', error)
      }
    }
    groupsLoaded = true
    return { groups: groups.value, groupMembers: groupMembers.value }
  } catch (error) {
    console.error('Error loading groups:', error)
    return { groups: [], groupMembers: {} }
  }
}

export function useGroup() {
  return {
    groups,
    groupMembers,
    loadGroups
  }
}
