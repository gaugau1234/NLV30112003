<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
// import { BACKEND_API } from '@/Config/config.js'

const email = ref('')
const password = ref('')
const error = ref('')
const router = useRouter()

const handleLogin = async () => {
    error.value = ''
    try {
        const response = await axios.post('http://localhost:8000/api/login', {
            email: email.value,
            password: password.value
        })
        localStorage.setItem('token', response.data.access_token)
        // Lưu thông tin user vào localStorage
        if (response.data.user) {
            localStorage.setItem('user', JSON.stringify(response.data.user))
        }
        router.push('/dashboard')
    } catch (err) {
        console.log(err.response?.data)
        error.value = err.response?.data?.message || 'Đăng nhập thất bại'
    }
}
</script>
<template>
    <div class="login-container">
        <h2>QUẢN LÝ CÔNG VIỆC NHÓM</h2>
        <h2>Đăng nhập</h2>

        <form @submit.prevent="handleLogin">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" v-model="email" placeholder="Nhập email" />
            </div>
            <div class="form-group">
                <div class="password-label-row">
                    <label for="password">Mật khẩu</label>
                    <router-link class="forgot-link" to="">Quên Mật Khẩu</router-link>
                </div>
                <input type="password" id="password" v-model="password" placeholder="Nhập mật khẩu" />
            </div>
            <div v-if="error" class="error-message">{{ error }}</div>
            <button type="submit">Đăng nhập</button>
        </form>
        <p>Bạn chưa có tài khoản? <router-link to="/register">Đăng ký</router-link></p>
    </div>
</template>


<style scoped>
.login-container {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    padding: 32px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f9f9f9, #eef1f5);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

h2 {
    font-size: 28px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 16px;
    color: #0077cc;
    text-transform: uppercase;
    letter-spacing: 1px;
    line-height: 1.4;
    background: linear-gradient(to right, #0077cc, #00bcd4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}


.form-group {
    margin-bottom: 18px;
}

.password-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.forgot-link {
    white-space: nowrap;
    font-size: 14px;
    color: #283ac0;
    margin-left: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.forgot-link:hover {
    text-decoration: underline;
    color: #195191;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #555;
}

input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    transition: border 0.3s;
}

input:focus {
    border-color: #195191;
    outline: none;
}

button {
    width: 100%;
    padding: 12px;
    background: #0099ff;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background: #bdeaf0;
    color: #208aa0;
}

p {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
    color: #555;
}

a {
    color: #283ac0;
    text-decoration: none;
    font-weight: 500;
}

a:hover {
    text-decoration: underline;
}
</style>
