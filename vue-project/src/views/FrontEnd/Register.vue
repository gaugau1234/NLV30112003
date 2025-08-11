<script setup>
// import { BACKEND_API } from '@/Config/config.js'
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const email = ref('')
const name = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const router = useRouter()

const success = ref('')
const handleRegister = async () => {
    error.value = ''
    success.value = ''
    if (password.value !== confirmPassword.value) {
        error.value = 'Mật khẩu không khớp'
        return
    }
    try {
        await axios.post('http://localhost:8000/api/register', {
            name: name.value,
            email: email.value,
            password: password.value
        })
        success.value = 'Đăng ký thành công!'
        setTimeout(() => {
            router.push('/')
        }, 1500)
    } catch (err) {
        console.log(err.response?.data)
        error.value =
            err.response?.data?.errors?.email?.[0] ||
            err.response?.data?.message ||
            'Đăng ký thất bại'
    }
}
</script>
<template>
    <div>
        <div v-if="success" class="register-success-overlay">
            <div class="register-success-box">
                <span class="register-success-icon">✔</span>
                <div class="register-success-text">{{ success }}</div>
            </div>
        </div>
        <div class="register-container">
            <h2>Đăng ký</h2>
            <form @submit.prevent="handleRegister">
                <div class="form-group">
                    <label for="name">Họ và tên:</label>
                    <input type="text" id="name" v-model="name" placeholder="Nhập Họ và tên" />
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" v-model="email" placeholder="Nhập email" />
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" v-model="password" placeholder="Nhập mật khẩu" />
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Nhập lại mật khẩu</label>
                    <input type="password" id="confirmPassword" v-model="confirmPassword" placeholder="Nhập lại mật khẩu" />
                </div>
                <div v-if="error" class="error-message">{{ error }}</div>
                <button type="submit">Tạo tài khoản</button>
            </form>
            <p>Đã có tài khoản? <router-link to="/">Đăng nhập</router-link></p>
        </div>
    </div>
</template>



<style scoped>
 .register-success-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(44, 183, 202, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
 .register-success-box {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(44,183,202,0.18);
    padding: 48px 36px;
    text-align: center;
    min-width: 320px;
    max-width: 90vw;
    display: flex;
    flex-direction: column;
    align-items: center;
}
 .register-success-icon {
    font-size: 54px;
    color: #2db2ca;
    margin-bottom: 18px;
    font-weight: bold;
}
 .register-success-text {
    font-size: 24px;
    color: #208aa0;
    font-weight: 600;
}
 .success-message {
    color: #2db2ca;
    background: #e6f7fa;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 12px;
    text-align: center;
    font-weight: 500;
}
.register-container {
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
    text-align: center;
    margin-bottom: 24px;
    font-size: 24px;
    color: #333;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
}

input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    transition: border 0.3s;

}

input::placeholder {
    color: #aaa;
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
    color: #28bac0;
    text-decoration: none;
    font-weight: 500;
}

a:hover {
    text-decoration: underline;
}
</style>
