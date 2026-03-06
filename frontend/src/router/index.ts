/**
 * Vue Router 4 — Rutas y guards por rol
 */

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AppLayout from '../layouts/AppLayout.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: () => import('../views/public/LandingView.vue'),
      meta: { public: true },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/public/LoginView.vue'),
      meta: { public: true, guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/public/RegisterView.vue'),
      meta: { public: true, guest: true },
    },
    {
      path: '/verify-email',
      name: 'verify-email',
      component: () => import('../views/public/VerifyEmailView.vue'),
      meta: { public: true },
    },
    {
      path: '/ponente',
      component: AppLayout,
      meta: { requiresAuth: true, role: 'ponente' },
      children: [
        { path: '', name: 'ponente-home', component: () => import('../views/ponente/PonenteHome.vue') },
        { path: 'submissions/new', name: 'ponente-new', component: () => import('../views/ponente/NuevaSubmission.vue') },
        { path: 'submissions/:id', name: 'ponente-detail', component: () => import('../views/ponente/SubmissionDetail.vue') },
      ],
    },
    {
      path: '/participante',
      component: AppLayout,
      meta: { requiresAuth: true, role: 'participante' },
      children: [
        { path: '', name: 'participante-home', component: () => import('../views/participante/ParticipanteHome.vue') },
        { path: 'pago', name: 'participante-pago', component: () => import('../views/participante/ParticipantePago.vue') },
      ],
    },
    {
      path: '/revisor',
      component: AppLayout,
      meta: { requiresAuth: true, role: 'revisor' },
      children: [
        { path: '', name: 'revisor-home', component: () => import('../views/revisor/RevisorHome.vue') },
        { path: 'reviews/:id', name: 'revisor-detail', component: () => import('../views/revisor/RevisionDetail.vue') },
      ],
    },
    {
      path: '/admin',
      component: AppLayout,
      meta: { requiresAuth: true, role: ['admin', 'administrativo'] },
      children: [
        { path: '', name: 'admin-home', component: () => import('../views/admin/AdminHome.vue') },
        { path: 'submissions', name: 'admin-submissions', component: () => import('../views/admin/AdminSubmissions.vue') },
        { path: 'submissions/:id', name: 'admin-submission-detail', component: () => import('../views/admin/AdminSubmissionDetail.vue') },
        { path: 'thematic-axes', name: 'admin-axes', component: () => import('../views/admin/AdminAxes.vue') },
      ],
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (to.meta.public) {
    if (to.meta.guest && auth.token) {
      const ok = auth.user ? true : await auth.fetchMe()
      if (ok) {
        const r = auth.role
        if (r === 'ponente') return { name: 'ponente-home' }
        if (r === 'participante') return { name: 'participante-home' }
        if (r === 'revisor') return { name: 'revisor-home' }
        if (r === 'admin' || r === 'administrativo') return { name: 'admin-home' }
      }
    }
    return
  }

  if (to.meta.requiresAuth) {
    if (!auth.token) return { name: 'login', query: { redirect: to.fullPath } }
    if (!auth.user) {
      const ok = await auth.fetchMe()
      if (!ok) return { name: 'login', query: { redirect: to.fullPath } }
    }
    // TODO: verificación de correo cuando se active
    // if (!auth.isEmailVerified) return { name: 'verify-email', query: { redirect: to.fullPath } }
    const requiredRole = to.meta.role as string | string[] | undefined
    if (requiredRole) {
      const roles = Array.isArray(requiredRole) ? requiredRole : [requiredRole]
      if (!roles.includes(auth.role ?? '')) {
        if (auth.role === 'ponente') return { name: 'ponente-home' }
        if (auth.role === 'participante') return { name: 'participante-home' }
        if (auth.role === 'revisor') return { name: 'revisor-home' }
        if (auth.role === 'admin' || auth.role === 'administrativo') return { name: 'admin-home' }
        return { name: 'landing' }
      }
    }
  }

  return
})

export default router
