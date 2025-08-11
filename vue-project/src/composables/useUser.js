import { ref } from 'vue'
import apiClient from '@/Config/axios'

const user = ref(null)

async function loadUser(forceReload = false) {
  if (user.value && !forceReload) {
    // Nếu đã có user rồi thì không gọi lại API
    return user.value
  }
  try {
    const response = await apiClient.get('/user')
    user.value = response.data
    return user.value
  } catch (err) {
    console.error('Không lấy được thông tin user:', err.response?.data)
    return null
  }
}

function clearUser() {
  user.value = null
}

export function useUser() {
  return {
    user,
    loadUser,
    clearUser
  }
}
