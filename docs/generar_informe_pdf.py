from fpdf import FPDF

OUTPUT = "/home/runner/work/tribunal-cuentas/tribunal-cuentas/docs/informe-avance-proyecto-ia.pdf"


def safe(text: str) -> str:
    return (
        text.replace("—", "-")
        .replace("•", "-")
        .replace("→", "->")
        .replace("“", '"')
        .replace("”", '"')
        .replace("…", "...")
    )


class ReportPDF(FPDF):
    def header(self):
        if self.page_no() == 1:
            return
        self.set_fill_color(26, 54, 93)
        self.set_text_color(255, 255, 255)
        self.set_font("Helvetica", "B", 10)
        self.cell(0, 8, safe("Informe de avance del proyecto - Tribunal de Cuentas"), new_x="LMARGIN", new_y="NEXT", fill=True)
        self.ln(3)
        self.set_text_color(0, 0, 0)

    def footer(self):
        self.set_y(-12)
        self.set_font("Helvetica", "I", 9)
        self.set_text_color(90, 90, 90)
        self.cell(0, 6, safe(f"Pagina {self.page_no()}"), align="C")


def mc(pdf, h, text, align="L", border=0, fill=False):
    pdf.multi_cell(0, h, safe(text), align=align, border=border, fill=fill, new_x="LMARGIN", new_y="NEXT")


def section_title(pdf: ReportPDF, title: str):
    pdf.set_font("Helvetica", "B", 13)
    pdf.set_text_color(26, 54, 93)
    mc(pdf, 7, title)
    pdf.ln(1)
    pdf.set_text_color(0, 0, 0)


def body_text(pdf: ReportPDF, text: str):
    pdf.set_font("Helvetica", "", 10)
    mc(pdf, 6, text)


def bullet_list(pdf: ReportPDF, items):
    pdf.set_font("Helvetica", "", 10)
    for item in items:
        mc(pdf, 6, f"- {item}")


def simple_table(pdf: ReportPDF, headers, rows, widths):
    pdf.set_font("Helvetica", "B", 9)
    pdf.set_fill_color(230, 236, 245)
    x0 = pdf.get_x()
    y0 = pdf.get_y()
    x = x0
    for h, w in zip(headers, widths):
        pdf.set_xy(x, y0)
        pdf.multi_cell(w, 7, safe(h), border=1, align="C", fill=True, new_x="RIGHT", new_y="TOP")
        x += w
    pdf.set_xy(x0, y0 + 7)

    pdf.set_font("Helvetica", "", 9)
    for row in rows:
        y_start = pdf.get_y()
        x_start = pdf.get_x()
        heights = []
        for value, w in zip(row, widths):
            lines = max(1, len(pdf.multi_cell(w, 5, safe(str(value)), border=0, dry_run=True, output="LINES")))
            heights.append(lines * 5)
        row_h = max(heights)

        x = x_start
        for value, w in zip(row, widths):
            pdf.set_xy(x, y_start)
            pdf.multi_cell(w, row_h, safe(str(value)), border=1, new_x="RIGHT", new_y="TOP")
            x += w
        pdf.set_xy(x_start, y_start + row_h)


pdf = ReportPDF(orientation="P", unit="mm", format="A4")
pdf.set_auto_page_break(auto=True, margin=15)

pdf.add_page()
pdf.set_fill_color(26, 54, 93)
pdf.rect(0, 0, 210, 297, style="F")
pdf.set_text_color(255, 255, 255)
pdf.set_y(55)
pdf.set_font("Helvetica", "B", 24)
mc(pdf, 12, "Informe de avance del proyecto", align="C")
pdf.ln(4)
pdf.set_font("Helvetica", "", 14)
mc(pdf, 9, "Sistema de Gestion de Licencias y Permisos - Tribunal de Cuentas", align="C")
pdf.ln(18)
pdf.set_font("Helvetica", "", 12)
mc(pdf, 8, "Repositorio: N1k0lasss/tribunal-cuentas", align="C")
mc(pdf, 8, "Fecha: 7 de octubre de 2026", align="C")
mc(pdf, 8, "Integrantes: [Completar nombres de los integrantes]", align="C")
pdf.ln(8)
pdf.set_font("Helvetica", "I", 11)
mc(pdf, 7, "Nota: reemplazar el campo de integrantes antes de la entrega final.", align="C")

pdf.add_page()
section_title(pdf, "Indice")
bullet_list(pdf, [
    "1. Resumen ejecutivo",
    "2. Objetivo del sistema",
    "3. Metodologia y alcance de verificacion",
    "4. Estado de avance (estimaciones funcionales por modulo)",
    "5. Funcionalidades evidenciadas (estado y referencias)",
    "6. Riesgos, pendientes y recomendaciones",
    "7. Datos que debe tener la IA",
    "8. Prompt/Comando utilizado",
    "9. Justificacion de la eleccion de la IA",
    "10. Metodologia y fuentes consultadas",
    "11. Conclusion general",
])

