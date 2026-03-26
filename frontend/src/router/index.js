import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Lazy-loaded page components
const LoginView      = () => import('@/views/LoginView.vue')
const RegisterView   = () => import('@/views/RegisterView.vue')
const DashboardView  = () => import('@/views/DashboardView.vue')
const DevicesView    = () => import('@/views/DevicesView.vue')
const BorrowView     = () => import('@/views/BorrowView.vue')
const BorrowingsView = () => import('@/views/BorrowingsView.vue')
const AdminView      = () => import('@/views/AdminView.vue')

const routes = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { guest: true },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true },
  },
  {
    path: '/devices',
    name: 'devices',
    component: DevicesView,
    meta: { requiresAuth: true },
  },
  {
    path: '/borrow',
    name: 'borrow',
    component: BorrowView,
    meta: { requiresAuth: true },
  },
  {
    path: '/borrowings',
    name: 'borrowings',
    component: BorrowingsView,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login' })
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
