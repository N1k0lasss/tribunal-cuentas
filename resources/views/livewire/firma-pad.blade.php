<div>
    <div class="space-y-4">
        <div class="text-sm text-gray-600">
            <x-heroicon-m-pencil class="inline w-4 h-4 mr-1" />
            Firme en el recuadro inferior para confirmar la aprobación
        </div>

        <div class="border-2 border-gray-300 rounded-lg bg-white overflow-hidden">
            <canvas 
                x-data="{
                    canvas: null,
                    ctx: null,
                    isDrawing: false,
                    lastX: 0,
                    lastY: 0,
                    init() {
                        this.canvas = this.$refs.firmaCanvas;
                        this.ctx = this.canvas.getContext('2d');
                        
                        // Configurar canvas
                        this.ctx.strokeStyle = '#1e40af';
                        this.ctx.lineWidth = 2;
                        this.ctx.lineCap = 'round';
                        this.ctx.lineJoin = 'round';
                        
                        // Eventos de mouse
                        this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
                        this.canvas.addEventListener('mousemove', (e) => this.draw(e));
                        this.canvas.addEventListener('mouseup', () => this.stopDrawing());
                        this.canvas.addEventListener('mouseout', () => this.stopDrawing());
                        
                        // Eventos touch
                        this.canvas.addEventListener('touchstart', (e) => this.handleTouch(e, 'start'));
                        this.canvas.addEventListener('touchmove', (e) => this.handleTouch(e, 'move'));
                        this.canvas.addEventListener('touchend', () => this.stopDrawing());
                    },
                    handleTouch(e, action) {
                        e.preventDefault();
                        const touch = e.touches[0];
                        const rect = this.canvas.getBoundingClientRect();
                        const x = touch.clientX - rect.left;
                        const y = touch.clientY - rect.top;
                        
                        if (action === 'start') {
                            this.isDrawing = true;
                            this.lastX = x;
                            this.lastY = y;
                        } else if (action === 'move' && this.isDrawing) {
                            this.drawAt(x, y);
                        }
                    },
                    startDrawing(e) {
                        this.isDrawing = true;
                        const rect = this.canvas.getBoundingClientRect();
                        this.lastX = e.clientX - rect.left;
                        this.lastY = e.clientY - rect.top;
                    },
                    draw(e) {
                        if (!this.isDrawing) return;
                        const rect = this.canvas.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;
                        this.drawAt(x, y);
                    },
                    drawAt(x, y) {
                        this.ctx.beginPath();
                        this.ctx.moveTo(this.lastX, this.lastY);
                        this.ctx.lineTo(x, y);
                        this.ctx.stroke();
                        this.lastX = x;
                        this.lastY = y;
                    },
                    stopDrawing() {
                        this.isDrawing = false;
                        this.guardarFirma();
                    },
                    guardarFirma() {
                        const firma = this.canvas.toDataURL('image/png');
                        @this.actualizarFirma(firma);
                    },
                    limpiar() {
                        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                        @this.limpiarFirma();
                    }
                }"
                x-ref="firmaCanvas"
                wire:ignore
                width="500"
                height="150"
                class="w-full cursor-crosshair touch-none"
            ></canvas>
        </div>

        <div class="flex justify-between items-center text-xs text-gray-500">
            <span>← Dibuje su firma con el mouse o dedo</span>
            <button 
                type="button"
                x-on:click="$refs.firmaCanvas.__x.$data.limpiar()"
                class="text-red-600 hover:text-red-800 font-medium"
            >
                <x-heroicon-m-trash class="inline w-4 h-4 mr-1" />
                Limpiar
            </button>
        </div>

        @if($firmaBase64)
            <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded flex items-center gap-2">
                <x-heroicon-m-check-circle class="w-5 h-5 text-green-600" />
                <span class="text-sm text-green-800">Firma capturada correctamente</span>
            </div>
        @endif
    </div>
</div>
