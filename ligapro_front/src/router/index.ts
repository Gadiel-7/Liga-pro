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
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
    },

    // --- Rutas del Aficionado (Solo Lectura) ---
    {
      path: '/explorer',
      name: 'explorer',
      component: () => import('../views/ExplorerView.vue'),
    },
    {
      path: '/aficionado/matches',
      name: 'aficionado-matches',
      component: () => import('../views/AficionadoMatchesView.vue'),
    },
    {
      path: '/league/matches',
      name: 'league-matches',
      component: () => import('../views/LeagueMatchesAficionadoView.vue'),
    },

    {
      path: '/aficionado/leagues/:id',
      name: 'league-detail-aficionado',
      component: () => import('../views/LeagueDetailAficionadoView.vue'),
    },
    {
      path: '/aficionado/teams/:id',
      name: 'aficionado-team-detail',
      component: () => import('../views/AficionadoTeamDetailView.vue'),
    },
    {
      path: '/standings',
      name: 'standings',
      component: () => import('../views/AficionadoStandingsAndStatsView.vue'),
    },
    {
      path: '/statistics',
      name: 'statistics',
      component: () => import('../views/AficionadoStandingsAndStatsView.vue'),
    },

    // --- Panel de Administración / Gestión (Original) ---
    {
      path: '/teams/:id',
      name: 'team-detail',
      component: () => import('../views/TeamDetailView.vue'),
    },
    {
      path: '/leagues/:id',
      name: 'league-detail',
      component: () => import('../views/LeagueDetailView.vue'),
    },
    {
      path: '/admin/matches',
      name: 'admin-matches',
      component: () => import('../views/MatchesView.vue'),
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
      path: '/categories',
      name: 'categories',
      component: () => import('../views/CategoriesView.vue'),
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../views/ProfileView.vue'),
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
    },

    // Ruta raíz y comodín
    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/explorer',
    },
  ],
})

export default router
