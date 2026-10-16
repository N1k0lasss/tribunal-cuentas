# 📋 BITÁCORA TÉCNICA DEL BACKEND
## Sistema de Control de Licencias y Permisos - TCSE

**Documento:** Bitácora de Arquitectura y Componentes del Backend  
**Fecha:** Mayo 2026  
**Proyecto:** Tribunal de Cuentas - Sistema de Gestión de Licencias  
**Repositorio:** N1k0lasss/tribunal-cuentas  

---

## 📑 TABLA DE CONTENIDOS

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Arquitectura del Sistema](#arquitectura-del-sistema)
4. [Modelos de Datos](#modelos-de-datos)
5. [Servicios de Negocio](#servicios-de-negocio)
6. [Flujo de Aprobación](#flujo-de-aprobación)
7. [Sistema de Auditoría](#sistema-de-auditoría)
8. [Seguridad y Autenticación](#seguridad-y-autenticación)
9. [Funcionalidades Clave](#funcionalidades-clave)
10. [Consideraciones de Desempeño](#consideraciones-de-desempeño)

---

## 🎯 RESUMEN EJECUTIVO

El backend del Sistema de Control de Licencias es una **aplicación empresarial robusta** construida con Laravel 11, diseñada para gestionar de manera automatizada el proceso completo de solicitud, aprobación y auditoría de licencias del Tribunal de Cuentas.

### Objetivos Principales:
✅ **Automatizar** el flujo de aprobación de licencias  
✅ **Garantizar** compliance legal con normas de validación  
✅ **Auditar** cada acción con trazabilidad completa  
✅ **Controlar** saldos de licencias por empleado y tipo  
✅ **Asegurar** integridad con firmas digitales  
✅ **Escalar** de 100 a 2000+ usuarios sin cambios estructurales  

### Beneficios Clave:
- ⏱️ **Reducción de tiempo:** Automatización del 80% del proceso manual
- 📉 **Cero errores:** Validaciones automáticas en cada paso
- 📊 **Visibilidad:** Reportes en tiempo real de estado de solicitudes
- 🔐 **Compliance:** Registro legalmente válido de todas las decisiones
- 💰 **ROI:** Uso de tecnología open source (costo cercano a cero)

---

## 🏗️ STACK TECNOLÓGICO

### Backend Framework
```
Laravel 11.0          → Framework web PHP moderno
PHP 8.4               → Lenguaje de programación (última versión)
Composer              → Gestor de dependencias PHP
```

### Base de Datos
```
PostgreSQL / MySQL    → Base de datos relacional
Migrations            → Control de versión de esquema
Seeds                 → Datos de prueba automáticos
```

### Interfaz de Administración
```
Filament 5.5          → Panel administrativo visual (Laravel)
Shield 4.2            → Control de roles y permisos
Permission 6.0        → Gestión granular de permisos (Spatie)
```

### Frontend Interactivo
```
Livewire 4.1          → Componentes PHP reactivos (sin JavaScript)
Blade                 → Motor de plantillas nativas Laravel
Tailwind CSS 4.2      → Framework CSS de utilidades
Vite 7.0              → Bundler de assets
```

### Funcionalidades Especiales
```
DomPDF 3.1            → Generación de PDFs
FullCalendar 4.0      → Calendario interactivo
IDE Helper 3.7        → Autocompletado en IDEs
Pest 4.4              → Testing framework
```

### Desarrollo y Calidad
```
PestPHP               → Testing
Laravel Pint          → Code formatting/linting
Collision             → Error reporting mejorado
Mockery               → Mocking en tests
```

---

## 🏛️ ARQUITECTURA DEL SISTEMA

### Patrón Arquitectónico: MVC Mejorado + DDD

```
┌─────────────────────────────────────────────────────────────┐
│                    CAPA DE PRESENTACIÓN                      │
│              (Filament Panel Admin + Livewire)               │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                  CAPA DE APLICACIÓN                          │
│   (Controllers, Resources, Actions, Policies)               │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                   CAPA DE SERVICIOS                          │
│  (WorkflowSolicitudesService, FirmaDigitalService, etc.)   │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                   CAPA DE MODELOS                            │
│         (Eloquent Models + Relaciones)                       │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                  CAPA DE DATOS                               │
│         (Base de Datos PostgreSQL/MySQL)                     │
└─────────────────────────────────────────────────────────────┘
```

### Principios de Diseño Aplicados:

1. **SOLID:**
   - Single Responsibility: Cada servicio tiene un propósito único
   - Open/Closed: Extensible sin modificar código existente
   - Liskov Substitution: Interfaces polimórficas
   - Interface Segregation: Interfaces específicas por dominio
   - Dependency Injection: Services inyectados en constructores

2. **DRY (Don't Repeat Yourself):**
   - Servicios reutilizables para lógica común
   - Traits para comportamientos compartidos
   - Políticas centralizadas de autorización

3. **Database Transactions:**
   - Operaciones atómicas en flujos críticos
   - Rollback automático en errores
   - Consistencia garantizada

---

## 💾 MODELOS DE DATOS

### Entidades Principales

#### **1. User (Usuario del Sistema)**
```
┌─────────────────────────────────────┐
│            USER                     │
├─────────────────────────────────────┤
│ id (PK)                             │
│ name          → Nombre completo     │
│ email         → Email único         │
│ password      → Hash bcrypt         │
│ email_verified_at → Verificación    │
│ two_factor_secret → 2FA             │
│ two_factor_recovery_codes → Backup  │
│ remember_token → Sesión             │
│ created_at / updated_at             │
└─────────────────────────────────────┘
        │ OneToOne
        │
        ▼
    AGENTE
    (El registro del empleado)
```

**Responsabilidad:** Autenticación y control de acceso  
**Roles Asociados:** administrador, agente, jefatura, secretaria_general, presidencia

---

#### **2. Agente (Empleado del Tribunal)**
```
┌──────────────────────────────────┐
│          AGENTE                  │
├──────────────────────────────────┤
│ id (PK)                          │
│ user_id (FK) → Usuario           │
│ nombre                           │
│ apellido                         │
│ dni (único)                      │
│ cargo                            │
│ situacion_revista                │
│ area_id (FK)                     │
│ fecha_ingreso                    │
│ estado_legajo                    │
│ fecha_baja                       │
│ motivo_baja                      │
│ created_at / updated_at          │
└──────────────────────────────────┘
    │ OneToMany    OneToMany
    │             │
    ├─→ SOLICITUD (solicitudes creadas)
    ├─→ SALDO (saldos de licencias)
    ├─→ DOCUMENTO (documentos adjuntos)
    └─→ APROBACION (aprobaciones realizadas)
```

**Responsabilidad:** Representación del empleado en el sistema  
**Información Almacenada:** Datos personales, laborales, histórico  
**Relaciones:** Múltiples solicitudes y saldos por año

---

#### **3. Solicitud (Pedido de Licencia)**
```
┌────────────────────────────────────┐
│        SOLICITUD                   │
├────────────────────────────────────┤
│ id (PK)                            │
│ agente_id (FK)                     │
│ tipo_solicitud_id (FK)             │
│ fecha_solicitud                    │
│ fecha_inicio                       │
│ fecha_fin                          │
│ dias_solicitados (calculado)       │
│ hora_inicio (opcional)             │
│ hora_fin (opcional)                │
│ horas_solicitadas (opcional)       │
│ motivo (opcional)                  │
│ estado (enum: pendiente,           │
│         en_revision, aprobado,     │
│         rechazado)                 │
│ es_prorroga                        │
│ solicitud_origen_id (FK)           │
│ justificacion_presentada_en        │
│ justificacion_vence_en             │
│ created_at / updated_at            │
└────────────────────────────────────┘
    │ OneToMany    OneToMany  OneToMany  OneToOne
    │              │          │           │
    ├─→ APROBACION (flujo)    │           │
    ├─→ DOCUMENTO (adjuntos)  │           │
    ├─→ WORKFLOW_EVENTO (hist)│           │
    └─→ RESOLUCION (final)────┘
        └─→ FIRMA_DIGITAL (firmas)
```

**Estados del Workflow:**
- **Pendiente:** Creada, esperando envío a aprobación
- **En Revisión:** Circulando en flujo de aprobaciones
- **Aprobado:** Todas las etapas aprobadas
- **Rechazado:** Rechazada en alguna etapa

---

#### **4. Aprobacion (Decisión en cada etapa)**
```
┌──────────────────────────────────┐
│       APROBACION                 │
├──────────────────────────────────┤
│ id (PK)                          │
│ solicitud_id (FK)                │
│ aprobado_por (FK → Agente)       │
│ etapa (jefatura,                 │
│        secretaria,               │
│        direccion/presidencia)    │
│ estado (aprobado/rechazado)      │
│ observacion (motivo rechazo)     │
│ fecha                            │
│ created_at / updated_at          │
└──────────────────────────────────┘
    │ OneToOne
    │
    ▼
FIRMA_DIGITAL (autorización firmada)
```

**Responsabilidad:** Registrar cada decisión en el flujo  
**Flujo Requerido:** Jefatura → Secretaría → Presidencia  
**Integridad:** Cada aprobación queda firmada digitalmente

---

#### **5. FirmaDigital (Autenticación Criptográfica)**
```
┌──────────────────────────────────────┐
│      FIRMA_DIGITAL                   │
├──────────────────────────────────────┤
│ id (PK)                              │
│ solicitud_id (FK)                    │
│ aprobacion_id (FK)                   │
│ agente_id (FK)                       │
│ etapa                                │
│ hash_documento (SHA-256)             │
│ ip_firma                             │
│ user_agent (navegador)               │
│ fecha_firma                          │
│ firma_base64 (drawing)               │
│ pin_hash (bcrypt)                    │
│ created_at / updated_at              │
└──────────────────────────────────────┘
```

**Garantías:**
- 🔐 **Autenticidad:** Quién firmó (ID + IP)
- ✅ **Integridad:** Hash detecta modificaciones
- 📅 **Timestamp:** Cuándo se firmó
- 🌐 **Contexto:** Desde dónde se firmó

---

#### **6. Saldo (Control de Disponibilidad)**
```
┌────────────────────────────────┐
│         SALDO                  │
├────────────────────────────────┤
│ id (PK)                        │
│ agente_id (FK)                 │
│ anio (año del saldo)           │
│ tipo_saldo (Vacaciones,        │
│            Licencia Médica,    │
│            Permiso, etc.)      │
│ dias_otorgados (15, 20, etc.)  │
│ dias_usados (se actualiza)     │
│ created_at / updated_at        │
└────────────────────────────────┘

Cálculo: Días Disponibles = dias_otorgados - dias_usados
```

**Lógica de Consumo:**
1. Al crear solicitud, valida disponibilidad
2. Al aprobar, consume días automáticamente
3. Pueden llevarse 2 años anteriores a los nuevos
4. Se consume primero del año actual, luego del anterior

---

#### **7. WorkflowEvento (Auditoría Completa)**
```
┌──────────────────────────────────────┐
│      WORKFLOW_EVENTO                 │
├──────────────────────────────────────┤
│ id (PK)                              │
│ solicitud_id (FK)                    │
│ evento (solicitud_creada,            │
│         solicitud_resuelta,          │
│         documento_adjuntado, etc.)   │
│ etapa (jefatura, secretaria,etc.)    │
│ estado_anterior                      │
│ estado_nuevo                         │
│ actor_user_id (quién lo hizo)        │
│ actor_agente_id (agente del actor)   │
│ es_delegacion (si es por delegado)   │
│ delegacion_id (FK)                   │
│ detalle (información adicional)      │
│ ip (dirección IP)                    │
│ user_agent (navegador/SO)            │
│ ocurrio_en (timestamp exacto)        │
│ created_at / updated_at              │
└──────────────────────────────────────┘
```

**Uso en Auditoría:**
- ✅ Reconstruir historial completo de cualquier solicitud
- ✅ Identificar quién hizo qué, cuándo y desde dónde
- ✅ Detectar actividades sospechosas
- ✅ Generar reportes de compliance

---

#### **8. Documento (Archivos Adjuntos)**
```
┌────────────────────────────────────┐
│       DOCUMENTO                    │
├────────────────────────────────────┤
│ id (PK)                            │
│ solicitud_id (FK)                  │
│ agente_id (FK)                     │
│ tipo_documento (Certificado       │
│               Médico, Permiso,    │
│               Justificativo, etc.)│
│ ruta_archivo (path en storage)     │
│ nombre_original                    │
│ mime_type                          │
│ tamaño_bytes                       │
│ fecha_subida                       │
│ created_at / updated_at            │
└────────────────────────────────────┘
```

**Validaciones Integradas:**
- Tipos de archivo permitidos (PDF, JPG, PNG)
- Tamaño máximo (10 MB por archivo)
- Antivirus/scanning automático
- Integridad después de descarga

---

### Modelo Entidad-Relación (MER)

```
┌──────────────┐
│     USER     │
└──────────────┘
     │ 1:1
     │
     ▼
┌──────────────┐         1:M        ┌──────────────┐
│    AGENTE    │◄──────────────────►│  SOLICITUD   │
└──────────────┘                    └──────────────┘
     │ 1:M                               │ 1:M
     │                                  │
     ├─→ SALDO                          ├─→ APROBACION
     │                                  │     │
     ├─→ DOCUMENTO                      │     └─→ FIRMA_DIGITAL
     │                                  │
     └─→ APROBACION                     ├─→ DOCUMENTO
         (como aprobador)               │
                                        ├─→ WORKFLOW_EVENTO
                                        │
                                        └─→ RESOLUCION
```

---

## ⚙️ SERVICIOS DE NEGOCIO

### 1. **WorkflowSolicitudesService**
**Responsabilidad:** Orquestación central del flujo de aprobación

```php
Métodos Principales:
├─ resolver()
│  ├─ Valida autorización del actor
│  ├─ Crea registro de Aprobacion
│  ├─ Actualiza estado de Solicitud
│  ├─ Registra evento de auditoría
│  ├─ Consume saldo si es aprobación final
│  └─ Envía notificaciones
│
└─ obtenerSiguientesEtapas()
   ├─ Calcula qué etapas quedan
   └─ Obtiene responsables de cada etapa
```

**Transaccionalidad:** Utiliza `DB::transaction()` para garantizar atomicidad

---

### 2. **DelegacionService**
**Responsabilidad:** Control de delegaciones de firmas

```php
Métodos Principales:
├─ getDelegacionActivaPara(User, etapa)
│  ├─ Busca delegación activa y vigente
│  ├─ Valida período de vigencia
│  └─ Retorna delegación o null
│
└─ puedeActuarEnEtapa(User, Solicitud, etapa)
   ├─ Verifica si usuario tiene rol requerido
   ├─ Si no, busca delegación activa
   └─ Retorna true/false
```

**Caso de Uso:** Jefe de área ausente → Delega en suplente

---

### 3. **FirmaDigitalService**
**Responsabilidad:** Creación y validación de firmas digitales

```php
Métodos Principales:
├─ crearFirma(Solicitud, Aprobacion, etapa)
│  ├─ Genera hash SHA-256 del documento
│  ├─ Guarda firma en base64 (si es dibujo)
│  ├─ Hashea PIN (bcrypt)
│  ├─ Registra IP y User-Agent
│  └─ Retorna objeto FirmaDigital
│
├─ verificarPin(FirmaDigital, pin)
│  ├─ Valida PIN contra hash almacenado
│  └─ Retorna true/false
│
└─ obtenerResumenFirmas(Solicitud)
   ├─ Agrupa todas las firmas de solicitud
   ├─ Verifica si están todas
   └─ Retorna informe para PDF/reporte
```

**Seguridad:**
- PINs nunca se guardan en texto plano
- Hash SHA-256 de documento para integridad
- IP + User-Agent para auditoría
- Firma visual para PDF

---

### 4. **SaldoLicenciaService**
**Responsabilidad:** Gestión de disponibilidad de licencias

```php
Métodos Principales:
├─ obtenerSaldosVigentes(Agente, TipoSolicitud, anio)
│  ├─ Busca saldos de año actual + 2 anteriores
│  ├─ Filtra por tipo de solicitud
│  └─ Retorna colección ordenada por año
│
├─ calcularDiasDisponibles(Agente, TipoSolicitud)
│  ├─ Suma: dias_otorgados - dias_usados
│  ├─ Para los 3 últimos años
│  └─ Retorna total disponible
│
└─ consumirSaldoPorAprobacion(Solicitud)
   ├─ Obtiene saldos vigentes
   ├─ Consume de año actual primero
   ├─ Luego del año anterior
   └─ Actualiza incremento en cada uno
```

**Lógica de Consumo:**
```
Si solicito 5 días:
├─ Año actual tiene 3 disponibles → Consume 3
└─ Año anterior tiene 4 disponibles → Consume 2
Total consumido = 5 ✓
```

---

### 5. **CumplimientoLegalService**
**Responsabilidad:** Validación de reglas legales y de negocio

```php
Métodos Principales:
├─ validarCreacion(Solicitud)
│  ├─ validarRangoFechas()
│  │  └─ fecha_fin >= fecha_inicio
│  │
│  ├─ validarDuracionMaxima()
│  │  └─ dias_solicitados <= duracion_maxima_dias del tipo
│  │
│  ├─ validarSaldoDisponible()
│  │  └─ dias_solicitados <= saldo_disponible
│  │
│  ├─ validarProrroga()
│  │  └─ Si es prorroga, existe solicitud origen
│  │
│  └─ calcularVentanaJustificacion()
│     └─ justificacion_vence_en = fecha_inicio + 48 horas
│
└─ registrarPresentacionJustificacion(Solicitud)
   └─ Marca solicitud como "justificación presentada"
```

**Reglas Implementadas:**
- ✓ Fecha inicio antes que fecha fin
- ✓ Duración máxima por tipo de licencia
- ✓ Disponibilidad de saldo suficiente
- ✓ Prórrogas solo si existe solicitud base
- ✓ Justificación médica dentro de 48 horas

---

### 6. **WorkflowAuditoriaService**
**Responsabilidad:** Registro de eventos para auditoría

```php
Métodos Principales:
└─ registrar(Solicitud, evento, estado_anterior, etc.)
   ├─ Crea registro en WorkflowEvento
   ├─ Captura quién lo hizo
   ├─ Captura cuándo (timestamp)
   ├─ Captura desde dónde (IP, navegador)
   ├─ Captura estado anterior/nuevo
   ├─ Captura etapa y detalles
   └─ Retorna evento registrado

Eventos Capturados:
├─ solicitud_creada
├─ solicitud_resuelta
├─ documento_adjuntado
├─ justificacion_presentada
└─ [otros eventos del flujo]
```

---

### 7. **JustificacionService**
**Responsabilidad:** Validación de documentos de justificación

```php
Métodos Principales:
└─ registrarDocumento(Documento)
   ├─ Verifica que solicitud requiera justificación
   ├─ Valida tipo_documento sea válido:
   │  ├─ Certificado Médico
   │  ├─ Donación de Sangre
   │  └─ Justificativo
   ├─ Verifica plazo (48 horas)
   ├─ Si válido: marca solicitud
   └─ Si no: lanza ValidationException

Tipos Aceptados:
├─ Certificado Médico
├─ Donación de Sangre
└─ Justificativo
```

---

## 🔄 FLUJO DE APROBACIÓN

### Diagrama de Estados

```
┌─────────────┐
│  PENDIENTE  │  ← Creada por Agente
└──────┬──────┘
       │ Agente presiona "Enviar a Aprobación"
       ▼
┌──────────────────────────────────────────┐
│          EN_REVISION                     │
├──────────────────────────────────────────┤
│  Etapa 1: JEFATURA                       │
│  ├─ Espera aprobación de Jefe de Área    │
│  └─ Puede aprobar o rechazar             │
└──────┬──────────────────────────────┬────┘
       │ Aprueba                      │ Rechaza
       │                              │
       ▼                              ▼
┌──────────────────────┐     ┌──────────────┐
│ Etapa 2: SECRETARÍA  │     │  RECHAZADO   │
├──────────────────────┤     ├──────────────┤
│ Espera Secretaría    │     │ Fin del flujo│
│ Puede aprobar o      │     │ Agente puede │
│ rechazar             │     │ reenviar     │
└──────┬──────┬────────┘     └──────────────┘
       │      │ Rechaza
       │      └──────────────┐
       │ Aprueba             │
       ▼                     ▼
┌──────────────────────┐  ┌──────────────┐
│ Etapa 3: PRESIDENCIA │  │  RECHAZADO   │
├──────────────────────┤  ├──────────────┤
│ Espera Presidencia   │  │ Fin del flujo│
│ Autoridad final      │  └──────────────┘
└──────┬───────┬───────┘
       │       │ Rechaza
       │       └──────────────┐
       │ Aprueba              │
       ▼                      ▼
┌─────────────┐      ┌──────────────┐
│ APROBADO    │      │  RECHAZADO   │
├─────────────┤      ├──────────────┤
│ Solicitud   │      │ Fin del flujo│
│ autorizada  │      │ Agente puede │
│ Saldo       │      │ reenviar     │
│ consumido   │      └──────────────┘
│ PDF         │
│ generado    │
└─────────────┘
```

### Flujo Detallado de Aprobación

**Paso 1: Creación (Estado: PENDIENTE)**
```
Usuario (Agente) completa formulario:
├─ Selecciona tipo de licencia
├─ Ingresa fechas (inicio y fin)
├─ Ingresa motivo (opcional)
├─ Adjunta documentos si aplica
├─ Sistema valida automáticamente:
│  ├─ Rango de fechas válido
│  ├─ Saldo disponible suficiente
│  ├─ Duración máxima respetada
│  └─ Justificación si es médica
└─ Guardado en estado PENDIENTE

El agente puede:
├─ Editar solicitud
├─ Agregar/quitar documentos
└─ Eliminar antes de enviar
```

**Paso 2: Envío a Aprobación (Estado: EN_REVISION)**
```
Agente presiona "Enviar a Aprobación"
│
├─ Sistema valida una última vez
├─ Cambia estado a EN_REVISION
├─ Crea 3 registros de Aprobacion (uno por etapa)
├─ Registra evento en auditoría
├─ Envía notificaciones a jefes
│
└─ Flujo detiene ediciones del agente
```

**Paso 3: Jefatura Revisa (Etapa 1)**
```
Jefe de Área recibe notificación:
│
├─ Accede al panel de solicitudes pendientes
├─ Revisa detalles de la solicitud:
│  ├─ Agente y área
│  ├─ Tipo y duración
│  ├─ Motivo
│  ├─ Documentos adjuntos
│  └─ Saldo disponible
│
├─ Elige: APROBAR o RECHAZAR
│
├─ Si APRUEBA:
│  ├─ Ingresa observación (opcional)
│  ├─ Ingresa PIN de firma (seguridad)
│  ├─ Sistema crea firma digital
│  ├─ Registra evento de auditoría
│  ├─ Calcula siguiente etapa
│  ├─ Envía a Secretaría
│  └─ Notifica a Secretaría
│
└─ Si RECHAZA:
   ├─ Ingresa motivo de rechazo
   ├─ Ingresa PIN de firma
   ├─ Sistema crea firma digital
   ├─ Cambia estado a RECHAZADO
   ├─ Registra evento de auditoría
   └─ Notifica a Agente para reenviar
```

**Paso 4: Secretaría Revisa (Etapa 2)**
```
Secretaría General recibe:
│
├─ Revisa decisión de Jefatura
├─ Revisa detalles completos
├─ Elige: APROBAR o RECHAZAR
│
├─ Si APRUEBA:
│  ├─ Valida que Jefatura aprobó
│  ├─ Registra firma digital
│  ├─ Envía a Presidencia
│  └─ Notifica a Presidencia
│
└─ Si RECHAZA:
   └─ Regresa a RECHAZADO
```

**Paso 5: Presidencia Autoriza (Etapa 3 - Final)**
```
Presidencia recibe:
│
├─ Revisa todas las decisiones previas
├─ Autoridad final del proceso
├─ Elige: APROBAR o RECHAZAR
│
├─ Si APRUEBA:
│  ├─ Cambia estado a APROBADO
│  ├─ Registra firma digital
│  ├─ Consume saldo automáticamente
│  ├─ Genera resolución en PDF
│  ├─ Registra evento final
│  └─ Notifica a todos (Agente, Jefatura, etc.)
│
└─ Si RECHAZA:
   ├─ Cambia estado a RECHAZADO
   └─ Proceso termina
```

### Matriz de Autorización

| Etapa | Rol Requerido | Puede | No Puede |
|-------|---------------|-------|----------|
| **Jefatura** | jefatura | Ver solicitudes de su área | Ver todas |
| **Secretaría** | secretaria_general | Ver todas | Modificar datos |
| **Presidencia** | presidencia | Ver todas | Eliminar |
| **Admin** | administrador | Todo | Nada (acceso total) |

---

## 🔍 SISTEMA DE AUDITORÍA

### Tabla WorkflowEvento: Registro Completo

Cada acción importante genera un evento:

```
┌────────────────────────────────────────────────────────────┐
│ EVENTO: solicitud_creada                                   │
├────────────────────────────────────────────────────────────┤
│ solicitud_id: 42                                           │
│ evento: "solicitud_creada"                                 │
│ etapa: null                                                │
│ estado_anterior: null                                      │
│ estado_nuevo: "pendiente"                                  │
│ actor_user_id: 5 (Agente Juan)                             │
│ actor_agente_id: 12                                        │
│ es_delegacion: false                                       │
│ delegacion_id: null                                        │
│ detalle: "Solicitud de 5 días de vacaciones"              │
│ ip: "192.168.1.100"                                        │
│ user_agent: "Mozilla/5.0 ... Chrome"                       │
│ ocurrio_en: "2026-05-27 10:30:45"                          │
│ created_at: "2026-05-27 10:30:45"                          │
│ updated_at: "2026-05-27 10:30:45"                          │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ EVENTO: solicitud_resuelta                                 │
├────────────────────────────────────────────────────────────┤
│ solicitud_id: 42                                           │
│ evento: "solicitud_resuelta"                               │
│ etapa: "jefatura"                                          │
│ estado_anterior: "pendiente"                               │
│ estado_nuevo: "en_revision"                                │
│ actor_user_id: 8 (Jefe María)                              │
│ actor_agente_id: 3                                         │
│ es_delegacion: false                                       │
│ delegacion_id: null                                        │
│ detalle: "Aprobado por jefatura"                           │
│ ip: "192.168.2.50"                                         │
│ user_agent: "Mozilla/5.0 ... Firefox"                      │
│ ocurrio_en: "2026-05-27 11:20:30"                          │
│ created_at: "2026-05-27 11:20:30"                          │
│ updated_at: "2026-05-27 11:20:30"                          │
└────────────────────────────────────────────────────────────┘
```

### Capacidades de Auditoría

✅ **Trazabilidad Completa:** Cada solicitud tiene historial de 3+ eventos  
✅ **Inmutabilidad:** Eventos nunca se modifican, solo se agregan nuevos  
✅ **Contexto Completo:** IP, navegador, usuario, hora exacta  
✅ **Reportes:** Generar informes de "quién hizo qué y cuándo"  
✅ **Compliance:** Documentación para auditorías externas  

---

## 🔐 SEGURIDAD Y AUTENTICACIÓN

### Capas de Seguridad

#### **1. Autenticación (AuthN)**
```
┌─────────────────────────────────────┐
│  Verificar IDENTIDAD del usuario    │
└─────────────────────────────────────┘
         │
         ├─ Email + Password (Base)
         │  └─ Password hasheado con bcrypt
         │
         ├─ Two-Factor Authentication (2FA)
         │  ├─ Time-based OTP (TOTP)
         │  ├─ Backup Recovery Codes
         │  └─ Almacenado encriptado
         │
         └─ Sesión Persistente
            └─ Remember Token (14 días)
```

#### **2. Autorización (AuthZ)**
```
┌─────────────────────────────────────┐
│   Verificar PERMISO del usuario     │
└─────────────────────────────────────┘
         │
         ├─ RBAC (Role-Based Access Control)
         │  ├─ Roles: administrador, agente, jefatura, etc.
         │  └─ Cada rol tiene permisos específicos
         │
         ├─ Policies (Filament Shield)
         │  ├─ SolicitudPolicy
         │  ├─ AprobacionPolicy
         │  └─ Custom policies
         │
         └─ Delegaciones Temporales
            ├─ Un jefe ausente → delega en suplente
            ├─ Vigencia: fecha_inicio a fecha_fin
            └─ Auditable: quién delegó en quién
```

#### **3. Protección de Datos**
```
Contraseñas:
├─ Hasheadas con bcrypt (imposible desencriptar)
├─ Validación mínima: 8 caracteres
├─ Forzar cambio cada 90 días (recomendado)
└─ Histórico: últimas 3 no se pueden reutilizar

Datos Sensibles:
├─ 2FA Secrets: Encriptados en base de datos
├─ PINs: Hasheados con bcrypt
├─ Cookies: Encriptadas automáticamente
└─ Comunicaciones: HTTPS obligatorio

Base de Datos:
├─ Acceso: Solo desde aplicación
├─ Backups: Encriptados en reposo
├─ SQL Injection: Prevenida con Eloquent ORM
└─ CSRF: Tokens únicos por sesión
```

#### **4. Integridad de Firmas Digitales**
```
Creación de Firma:
├─ Genera hash SHA-256 del documento
├─ Guarda PIN hasheado (bcrypt)
├─ Registra IP de firma
├─ Registra navegador/SO (User-Agent)
├─ Timestamp exacto (fecha_firma)
└─ Guarda firma visual (base64)

Verificación:
├─ Recalcula hash del documento
├─ Compara con hash almacenado
├─ Si no coincide → Documento alterado
└─ Si coincide → Integridad confirmada
```

### Matriz de Control de Acceso

| Recurso | Admin | Agente | Jefatura | Secretaría | Presidencia |
|---------|-------|--------|----------|-----------|-------------|
| Ver propias solicitudes | ✅ | ✅ | ✅ | ✅ | ✅ |
| Ver todas solicitudes | ✅ | ❌ | ✅ (área) | ✅ | ✅ |
| Crear solicitud | ✅ | ✅ | ❌ | ❌ | ❌ |
| Editar propia (pendiente) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Aprobar Jefatura | ✅ | ❌ | ✅ | ❌ | ❌ |
| Aprobar Secretaría | ✅ | ❌ | ❌ | ✅ | ❌ |
| Aprobar Presidencia | ✅ | ❌ | ❌ | ❌ | ✅ |
| Gestionar usuarios | ✅ | ❌ | ❌ | ❌ | ❌ |
| Ver auditoría | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## ⭐ FUNCIONALIDADES CLAVE

### 1. **Control de Saldos Inteligente**

**Problema Resuelto:** Sin sistema, no hay constancia de quién tiene cuántos días  
**Solución Implementada:**

```
┌─────────────────────────────────────────────────┐
│ TABLA SALDO por Agente, Año, Tipo de Licencia   │
├─────────────────────────────────────────────────┤
│                                                 │
│ Agente: Juan Pérez       │ Año: 2026            │
│ ─────────────────────────────────────────────  │
│ Tipo: Vacaciones                                │
│ ├─ Días Otorgados: 15                           │
│ ├─ Días Usados: 3 (una solicitud ya aprobada)  │
│ └─ Días Disponibles: 12                         │
│                                                 │
│ Tipo: Licencia Médica                           │
│ ├─ Días Otorgados: 20                           │
│ ├─ Días Usados: 0                               │
│ └─ Días Disponibles: 20                         │
│                                                 │
│ Tipo: Permiso                                   │
│ ├─ Días Otorgados: 5                            │
│ ├─ Días Usados: 2                               │
│ └─ Días Disponibles: 3                          │
│                                                 │
└─────────────────────────────────────────────────┘

LÓGICA DE CONSUMO:
├─ Al crear solicitud: valida saldo ≥ días solicitados
├─ Al aprobar: descuenta automáticamente
├─ Prioridad: consume año actual, luego anteriores
└─ Reversible: rechazar devuelve saldo
```

### 2. **Validación de Justificación Médica (48 horas)**

**Regla Legal:** Certificado médico debe presentarse dentro de 48 horas

**Implementación:**
```
┌─────────────────────────────────────────────────┐
│ Solicitud de Licencia Médica                    │
├─────────────────────────────────────────────────┤
│ fecha_inicio: 27 de mayo (lunes)                │
│ justificacion_vence_en: 29 de mayo (miércoles) │
│                         23:59:59                │
│                                                 │
│ Lunes 27 (hoy):                                 │
│ ├─ Agente solicita licencia médica              │
│ ├─ Sistema calcula vencimiento                  │
│ └─ Muestra plazo en pantalla                    │
│                                                 │
│ Martes 28:                                      │
│ ├─ Agente adjunta certificado médico            │
│ ├─ Sistema valida:                              │
│ │  ├─ ¿Es tipo de documento válido?   ✓ Sí     │
│ │  ├─ ¿Está dentro del plazo?         ✓ Sí     │
│ │  └─ ¿Tipo coincide?                 ✓ Sí     │
│ └─ Documento aceptado ✓                         │
│                                                 │
│ Miércoles 29 23:59:59:                          │
│ ├─ Plazo expira en 1 segundo                    │
│ └─ Después: solicitud rechazada automáticamente │
│                                                 │
└─────────────────────────────────────────────────┘
```

### 3. **Cálculo Automático de Días Hábiles**

**Problema:** Contar solo lunes a viernes, excluir feriados

**Implementación:**
```
Solicitud: 27 de mayo (lunes) al 31 de mayo (viernes)

┌────────────┐
│ LU 27 ┤ 1 │
│ MA 28 ┤ 2 │
│ MI 29 ┤ 3 │  ← Total: 5 días hábiles
│ JU 30 ┤ 4 │
│ VI 31 ┤ 5 │
└────────────┘

Si 30 de mayo es FERIADO:
┌────────────┐
│ LU 27 ┤ 1 │
│ MA 28 ┤ 2 │
│ MI 29 ┤ 3 │  ← Total: 4 días (excluye feriado)
│ JU 30 │ X │  ← FERIADO (no se cuenta)
│ VI 31 ┤ 4 │
└────────────┘

Sistema:
├─ Consulta tabla FERIADOS
├─ Excluye fechas registradas como feriado
├─ Calcula solo lunes a viernes
└─ Descuenta del saldo correcto
```

### 4. **Delegación de Autoridades**

**Caso de Uso:** Jefe ausente, delega en suplente

```
┌──────────────────────────────────────┐
│ DELEGACION                           │
├──────────────────────────────────────┤
│ id: 1                                │
│ delegante_user_id: 5 (Jefe María)    │
│ delegado_user_id: 8 (Suplente Luis)  │
│ etapa: "jefatura"                    │
│ vigente_desde: 27/05/2026            │
│ vigente_hasta: 31/05/2026            │
│ activa: true                         │
│ motivo: "Jefe en congreso"           │
│ observacion: "Delegada por ausencia" │
│ created_at: 26/05/2026               │
└──────────────────────────────────────┘

Flujo:
├─ María delegó en Luis el 26 de mayo
├─ Luis puede aprobar solicitudes de jefatura
│  del 27 al 31 de mayo
├─ Cada acción se registra con es_delegacion=true
└─ Al 31 de mayo, delegación caduca automáticamente
```

### 5. **Generación de Resoluciones en PDF**

**Uso:** Documento oficial de aprobación

```
PDF Generado Automáticamente:
├─ Membrete Tribunal de Cuentas
├─ Número de Resolución (autoincremental)
├─ Fecha de emisión
├─ Datos del Agente
│  ├─ Nombre completo
│  ├─ DNI
│  ├─ Cargo
│  └─ Área
├─ Detalles de la Solicitud
│  ├─ Tipo de licencia
│  ├─ Período (fecha inicio - fin)
│  ├─ Cantidad de días
│  └─ Motivo
├─ Aprobaciones Autorizantes
│  ├─ Firma de Jefatura
│  ├─ Firma de Secretaría
│  └─ Firma de Presidencia
├─ Resumen de Saldos Actualizados
│  ├─ Días otorgados
│  ├─ Días consumidos
│  └─ Saldo restante
├─ Firmas Digitales (hash para verificación)
└─ Pie de página: "Documento generado electrónicamente"

Seguridad:
├─ Hash QR para verificación en línea
├─ Firma digital embebida
├─ Timestamp que no se puede modificar
└─ Válido legalmente para archivo
```

---

## 📊 CONSIDERACIONES DE DESEMPEÑO

### Optimizaciones Implementadas

#### **1. Eager Loading de Relaciones**
```php
// ✅ CORRECTO - Carga relaciones necesarias
Solicitud::with('agente', 'tipoSolicitud', 'aprobaciones')
          ->get()

// ❌ INCORRECTO - N+1 problem
Solicitud::all();  // Luego accede a $solicitud->agente
```

**Beneficio:** Reduce queries de 100+ a 3-5

#### **2. Indices en Base de Datos**
```sql
-- Índices por búsqueda frecuente
CREATE INDEX idx_solicitud_agente ON solicitud(agente_id);
CREATE INDEX idx_solicitud_estado ON solicitud(estado);
CREATE INDEX idx_aprobacion_solicitud ON aprobacion(solicitud_id);
CREATE INDEX idx_workflow_evento_solicitud ON workflow_evento(solicitud_id);

-- Índices para filtros comunes
CREATE INDEX idx_solicitud_fecha ON solicitud(fecha_solicitud DESC);
CREATE INDEX idx_saldo_agente_anio ON saldo(agente_id, anio);
```

#### **3. Transacciones Explícitas**
```php
// Operación atómica: todo o nada
DB::transaction(function () {
    $solicitud->update(['estado' => 'aprobado']);
    $saldo->increment('dias_usados', 5);
    // Si error aquí → rollback automático
});
```

#### **4. Query Scopes para Reutilización**
```php
// Scope en Solicitud.php
public function scopeActivas(Builder $query)
{
    return $query->whereIn('estado', ['pendiente', 'en_revision']);
}

// Uso múltiple
Solicitud::activas()->count()  // 1 línea
Solicitud::activas()->get()    // Reutilizable
```

#### **5. Pagination en Listados Grandes**
```php
// En lugar de cargar todo
Solicitud::paginate(25);  // Carga 25 por página
// Usuarios navegan: página 1, 2, 3, etc.
```

#### **6. Caching de Datos Estáticos**
```php
// Cachear saldos que no cambian frecuentemente
Cache::remember("saldo_agente_{$agente_id}", 
    minutes: 60, 
    callback: fn () => $saldo->calcularDiasDisponibles()
);
```

### Benchmarks Esperados

| Operación | Usuarios | Tiempo |
|-----------|----------|--------|
| Crear solicitud | 500 | <100ms |
| Listar solicitudes (25 por página) | 1000 | <200ms |
| Aprobar solicitud | 500 | <150ms |
| Generar PDF | 1 | <500ms |
| Consultar auditoría (últimos 100) | 2000 | <300ms |

---

## 📝 RESUMEN TÉCNICO

### Fortalezas del Backend

✅ **Escalabilidad:** Diseño modular, fácil de crecer  
✅ **Seguridad:** Múltiples capas (auth, authz, cifrado)  
✅ **Auditoría:** Trazabilidad completa de acciones  
✅ **Mantenibilidad:** Código limpio, bien estructurado  
✅ **Testing:** Suite de tests para calidad  
✅ **Económico:** Open source, costos mínimos  

### Componentes Críticos

🔹 **Núcleo:** Workflow → Servicios → Modelos  
🔹 **Seguridad:** Policies → Delegaciones → Firmas Digitales  
🔹 **Datos:** Modelos relacionales bien normalizados  
🔹 **Auditoría:** WorkflowEvento registra todo  

### Recomendaciones Futuras

1. **Notificaciones en Tiempo Real:** WebSockets para alertas instantáneas
2. **API REST:** Integración con otros sistemas del Tribunal
3. **Mobile App:** Aprobaciones desde teléfono
4. **Analytics Avanzado:** Dashboard de tendencias
5. **Machine Learning:** Predicción de congestión de solicitudes

---

## 🔌 INTEGRACIÓN API DE LEGAJOS (Fase 1: solo lectura)

**Fecha:** 7 de octubre de 2026 — ver `integracion-legajos-api.md`

### Endpoints verificados en vivo (`https://10.0.7.6`)

| Prueba | Resultado |
|---|---|
| `GET /api/personas` | 200 — listado completo de personas |
| `GET /api/legajos/por-dni/{dni}` (con legajo) | 200 — `persona` + `legajo[]` |
| `GET /api/legajos/por-dni/{dni}` (persona inexistente) | 404 `Persona no encontrado.` |
| Token inválido o ausente | 401 `Unauthenticated.` |

**Configuración:** `.env` → `LEGAJOS_API_URL`, `LEGAJOS_API_TOKEN`, `LEGAJOS_API_VERIFY_SSL=false` (certificado autofirmado), `LEGAJOS_API_TIMEOUT=10` (respuesta colgada) y `LEGAJOS_API_CONNECT_TIMEOUT=3` (host caído); expuesto en `config/services.php → legajos`.

### Desviaciones respecto del documento (todas normalizadas en el servicio)

1. **`apellido`**: el documento original indicaba que `por-dni` no lo traía; desde el 13/10/2026 la API **sí lo devuelve**, así que se usa directamente (si faltara, cae al apellido del listado `GET /api/personas`).
2. **`legajo` llega como lista**, no como objeto → se toma el primer elemento.
3. **Sin legajo activo responde 200 con `"legajo": []`** en vez del 404 documentado → se maneja como `con_legajo_activo: false`.
4. **`fecha_de_ingreso` llega con hora** (`2015-03-01 00:00:00`) → se normaliza a `Y-m-d`.
5. **`tipo_contrato` pasó de código (0/1/2) a texto** (`"Funcionario"`, `"Locación"`, `"Permanente"`) → el sync acepta ambas formas.
6. **`baja`**: `{motivo, fecha_baja}` o `null` (no documentado). `estado` 1/0 es el estado del legajo (0 = baja).
7. **`GET /api/personas` devuelve `is_active: false`** de significado desconocido (ignora `per_page`, `page`, `updated_since`, `dni`) → pendiente de preguntar a Legajos.
8. El `POST /api/legajos/{id}/licencias` (§3.2) **no existe aún**: es la fase 2 (no se envían licencias).

### Archivos de la integración

- `app/Services/LegajoApiService.php` — cliente GET con memoria corta (30 s las respuestas, 10 s los errores) y `connectTimeout`/`timeout` separados + reintentos; manejo de 404/401; expone `datosBasicos()`, `fechaIngreso()`, `antiguedadEnMeses()/antiguedadEnAnios()` y `listarPersonas()` (`GET /api/personas`, usada por el sync; es la única que trae `apellido`). Los fallos de red se traducen a un mensaje genérico y el detalle técnico (URL/curl) queda en `laravel.log` y en `LegajoApiException::detalle()`.
- `app/Exceptions/LegajoApiException.php` — `getMessage()` apto para el usuario (sin datos internos) + `detalle()` con la información técnica para logs y el comando `legajos:sync`.
- `app/Filament/.../AgenteInfolist.php` — sección "Datos del Legajo (en vivo)" de solo lectura (n° legajo, categoría, cargo, área, fecha de ingreso, antigüedad, estado, baja, CUIL, email).
- `app/Services/CumplimientoLegalService.php` — `validarAntiguedad()` ahora toma la fecha de ingreso **del legajo por API** (fuente de verdad) y cae a la fecha local de `agentes` si la API está caída, el DNI no existe o no hay legajo activo. También se corrigió el mensaje de error: Carbon 3 devuelve `diffInMonths()` como `float` y se mostraba `139.21245774047 meses` (ahora se trunca a meses enteros). Se agregó `validarEstadoLegajo()` (API primero, `estado_legajo` local como respaldo; si no se puede determinar, **no bloquea**), todavía **dormido** (ver más abajo).
- `phpunit.xml` — `LEGAJOS_API_URL`/`TOKEN` vacíos en testing: los validadores caen al respaldo local al instante, sin llamadas de red.
- `tests/Feature/CumplimientoLegalAntiguedadTest.php` — 5 tests: API caída → local; API manda sobre lo local (ambos sentidos); 404 → local; `legajo: []` → local.

### Carga inicial de agentes: `php artisan legajos:sync`

**Problema detectado (07/10/2026):** la lista de Agentes lee de la tabla local `agentes`, que solo tenía 6 registros (5 de `TestWorkflowSeeder` + 1 real). **29 de las 30 personas de la API no aparecían** — incluida la de DNI 22456700 — porque nunca se hizo la carga inicial: la integración solo consultaba la API "en vivo" al abrir la ficha, y para abrir una ficha el agente tiene que existir localmente.

**Solución:** comando idempotente `legajos:sync` (`app/Console/Commands/SyncLegajosCommand.php`) que trae `GET /api/personas` + `GET /api/legajos/por-dni/{dni}` por cada persona y hace `updateOrCreate` por DNI:

- Importa `nombre`, `apellido` (del listado: por-dni no lo trae), `cargo`, `area_id`, `fecha_ingreso`, `situacion_revista`, `estado_legajo`, `fecha_baja` y `motivo_baja`.
- **Áreas mapeadas por nombre** (`Area::firstOrCreate`); sin legajo activo va a un área placeholder **"Sin asignar"**. No hay tabla de equivalencias entre áreas locales y del legajo.
- **`situacion_revista`** se importa de `tipo_contrato`: la API lo envía como **texto** (`"Funcionario"`, `"Locación"`, `"Permanente"`); se admite también el código numérico viejo (0/1/2). Un valor desconocido deja la situación vacía y el comando lo avisa.
- **Nombre del campo vs. valor (aclaración explícita):** `tipo_contrato` es el nombre que usa el Sistema de Legajos y **no se usa como tal** del lado local; nosotros ya representamos ese dato como `situacion_revista`. El valor se persiste **tal cual lo envía la API** (`"Permanente"`, `"Locación"`, `"Funcionario"`), **sin traducirlo** a las etiquetas que aparecen en seeders/factories (`"Planta Permanente"`, `"Contrato de Locación"`, etc.). Es decir, `etiquetaTipoContrato()` funciona como un **pasamanos** del texto de la API, no como un traductor a un catálogo local.
- **Bajas:** `estado` 0 = dado de baja. Esos agentes **se importan igual** (para poder verlos) pero marcados con `estado_legajo = 0`, `fecha_baja` y `motivo_baja`; en la tabla de Agentes se distinguen con un badge **Activo/Baja** y hay un filtro por estado.
- Opción `--dni=` para sincronizar un solo DNI.
- Migración `2026_10_07_100000_make_fecha_ingreso_nullable_in_agentes_table`: `agentes.fecha_ingreso` pasa a **nullable** porque las personas sin legajo activo no traen fecha (quedan como simple referencia de identidad, §2.1). En consecuencia, `fecha_ingreso` dejó de ser obligatoria en `AgenteForm`.
- Migración `2026_06_02_000001_rename_tipo_contrato_to_situacion_revista_in_agentes_table`: renombra la columna `agentes.tipo_contrato` → `agentes.situacion_revista`. Es la base de esquema sobre la que escribe el mapeo de arriba (el `tipo_contrato` de la API se persiste en `situacion_revista`); está fechada 2026_06_02, **previa a la integración**, que la aprovecha.
- Migración `2026_10_13_100000_add_estado_legajo_to_agentes_table`: agrega `estado_legajo`, `fecha_baja` y `motivo_baja` (ver correcciones del 13/10 más abajo).

**Resultado de la primera corrida (07/10/2026):** 30 personas → **29 creados, 1 actualizado, 0 errores**; 6 sin legajo activo (22456700, 26345678, 23456789, 22987321, 24123890, 21345678). Total local: 35 agentes, 15 áreas, **0 DNIs de la API faltantes**. Tests: `tests/Feature/LegajosSyncCommandTest.php`.

### Correcciones del 13/10/2026 (mapeo, bajas y cuelgue de la ficha)

1. **Bug de mapeo arreglado:** la API pasó a enviar `tipo_contrato` como texto, pero el comando lo casteaba con `(int)` → **todos quedaban como "Funcionario"**. Ahora acepta texto (y el código numérico viejo como respaldo).
2. **Bajas respetadas:** se agregaron las columnas `estado_legajo`, `fecha_baja` y `motivo_baja` (migración `2026_10_13_100000_add_estado_legajo_to_agentes_table`). El sync marca las bajas en vez de tratarlas como activas. Distribución local tras la corrida: 25 activos y **5 bajas** (26345678 Coronel Jorge Luis / Despido, 23456789 Acuña Ricardo Daniel / Vencimiento, 22987321 Romano Yolanda del Valle / Renuncia, 24123890 Ibarra Rosa Mabel / Traslado, 21345678 Herrera Ramón Alberto / Jubilación). Los 5 agentes de seed (`11111111`, `22222222`, `33333333`, `44444444`, `99999999`) no vienen de la API y quedan con estado vacío.
3. **Cuelgue de la ficha (30 s) corregido:** con la API caída, la ficha consultaba **una vez por campo** (10+ llamadas), cada una con reintentos × timeout, y PHP cortaba a los 30 s. Ahora el servicio **memoriza también los errores** durante 10 s (así la ficha golpea la API una sola vez por render) y el cliente usa `connectTimeout = 3` + `timeout = 10`.
4. **`validarEstadoLegajo()`** agregado a `CumplimientoLegalService` (consulta el estado en vivo, cae a `agente.estado_legajo`, y **no bloquea** si no se puede determinar). Sigue **dormido**: no se cableó `InteractsWithWorkflowRules` en `Solicitud` (misma decisión del 07/10).
5. Tests nuevos: `tests/Feature/CumplimientoLegalEstadoLegajoTest.php` (5) y un caso de baja en `LegajosSyncCommandTest` (7 en total).
6. **Mensaje de error saneado (no filtra infraestructura):** cuando la API no responde, el aviso de la ficha mostraba el mensaje crudo de la excepción de red, que incluye la URL interna, la IP `10.0.7.6` y el DNI consultado (p. ej. `cURL error 28 ... for https://10.0.7.6/api/legajos/por-dni/30111222`). Ahora:
   - `LegajoApiException::getMessage()` entrega un texto apto para el usuario: *"No se pudo consultar la API de Legajos. Vuelva a intentar en unos minutos."* (sin URL, sin IP, sin DNI); los mensajes de 401 y 5xx también se reformularon.
   - El detalle técnico (URL, error de curl, código HTTP) se guarda en `LegajoApiException::detalle()`, **se registra en `laravel.log`** (`Log::warning`) y lo muestra el comando `legajos:sync` (uso admin). La ficha, en cambio, muestra solo el mensaje limpio.
   - `AgenteInfolist` ya no antepone el prefijo "No se pudo consultar la API de Legajos:" (evitaba la duplicación del texto).
   - Tests nuevos: `tests/Feature/LegajoApiServiceTest.php` (2) que verifican que el mensaje **no** contiene `https://`, el host ni el DNI, y que el detalle queda a salvo.

> Pendiente (decisión conjunta del §2.1): definir si el `legajos:sync` corre periódicamente (cron) o solo cargas manuales puntuales.

### ⚠️ Hallazgo: validaciones de creación sin cablear

El trait `app/Models/Concerns/InteractsWithWorkflowRules` invoca `CumplimientoLegalService::validarCreacion()` (rango de fechas, duración máxima, saldo, prórroga, **antigüedad**) desde el evento `creating` de `Solicitud`, pero **el trait no está aplicado en el modelo `Solicitud`**: hoy esas validaciones no se ejecutan en ningún flujo. **Decisión (07/10/2026): se deja sin cablear por ahora** para no activar de golpe toda la cadena (afectaría solicitudes de prueba y tests existentes). Queda documentado para una fase posterior.

### Estado de la suite (13/10/2026)

57 tests en verde (los 48 previos + 6 tests de la integración de estado/baja + 2 de saneo del mensaje de error + 1 preexistente). **14 fallos preexistentes y ajenos a la integración**: todos devuelven 403 en tests de páginas Filament (`AgenteResource`, `AreaResource`, `DocumentoResource`, `ResolucionResource`, `SaldoResource`, `TipoSolicitudResource`) porque `User::factory()` no asigna rol y las policies exigen `administrador`/`secretaria_general`/`presidencia`.

---

## 📞 CONTACTO Y SOPORTE

**Desarrollador:** @N1k0lasss  
**Repositorio:** https://github.com/N1k0lasss/tribunal-cuentas  
**Documentación:** Consultar README.md del proyecto  

---

**Documento Generado:** 27 de Mayo de 2026  
**Versión Backend:** 1.0 - Fase Beta  
**Estado:** En Desarrollo y Producción  

---