section_title(pdf, "1) Resumen ejecutivo")
body_text(pdf, "El repositorio evidencia un sistema Laravel para gestion de licencias y permisos con panel administrativo en Filament, workflow de aprobaciones por etapas, control de saldos, gestion documental y componentes de auditoria/notificaciones. Tambien se observan modulos de calendario, estadisticas y reportes en PDF.")
body_text(pdf, "La base funcional se encuentra avanzada, con multiples recursos CRUD, servicios de negocio y pruebas de integracion/feature. Existen elementos parciales o sujetos a validacion en entorno real, como la ruta /recibos con texto provisional y aspectos dependientes de configuracion/credenciales de produccion.")

section_title(pdf, "2) Objetivo del sistema")
body_text(pdf, "Implementar un sistema institucional para administrar solicitudes de licencias/permisos, su flujo de aprobacion por roles, documentacion de respaldo, resoluciones, saldos disponibles y trazabilidad de auditoria, con soporte de reportes y panel de visualizacion para el Tribunal de Cuentas.")

section_title(pdf, "3) Metodologia y alcance de verificacion")
bullet_list(pdf, [
    "Analisis basado exclusivamente en evidencia del codigo/estructura en rama main.",
    "No se asumieron datos de produccion ni resultados no observables en archivos.",
    "Los porcentajes son estimaciones funcionales inferidas, no metricas oficiales del equipo.",
    "Se distingue evidencia verificable de aspectos no verificables sin ejecucion/despliegue.",
])

pdf.add_page()
section_title(pdf, "4) Estado de avance (estimaciones funcionales por modulo)")
body_text(pdf, "Advertencia: porcentajes estimados por lectura de estructura/codigo; no representan medicion oficial de gestion del proyecto.")
headers = ["Modulo", "Avance", "Evidencia principal"]
rows = [
    ["Base tecnologica y puesta en marcha", "90%", "composer.json, package.json, README.md, AdminPanelProvider.php"],
    ["Usuarios, autenticacion, roles y permisos", "85%", "User.php, RoleSeeder.php, app/Policies, LoginHistories, middlewares"],
    ["Agentes, areas y tipos de solicitud", "85%", "Resources/Agentes, Areas, TipoSolicituds, migraciones"],
    ["Solicitudes, aprobaciones, resoluciones, saldos y documentos", "80%", "SolicitudResource.php, Aprobacions, Resolucions, Saldos, Documentos"],
    ["Workflow y auditoria", "88%", "WorkflowSolicitudesService, WorkflowAuditoriaService, InteractsWithWorkflowRules, SolicitudObserver, ActivityResource"],
    ["Calendario, feriados y estadisticas/reportes", "82%", "Calendario.php, FeriadosCalendario.php, EstadisticasLicencias.php, ReporteAnual.php"],
    ["Firma digital y justificacion", "70%", "FirmaDigitalService.php, JustificacionService.php, firma-pad.blade.php"],
    ["Notificaciones y vencimientos", "78%", "CheckExpiredDocuments.php, app/Notifications, migracion flags"],
    ["Pruebas automatizadas y CI", "80%", "tests/Feature, Pest, workflows lint/tests"],
    ["Frontend/presentacion", "85%", "Filament + Blade + estilos admin"],
]
simple_table(pdf, headers, rows, [78, 20, 92])

pdf.add_page()
section_title(pdf, "5) Funcionalidades evidenciadas (estado y referencias)")
headers2 = ["Funcionalidad", "Estado", "Referencias"]
rows2 = [
    ["Inicio redirige al panel Filament configurable", "Implementado", "routes/web.php"],
    ["Roles institucionales con Spatie + seeding", "Implementado", "User.php, RoleSeeder.php, DatabaseSeeder.php"],
    ["Policies por dominio", "Implementado", "app/Policies/*.php"],
    ["Workflow de aprobacion por etapas", "Implementado", "WorkflowSolicitudesService.php, Solicitud.php"],
    ["Auditoria de workflow (actor/IP/user-agent)", "Implementado", "WorkflowAuditoriaService.php"],
    ["Reglas de bloqueo de edicion en flujo", "Implementado", "InteractsWithWorkflowRules.php"],
    ["Notificacion + rechazo automatico por adjuntos vencidos", "Implementado", "SolicitudObserver.php, CheckExpiredDocuments.php"],
    ["Gestion documental con upload y vista", "Implementado", "DocumentoResource.php, ruta documento.ver"],
    ["Historial de login y control de sesion/horario", "Implementado", "login_histories migration, SessionTimeout, BlockOutsideWorkingHours"],
    ["Calendarios, historial y reportes PDF", "Implementado", "Pages/Calendario, HistorialSolicitudes, ReporteAnual, InformeDiarioPage"],
    ["Ruta /recibos con texto fijo", "Parcial", "routes/web.php"],
    ["Configuracion/credenciales de produccion", "Pendiente/por validar", "No verificable sin entorno ejecutandose"],
]
simple_table(pdf, headers2, rows2, [83, 25, 82])

