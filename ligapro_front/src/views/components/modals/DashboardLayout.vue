<template>
  <div class="flex min-h-screen bg-slate-100">
    <!-- Menú Lateral -->
    <aside
      class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between p-4 shrink-0"
    >
      <div>
        <div class="flex items-center gap-2 px-2 py-3 mb-6">
          <span class="text-xl">⚽</span>
          <span class="font-black text-lg tracking-tight text-slate-900">LigaPro</span>
        </div>

        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2 mb-2">
          Administración General
        </div>

        <nav class="space-y-1">
          <router-link
            to="/dashboard"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors"
            :class="
              $route.path === '/dashboard'
                ? 'bg-indigo-50 text-indigo-600'
                : 'text-slate-600 hover:bg-slate-50'
            "
          >
            🏠 Dashboard
          </router-link>
          <router-link
            to="/leagues"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors"
            :class="
              $route.path.includes('/leagues')
                ? 'bg-indigo-50 text-indigo-600'
                : 'text-slate-600 hover:bg-slate-50'
            "
          >
            🏆 Ligas
          </router-link>
          <router-link
            to="/matches"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors"
            :class="
              $route.path === '/matches'
                ? 'bg-indigo-50 text-indigo-600'
                : 'text-slate-600 hover:bg-slate-50'
            "
          >
            📅 Calendario Global
          </router-link>
        </nav>
      </div>

      <div class="pt-4 border-t border-slate-200">
        <router-link
          to="/profile"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
        >
          ⚙️ Ajustes y Perfil
        </router-link>
      </div>
    </aside>

    <!-- Contenido Principal (Aquí se inyecta la vista mediante el slot) -->
    <main class="flex-1 p-8 overflow-y-auto">
      <slot />
    </main>
  </div>
</template>
