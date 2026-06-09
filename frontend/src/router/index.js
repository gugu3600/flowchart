import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Canvas from '../views/Canvas.vue'
import HelpGuide from '../views/HelpGuide.vue'
import TableDesigner from '../views/TableDesigner.vue'
import LogicDesigner from '../views/LogicDesigner.vue'

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
    path: '/:pathMatch(.*)*',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