section_title(pdf, "6) Riesgos, pendientes y recomendaciones")
body_text(pdf, "Observado en codigo (verificable):")
bullet_list(pdf, [
    "Ruta /recibos con contenido provisional: implementar vista/flujo real o restringir acceso temporal.",
    "Dependencia de configuracion operativa (correo, storage, colas): validar en staging/produccion.",
    "Reforzar pruebas de escenarios limite en workflow legal (prorrogas, rechazos automaticos, categorias).",
])
body_text(pdf, "No verificable sin ejecucion/despliegue:")
bullet_list(pdf, [
    "Entregabilidad real de correos/notificaciones en infraestructura productiva.",
    "Rendimiento con volumen de datos real.",
    "Integridad de credenciales y servicios externos en operacion.",
    "UX final con usuarios reales del organismo.",
])

section_title(pdf, "7) Datos que debe tener la IA")
bullet_list(pdf, [
    "URL/nombre de repositorio, ID, rama y fecha de corte.",
    "Objetivo del proyecto y contexto institucional.",
    "Stack tecnologico y lenguajes.",
    "Arbol de archivos y documentacion.",
    "Manifiestos/configuracion: composer.json, package.json, README, workflows.",
    "Evidencia de modelos, servicios, rutas, vistas, migraciones, seeders y pruebas.",
    "Commits/PRs/issues para evolucion temporal (si aplica).",
    "Nombres de integrantes, periodo evaluado, criterios de avance y formato de entrega.",
    "Restricciones de privacidad y datos que no deben enviarse.",
])

pdf.add_page()
section_title(pdf, "8) Prompt/Comando utilizado")
body_text(pdf, "Prompt reproducible (resumen): Analiza el repositorio N1k0lasss/tribunal-cuentas (ID 1173793925), rama main, fecha de corte 7/10/2026; redacta en espanol un informe de avance con portada, resumen, objetivo, metodologia, avance por modulos con porcentajes estimados, tabla de funcionalidades con estado y referencias de archivos, riesgos, pendientes, recomendaciones, datos necesarios para IA, prompt utilizado y justificacion del uso de IA; distinguir evidencia vs inferencia; no inventar datos ni resultados no observados; salida final en PDF.")
body_text(pdf, "Comando breve sugerido: Generar informe de avance (PDF) del repo N1k0lasss/tribunal-cuentas en main, citando evidencia de archivos y separando evidencia vs inferencia.")

section_title(pdf, "9) Justificacion de la eleccion de la IA")
body_text(pdf, "Se eligio una IA generativa con capacidad de analisis de repositorios/documentos por su aptitud para sintetizar multiples archivos, relacionar arquitectura con funcionalidades y redactar un documento formal con tablas y secciones estructuradas.")
body_text(pdf, "Limitaciones: no sustituye ejecucion real, pruebas manuales, revision humana de requisitos, ni validacion de datos/configuracion productiva.")

section_title(pdf, "10) Metodologia y fuentes consultadas")
bullet_list(pdf, [
    "README.md; composer.json; package.json; routes/web.php; app/Models/User.php; app/Models/Solicitud.php",
    "database/seeders/DatabaseSeeder.php; database/seeders/RoleSeeder.php",
    "Arbol app/Filament/Admin/Resources; app/Services; app/Policies; database/migrations",
    "tests/Feature; resources/views; .github/workflows/lint.yml; .github/workflows/tests.yml",
])

section_title(pdf, "11) Conclusion general")
body_text(pdf, "Con base en la evidencia del codigo en main, el proyecto presenta un estado de madurez tecnico medio-alto: stack actual, estructura modular consistente, workflow de aprobaciones por rol, trazabilidad y base de pruebas automatizadas.")
body_text(pdf, "La valoracion final debe ser prudente: existen componentes parciales y validaciones operativas pendientes. Se considera avanzado en implementacion tecnica, con necesidad de cierre funcional y validacion integral en entorno real.")

pdf.output(OUTPUT)
print(OUTPUT)
