import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Canvas from '../views/Canvas.vue'
import HelpGuide from '../views/HelpGuide.vue'
import TableDesigner from '../views/TableDesigner.vue'
import LogicDesigner from '../views/LogicDesigner.vue'
import AdminDashboard from '../views/AdminDashboard.vue'
import { adminGuard } from './routeGuard.js'

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
    path: '/canvas',
    name: 'Canvas',
    component: Canvas,
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
  },
  {
    path: '/logics',
    name: 'LogicDesigner',
    component: LogicDesigner,
  },
  {
    path: '/admin',
    name: 'AdminDashboard',
    component: AdminDashboard,
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

router.beforeEach(async (to, from, next) => {
  if (to.meta.requiresAdmin) {
    return adminGuard(to, from, next)
  }
  next()
})

export default router
