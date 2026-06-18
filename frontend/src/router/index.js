import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Subscribe from '../views/Subscribe.vue'
import Canvas from '../views/Canvas.vue'
import HelpGuide from '../views/HelpGuide.vue'
import TableDesigner from '../views/TableDesigner.vue'
import LogicDesigner from '../views/LogicDesigner.vue'
import AdminDashboard from '../views/AdminDashboard.vue'
import AdminLogics from '../views/AdminLogics.vue'
import AdminTables from '../views/AdminTables.vue'
import AdminFlows from '../views/AdminFlows.vue'
import { authGuard, adminGuard } from './routeGuard.js'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
  },
  {
    path: '/subscribe',
    name: 'Subscribe',
    component: Subscribe,
    meta: { requiresAuth: true },
  },
  {
    path: '/canvas',
    name: 'Canvas',
    component: Canvas,
    meta: { requiresAuth: true },
  },
  {
    path: '/help',
    name: 'HelpGuide',
    component: HelpGuide,
  },
  {
    path: '/tables',
    name: 'TableDesigner',
    component: TableDesigner,
    meta: { requiresAuth: true },
  },
  {
    path: '/logics',
    name: 'LogicDesigner',
    component: LogicDesigner,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/logics',
    name: 'AdminLogics',
    component: AdminLogics,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/tables',
    name: 'AdminTables',
    component: AdminTables,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/flows',
    name: 'AdminFlows',
    component: AdminFlows,
    meta: { requiresAdmin: true },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const { useUserStore } = await import('../stores/useUserStore.js')
  const store = useUserStore()

  const needsAuth = to.meta.requiresAuth || to.meta.requiresAdmin
  const isPublicAuthPage = to.name === 'Login' || to.name === 'Register'
  if ((needsAuth || isPublicAuthPage) && !store.state.user) {
    try {
      await store.fetchUser()
    } catch {
      store.state.user = null
    }
  }

  const isAuth = !!store.state.user

  const authResult = authGuard(to, isAuth)
  if (authResult) return authResult

  if (to.meta.requiresAdmin) {
    return adminGuard(to, isAuth, store)
  }

  if (isAuth && (to.name === 'Login' || to.name === 'Register')) {
    return '/canvas'
  }
})

export default router
