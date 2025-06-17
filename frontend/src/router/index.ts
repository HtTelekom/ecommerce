
// export default router
import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import HomeView from '../views/HomeView.vue'

// Route type definitions
declare module 'vue-router' {
  interface RouteMeta {
    title?: string
    requiresAuth?: boolean
    requiresAdmin?: boolean
    guest?: boolean
  }
}

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/home',
    name: 'home',
    component: HomeView,
    meta: {
      title: 'Ana Sayfa',
      requiresAuth: true
    }
  },
  {
    path: '/about',
    name: 'about',
    component: () => import('../views/AboutView.vue'),
    meta: {
      title: 'Hakkında',
      requiresAuth: true
    }
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: {
      guest: true,
      title: 'Giriş Yap'
    }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: {
      requiresAuth: true,
      title: 'Dashboard'
    }
  },

  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue'),
    meta: {
      title: '404 - Sayfa Bulunamadı'
    }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Type definitions for user and auth
interface User {
  id: number
  name: string
  email: string
  role: 'user' | 'admin' | 'super_admin'
}

// Navigation guards
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token')
  let user: User | null = null

  try {
    const userStr = localStorage.getItem('user')
    user = userStr ? JSON.parse(userStr) : null
  } catch (error) {
    console.error('Error parsing user data:', error)
    localStorage.removeItem('user')
  }

  // Set page title
  if (to.meta.title) {
    document.title = `${to.meta.title} - E-Commerce Admin`
  } else {
    document.title = 'E-Commerce Admin'
  }

  // Check authentication requirements
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!token || !user) {
      // Clear invalid auth data
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')

      next({
        path: '/login',
        query: { redirect: to.fullPath }
      })
      return
    }

    // Check admin requirements
    if (to.matched.some(record => record.meta.requiresAdmin)) {
      if (!['admin', 'super_admin'].includes(user.role)) {
        next({
          path: '/dashboard',
          query: { error: 'insufficient_permissions' }
        })
        return
      }
    }

    next()
  } else if (to.matched.some(record => record.meta.guest)) {
    // Guest only routes (login page)
    if (token && user) {
      next('/dashboard')
      return
    }
    next()
  } else {
    next()
  }
})

// After navigation guard for additional setup
router.afterEach((to, from) => {
  // You can add analytics tracking here
  console.log(`Navigated from ${from.path} to ${to.path}`)
})

export default router