<template>
  <aside
    class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col h-screen"
  >
    <!-- Logo / Marca -->
    <div class="p-6 flex items-center gap-3 border-b border-slate-100 dark:border-slate-800">
      <span class="text-xl">⚡</span>
      <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">LigaPro</h1>
    </div>

    <!-- Menú de Navegación del Administrador -->
    <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
      <p
        class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500"
      >
        Administración General
      </p>

      <!-- Dashboard / Inicio -->
      <router-link
        to="/dashboard"
        class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
        active-class="bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400"
      >
        <span>🏠</span> Dashboard
      </router-link>

      <!-- Mis Ligas -->
      <router-link
        to="/leagues"
        class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
        active-class="bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400"
      >
        <span>🏆</span> Ligas
      </router-link>

      <!-- Gestión Global de Partidos -->
      <router-link
        to="/matches"
        class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
        active-class="bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400"
      >
        <span>📅</span> Calendario Global
      </router-link>

      <!-- Directorio de Equipos -->
      <router-link
        to="/teams"
        class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
        active-class="bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400"
      >
        <span>🛡️</span> Equipos y Clubes
      </router-link>

      <!-- Estadísticas Generales / Disciplina -->
      <router-link
        to="/statistics"
        class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
        active-class="bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400"
      >
        <span>📊</span> Reportes y Sanciones
      </router-link>
    </nav>

    <!-- Sección de Perfil y Ajustes al pie de la barra lateral -->
    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
      <router-link
        to="/profile"
        class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        active-class="bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white"
      >
        <span>⚙️</span> Ajustes y Perfil
      </router-link>
    </div>
  </aside>
</template>
