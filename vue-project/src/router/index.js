import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/FrontEnd/Login.vue'
import Register from '@/views/FrontEnd/Register.vue'
import Dashboard from '@/views/dashboard/dashboard.vue'
import GroupsManager from '@/views/groups/GroupsManager.vue'
import Layout from '@/components/FrontEnd/Layout.vue'
import TaskList from '@/views/TaskList/TaskList.vue'
import Notifications from '@/views/Notifications/Notifications.vue'
import SettingAccount from '@/views/Setting/SettingAccount.vue'
import Report from '@/views/Report/Report.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: Login,
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
    },

    {
      path: '/',
      component: Layout,
      children: [
        { path: 'dashboard', name: 'dashboard.index', component: Dashboard, meta: { requiresAuth: true } },
        { path: 'groups', name: 'groups.index', component: GroupsManager, meta: { requiresAuth: true } },
        { path: 'tasklist', name: 'tasklist.index',component: TaskList, meta: { requiresAuth: true }},
        { path: 'notifications', name: 'notifications.index', component: Notifications, meta: { requiresAuth: true } },
        { path: 'report', name: 'report.index', component: Report, meta: { requiresAuth: true } },
        { path: 'settingaccount', name: 'settingaccount.index', component: SettingAccount, meta: { requiresAuth: true } },
      ]
    }
  ],
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  // Nếu vào trang cần đăng nhập mà không có token thì chuyển về login
  if (to.meta.requiresAuth && !token) {
    next('/')
  } else {
    next()
  }
})

export default router
