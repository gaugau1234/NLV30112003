<script setup>
import { onMounted, watch } from 'vue'
import { useUser } from '@/composables/useUser'
import './Sidebar.css';

const { user, loadUser } = useUser()

onMounted(async () => {
    await loadUser()
})

// Watch user changes and reload user data to update sidebar
watch(user, async (newUser, oldUser) => {
    if (newUser?.id !== oldUser?.id) {
        await loadUser(true)
    }
})


</script>
<template>
    <aside id="sidebar" class="app-sidebar">
        <div class="aside-head">
            <div class="head-sidebar">
                <span class="image img-cover profile-image">
                    <img src="@/assets/logo/logo.jpg" alt="Logo" />
                </span>
                <div class="name">{{ user?.name }}</div>

                <div class="role">ID: {{ user?.id }}</div>
            </div>
            <nav class="aside-body">
                <div class="sidebar-menu">
                    <router-link to="/dashboard" class="sidebar-link" active-class="active">
                        <i class='bx bx-home'></i>
                        <span class="nav-label">Dashboard</span>
                        <i class='bx bx-chevron-right'></i>
                    </router-link>
                    <router-link to="/groups" class="sidebar-link" active-class="active">
                        <i class='bx bx-group'></i>
                        <span class="nav-label">Quản lý nhóm</span>
                        <i class='bx bx-chevron-right'></i>
                    </router-link>
                    <router-link to="/tasklist" class="sidebar-link" active-class="active">
                        <i class='bx bx-task'></i>
                        <span class="nav-label">Quản lý công việc</span>
                        <i class='bx bx-chevron-right'></i>
                    </router-link>
                    <router-link to="/notifications" class="sidebar-link" active-class="active">
                        <i class='bx bx-bell'></i>
                        <span class="nav-label">Thông báo</span>
                        <i class='bx bx-chevron-right'></i>
                    </router-link>
                    <router-link to="/report" class="sidebar-link" active-class="active">
                        <i class='bx bx-cog'></i>
                        <span class="nav-label">Báo Cáo</span>
                        <i class='bx bx-chevron-right'></i>
                    </router-link>
                    <router-link to="/settingaccount" class="sidebar-link" active-class="active">
                        <i class='bx bx-cog'></i>
                        <span class="nav-label">Cài đặt tài khoản</span>
                        <i class='bx bx-chevron-right'></i>
                    </router-link>

                </div>
            </nav>
        </div>

    </aside>
</template>
