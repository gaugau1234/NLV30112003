<script setup>
import Layout from '@/components/FrontEnd/Layout.vue';
import './SettingAccount.css';
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/Config/axios'

const user = ref(null)
const router = useRouter()
const loading = ref(false)
const message = ref({ type: '', text: '' })

// Thông tin cá nhân
const userInfo = ref({
    name: '',
    email: '',
    avatar: '',
    notifications: true
})

// Đổi mật khẩu
const changePassword = ref({
    current: '',
    newPass: '',
    confirm: ''
})

// Lấy thông tin người dùng
onMounted(async () => {
    try {
        const userRes = await apiClient.get('/user')
        user.value = userRes.data
        userInfo.value.name = user.value.name
        userInfo.value.email = user.value.email
    } catch (error) {
        console.error('Lỗi khi lấy thông tin người dùng:', error)
    }
})

// Cập nhật thông tin cá nhân
const saveProfile = async () => {
    if (!userInfo.value.name.trim() || !userInfo.value.email.trim()) {
        message.value = { type: 'error', text: 'Vui lòng điền đầy đủ thông tin' }
        return
    }

    loading.value = true
    message.value = { type: '', text: '' }

    try {
        const response = await apiClient.put('/user/profile', {
            name: userInfo.value.name,
            email: userInfo.value.email
        })

        user.value = response.data.user
        message.value = { type: 'success', text: 'Cập nhật thông tin thành công!' }

        // Cập nhật lại thông tin hiển thị
        userInfo.value.name = user.value.name
        userInfo.value.email = user.value.email

        // Hiển thị thông báo đổi thành công
        alert('Đã đổi tên và email thành công!')

    } catch (error) {
        message.value = {
            type: 'error',
            text: error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật thông tin'
        }
    } finally {
        loading.value = false
    }
}

// Đổi mật khẩu
const updatePassword = async () => {
    if (!changePassword.value.current || !changePassword.value.newPass || !changePassword.value.confirm) {
        message.value = { type: 'error', text: 'Vui lòng điền đầy đủ thông tin mật khẩu' }
        return
    }

    if (changePassword.value.newPass !== changePassword.value.confirm) {
        message.value = { type: 'error', text: 'Mật khẩu mới không khớp' }
        return
    }

    if (changePassword.value.newPass.length < 6) {
        message.value = { type: 'error', text: 'Mật khẩu mới phải có ít nhất 6 ký tự' }
        return
    }

    loading.value = true
    message.value = { type: '', text: '' }

    try {
        await apiClient.put('/user/password', {
            current_password: changePassword.value.current,
            new_password: changePassword.value.newPass,
            new_password_confirmation: changePassword.value.confirm
        })

        message.value = { type: 'success', text: 'Đổi mật khẩu thành công!' }

        // Reset form
        changePassword.value = {
            current: '',
            newPass: '',
            confirm: ''
        }

        // Hiển thị thông báo đổi mật khẩu thành công
        alert('Đã đổi mật khẩu thành công!')

    } catch (error) {
        message.value = {
            type: 'error',
            text: error.response?.data?.message || 'Có lỗi xảy ra khi đổi mật khẩu'
        }
    } finally {
        loading.value = false
    }
}

// Đăng xuất
const handleLogout = async () => {
    try {
        await apiClient.post('/logout')
    } catch (error) {
        console.warn('Không thể gọi logout backend:', error)
    }
    localStorage.removeItem('token')
    user.value = null
    router.push('/')
}



</script>
<template>
    <layout>
        <template #main-content>
            <div class="account-settings">
                <h2> Cài đặt tài khoản</h2>

                <section class="profile-section">
                    <h3> Thông tin cá nhân</h3>
                    <input v-model="userInfo.name" placeholder="Họ và tên" />
                    <input v-model="userInfo.email" placeholder="Email" />
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="userInfo.notifications" />
                        Nhận thông báo qua email
                    </label>

                    <div class="button-save" style="margin-top: 12px;">
                        <button class="button-setting" @click="saveProfile">Lưu thay đổi</button>
                    </div>
                </section>

                <section class="password-section">
                    <h3> Đổi mật khẩu</h3>
                    <input v-model="changePassword.current" placeholder="Mật khẩu hiện tại" type="password" />
                    <input v-model="changePassword.newPass" placeholder="Mật khẩu mới" type="password" />
                    <input v-model="changePassword.confirm" placeholder="Nhập lại mật khẩu mới" type="password" />
                    <button class="button-setting" @click="updatePassword">Đổi mật khẩu</button>
                </section>
                <button class="logout" @click="handleLogout">Đăng xuất</button>
            </div>

        </template>
    </layout>
</template>
