<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{
                currentMonth: new Date().getMonth() + 1,
                currentYear: new Date().getFullYear(),
                feriados: @js($this->feriados),
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                get filteredFeriados() {
                    // Ahora usamos directamente los feriados que nos envía el calendario (ya vienen filtrados por mes visible)
                    return this.feriados.sort((a, b) => new Date(a.fecha_inicio) - new Date(b.fecha_inicio));
                },
                get monthName() {
                    return this.monthNames[this.currentMonth - 1];
                },
                capitalizeFirstLetter(string) {
                    return string.charAt(0).toUpperCase() + string.slice(1);
                },
                formatDate(dateString) {
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    const date = new Date(dateString);
                    date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
                    let formatted = date.toLocaleDateString('es-AR', options);
                    return this.capitalizeFirstLetter(formatted);
                },
                init() {
                    window.addEventListener('calendar-state-update', (e) => {
                        this.currentMonth = e.detail.month;
                        this.currentYear = e.detail.year;
                        // Si el calendario nos manda sus eventos en tiempo real, los usamos!
                        if (e.detail.eventos) {
                            this.feriados = e.detail.eventos;
                        }
                    });
                }
            }"
            style="display: flex; flex-direction: column; gap: 1.25rem;"
        >
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.75rem;">
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white" x-text="'Feriados de ' + monthName"></h2>
                <div class="px-3 py-1 rounded-full bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 text-xs font-semibold" x-text="filteredFeriados.length + ' feriados'"></div>
            </div>
            
            <div class="custom-scrollbar" style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 500px; overflow-y: auto; padding-right: 0.5rem;">
                <template x-if="filteredFeriados.length === 0">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; text-align: center; border-radius: 1rem; border: 1px dashed rgba(156, 163, 175, 0.5);">
                        <svg style="width: 40px; height: 40px; color: #9ca3af; margin-bottom: 0.5rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">No hay feriados agendados para este mes.</span>
                    </div>
                </template>
                
                <template x-for="feriado in filteredFeriados" :key="feriado.id">
                    <div 
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm transition-all hover:shadow-md group"
                        :style="feriado.tipo === 'Nacional' ? 'border-left: 6px solid #3b82f6; padding: 1rem;' : (feriado.tipo === 'Local' ? 'border-left: 6px solid #10b981; padding: 1rem;' : 'border-left: 6px solid #f59e0b; padding: 1rem;')"
                    >
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                            <h3 class="font-bold text-gray-900 dark:text-white text-base leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors" x-text="feriado.nombre"></h3>
                            
                            <template x-if="feriado.tipo === 'Nacional'">
                                <div style="padding: 0.375rem; border-radius: 0.375rem; background-color: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                    <svg style="width: 20px; height: 20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                      <path d="M3 2.5a.5.5 0 0 1 .5.5v1.27l9.467 2.84a1 1 0 0 1 .684.897l.006 5.96a.5.5 0 0 1-.5.5H3.5v4.03a.5.5 0 0 1-1 0v-15.5a.5.5 0 0 1 .5-.5Z" />
                                    </svg>
                                </div>
                            </template>
                            <template x-if="feriado.tipo === 'Local'">
                                <div style="padding: 0.375rem; border-radius: 0.375rem; background-color: rgba(16, 185, 129, 0.1); color: #10b981;">
                                    <svg style="width: 20px; height: 20px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                </div>
                            </template>
                            <template x-if="feriado.tipo === 'Provincial'">
                                <div style="padding: 0.375rem; border-radius: 0.375rem; background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                    <svg style="width: 20px; height: 20px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                                    </svg>
                                </div>
                            </template>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.375rem; font-size: 0.875rem;" class="text-gray-500 dark:text-gray-400">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <svg style="width: 16px; height: 16px; opacity: 0.7;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                <span class="font-medium" x-text="formatDate(feriado.fecha_inicio)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <style>
                .custom-scrollbar::-webkit-scrollbar { width: 5px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.5); border-radius: 20px; }
            </style>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>