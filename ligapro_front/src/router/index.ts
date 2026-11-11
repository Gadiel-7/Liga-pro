import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // --- Autenticación ---
    {
      path: '/email/verify/:id/:hash?',
      name: 'VerifyEmail',
      component: () => import('../views/VerifyEmailView.vue'),
    },

    {
      path: '/explorer',
      name: 'explorer',
      component: () => import('../views/ExplorerView.vue'),
    },

    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
    },

    // --- Panel Principal y Ligas ---
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../views/ProfileView.vue'),
    },
    {
      path: '/standings',
      name: 'standings',
      component: () => import('../views/StandingsView.vue'),
    },

    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
    },
    {
      path: '/leagues',
      name: 'leagues-list',
      component: () => import('../views/LeagueListView.vue'),
    },
    {
      path: '/leagues/:id',
      name: 'league-detail',
      component: () => import('../views/LeagueDetailView.vue'),
    },

    // --- Secciones de Gestión ---
    {
      path: '/matches',
      name: 'matches',
      component: () => import('../views/MatchesView.vue'),
    },
    {
      path: '/categories',
      name: 'categories',
      component: () => import('../views/CategoriesView.vue'),
    },
    {
      path: '/teams/:id',
      name: 'team-detail',
      component: () => import('../views/TeamDetailView.vue'),
    },
    {
      path: '/statistics',
      name: 'statistics',
      component: () => import('../views/StatisticsView.vue'),
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
    },

    // Ruta de comodín para redirigir cualquier URL desconocida al dashboard
    {
      path: '/:pathMatch(.*)*',
      redirect: '/dashboard',
    },
  ],
})

export default router
