# Diseño Técnico: Sistema de Presentación con Control Móvil

## 📱 Visión General

SimpleMenti opera en **4 modos distintos**:

1. **Modo Dashboard**: Creación y gestión de presentaciones
2. **Modo Presentador**: Muestra presentaciones/evaluaciones en vivo (presentador.php)
3. **Modo Control Remoto**: Control desde dispositivo móvil (control-movil.php) - NUEVO
4. **Modo Estudiante**: Respuesta a evaluaciones (participante.php)

Este documento describe la implementación del **Modo Control Remoto**, permitiendo al docente controlar la presentación desde su smartphone/tablet con seguridad y optimización cliente-servidor.

---

## 🔄 Flujo de Uso Completo

### Entrada del Docente (index.php)

```
1. Docente abre index.php
2. Ingresa código de 6 dígitos (ej: ABC123)
3. Sistema detecta que es código de SESIÓN → presentador.php
```

### Modo Presentador (Estado Normal)

```
presentador.php muestra:
├─ Código/QR para ESTUDIANTES (SIEMPRE VISIBLE)
│  └─ Grande, prominente, en la parte superior
│
├─ Contenido de la presentación
│  └─ Pregunta actual, resultados, estadísticas
│
└─ Botón "Conectar Dispositivo Móvil" (DISCRETO)
   └─ Pequeño, en la parte inferior
```

### Activación de Control Móvil (Opcional)

```
1. Docente hace clic en "Conectar Dispositivo Móvil"
   └─► Modal de advertencia:
       "⚠️ Este QR dará control TOTAL. No proyectes ni compartas."
       [Cancelar] [Generar QR]

2. Si acepta → Muestra QR/código temporal (30 segundos)
   └─► QR solo para control (NO es el QR de estudiantes)

3. Docente escanea desde SU móvil (sin proyectar)
   └─► Móvil:
       ├─ ¿Tiene sesión? → Vinculación directa
       └─ No tiene sesión? → Pide credenciales → Vinculación

4. Móvil entra en modo control-movil.php
5. PC puede cambiar a modo proyección fullscreen (opcional)
```

### Seguridad por Diseño

- ✅ QR/código de control NO visible por defecto
- ✅ Advertencia explícita antes de generar
- ✅ QR expira en 30 segundos
- ✅ Separación clara: QR estudiantes ≠ QR control
- ✅ Autenticación requerida en móvil

---

## 🎯 Funcionalidades Principales

### 1. ID de Presentación Especial
- **Generación**: ID único de 8 caracteres (ej: `PRE-X7K9M`) generado al activar "Modo Presentación Móvil"
- **Vigencia**: 24 horas o hasta finalizar sesión
- **Seguridad**: Token JWT con validación de dispositivo/IP
- **Persistencia**: Almacenado en `data/presentation_tokens/{session_id}.json`

### 2. Modo Proyección (PC en Aula)
- **Vista dedicada**: `proyeccion.php` - pantalla fullscreen sin controles
- **Sincronización**: WebSocket o polling optimizado (500ms)
- **Características**:
  - Slides/preguntas según secuencia
  - Puntero virtual sincronizado desde móvil
  - Transiciones suaves
  - Sin UI de control (todo desde móvil)

### 3. Control Móvil (Smartphone Docente)
- **Vista optimizada**: `control-movil.php` - responsive mobile-first
- **Capacidades**:
  - ✅ Avanzar/retroceder diapositivas (swipe + botones)
  - ✅ Ver preview de siguiente slide
  - ✅ Lista de participantes conectados
  - ✅ Panel de interacciones en tiempo real:
    - 🙋 Manos levantadas (con nombres)
    - 💬 Preguntas de estudiantes (responder/marcar respondida)
    - 📊 Nivel de comprensión (gráfico)
    - 😀 Reacciones recientes
  - ✅ Puntero virtual (touchpad mode)
  - ✅ Notas del presentador (privadas)
  - ✅ Timer/cronómetro

### 4. Puntero Virtual
- **Tecnología**: Canvas + WebSocket/SSE para posición
- **Visualización**: Círculo suave con efecto de "laser pointer"
- **Control**: Desde móvil con touchpad o gyroscope
- **Optimización**: Solo envía coordenadas cuando se mueve (throttling 50ms)

---

## 🏗️ Arquitectura Técnica

### Componentes Nuevos

```
┌─────────────────────────────────────────────────────────────┐
│                    ARQUITECTURA DEL SISTEMA                  │
└─────────────────────────────────────────────────────────────┘

┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   MÓVIL      │         │   SERVIDOR   │         │  PROYECCIÓN  │
│  (Docente)   │◄────────►│    PHP       │◄────────►│  (PC Aula)   │
└──────────────┘         └──────────────┘         └──────────────┘
      │                         │                         │
      │  POST control-movil/    │                         │
      │  avanzar.php            │                         │
      ├────────────────────────►│                         │
      │                         │                         │
      │                         │  SSE /stream-           │
      │                         │  proyeccion.php         │
      │                         │◄────────────────────────┤
      │                         │                         │
      │                         │  JSON state update      │
      │                         ├────────────────────────►│
      │                         │                         │
      │  POST puntero.php       │                         │
      │  {x: 0.5, y: 0.3}       │                         │
      ├────────────────────────►│                         │
      │                         │                         │
      │                         │  Broadcast pointer      │
      │                         ├────────────────────────►│
      │                         │                         │
```

### Flujo de Activación Simplificado

**Filosofía**: QR/código de control NO visible por defecto. Solo se genera cuando el docente lo solicita explícitamente, con advertencia de seguridad.

#### Desde presentador.php

```
1. Docente en presentador.php (modo normal)
   ├─ Ve QR/código de ESTUDIANTES (visible)
   └─ Ve botón "Conectar Dispositivo Móvil" (discreto)

2. Hace clic en "Conectar Dispositivo Móvil"
   └─► Modal de advertencia:
       ┌────────────────────────────────────────┐
       │  ⚠️ ADVERTENCIA DE SEGURIDAD           │
       │                                        │
       │  Este QR te dará control TOTAL de la   │
       │  presentación desde tu móvil.          │
       │                                        │
       │  🚫 NO proyectes esta pantalla         │
       │  🚫 NO compartas este código           │
       │  🚫 Escanea solo desde TU dispositivo  │
       │                                        │
       │  El QR expira en 30 segundos.          │
       │                                        │
       │  [❌ Cancelar]  [✅ Generar QR]        │
       └────────────────────────────────────────┘

3. Si acepta → POST /api/generar_codigo_emparejamiento.php
   └─► Genera QR/código temporal (30s)
       └─► Modal muestra QR (NO proyectado)

4. Docente escanea desde SU móvil
   └─► Móvil abre control-movil.php con QR data
       ├─ Valida QR no expirado
       ├─ ¿Tiene sesión autenticada?
       │  ├─ SÍ → Vinculación directa
       │  └─ NO → Pide credenciales → Vinculación
       │
       └─► POST /api/vincular_proyeccion.php
           ├─ Valida autenticación
           ├─ Crea vinculación
           └─ Móvil entra en modo control

5. ✅ Control móvil activo
   ├─ Móvil: Interfaz de control completa
   └─ PC: Continúa en presentador.php (o cambia a proyección fullscreen)
```

#### Seguridad Implementada

✅ **Ocultación por defecto**:
- QR de control NO visible hasta que se solicite
- Requiere acción explícita del docente

✅ **Advertencia clara**:
- Modal de advertencia antes de generar
- Instrucciones de seguridad visibles

✅ **Expiración rápida**:
- QR válido solo 30 segundos
- Evita uso posterior no autorizado

✅ **Autenticación**:
- Móvil debe estar autenticado
- Vinculación requiere sesión válida

---

## 👤 Autenticación en Móvil

Cuando el móvil escanea el QR de control, hay dos escenarios:

### Escenario 1: Móvil ya autenticado ✅

```
Móvil escanea QR
└─► control-movil.php detecta sesión activa (cookie PHPSESSID)
    └─► Vinculación inmediata
        └─► Entra en modo control
```

**No requiere credenciales adicionales.**

### Escenario 2: Móvil sin autenticación 🔐

```
Móvil escanea QR
└─► control-movil.php NO detecta sesión
    └─► Muestra pantalla de login:
        ┌─────────────────────────────┐
        │  Autenticación requerida    │
        │                             │
        │  Email/Usuario:             │
        │  [___________________]      │
        │                             │
        │  Contraseña:                │
        │  [___________________]      │
        │                             │
        │  [Iniciar Sesión]           │
        └─────────────────────────────┘
    └─► Tras login exitoso:
        └─► Vinculación automática
            └─► Entra en modo control
```

**Credenciales**: Las mismas que usa para acceder al dashboard/presentador.

### Persistencia de Sesión

Una vez autenticado en el móvil:
- ✅ Sesión persiste (cookie con duración configurable)
- ✅ Puede vincular múltiples proyecciones sin re-autenticar
- ✅ Puede cerrar sesión manualmente desde el móvil

---

## 📁 Estructura de Archivos Nuevos

```
/simplementi/
│
├── control-movil.php              # Interfaz de control para móvil
├── proyeccion.php                 # Vista de proyección para PC aula
│
├── api/
│   ├── generar_codigo_emparejamiento.php  # Genera QR + código para proyección
│   ├── vincular_proyeccion.php            # Vincula móvil ↔ proyección
│   ├── desvincular_proyeccion.php         # Desvincula desde móvil
│   ├── validar_vinculacion.php            # Valida vinculación activa
│   │
│   ├── control-movil/
│   │   ├── avanzar.php                # Avanzar slide
│   │   ├── retroceder.php             # Retroceder slide
│   │   ├── ir_a_slide.php             # Ir a slide específico
│   │   ├── actualizar_puntero.php     # Actualizar posición puntero
│   │   ├── toggle_puntero.php         # Mostrar/ocultar puntero
│   │   └── estado.php                 # Estado actual (slide, participantes, etc)
│   │
│   └── proyeccion/
│       ├── stream-state.php           # SSE stream para cambios de estado
│       ├── validar_codigo.php         # Validar código ingresado manualmente
│       └── get-state.php              # Obtener estado actual (fallback)
│
├── includes/
│   ├── control-movil/
│   │   ├── header.php                 # Header móvil optimizado
│   │   ├── navegacion.php             # Controles navegación
│   │   ├── panel-interacciones.php    # Panel interacciones móvil
│   │   ├── puntero-touchpad.php       # Interfaz touchpad puntero
│   │   └── scripts.php                # JavaScript optimizado móvil
│   │
│   └── proyeccion/
│       ├── header.php                 # Header proyección
│       ├── pantalla-slide.php         # Mostrar slide actual
│       ├── puntero-canvas.php         # Canvas para puntero
│       └── scripts.php                # JavaScript sincronización
│
├── css/
│   ├── control-movil.css              # Estilos mobile-first
│   └── proyeccion.css                 # Estilos proyección fullscreen
│
└── data/
    └── projection_links/
        └── {pair_code}.json           # Vinculación móvil ↔ proyección
```

---

## 💾 Modelo de Datos

### Archivo: `data/projection_links/{pair_code}.json`

**Propósito**: Almacena la vinculación entre un dispositivo móvil (control) y una proyección.

```json
{
  "pair_code": "A7K9-M2X1",
  "qr_data": {
    "type": "projection_pair",
    "code": "A7K9-M2X1",
    "timestamp": "2025-11-22T10:30:00"
  },
  "created_at": "2025-11-22T10:30:00",
  "expires_at": "2025-11-22T10:30:30",
  "status": "waiting|paired|disconnected",

  "session": {
    "session_id": "ABC123",
    "presentation_id": "demo_test",
    "created_by": "profesor@example.com"
  },

  "mobile_device": {
    "session_token": "mobile_session_xyz789",
    "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0...)",
    "ip": "192.168.1.100",
    "paired_at": "2025-11-22T10:31:15"
  },

  "projection_device": {
    "session_id": "projection_abc456",
    "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...",
    "ip": "192.168.1.50",
    "connected_at": "2025-11-22T10:31:16",
    "screen_resolution": "1920x1080"
  },

  "state": {
    "current_slide": 3,
    "total_slides": 10,
    "pointer": {
      "enabled": true,
      "x": 0.5,
      "y": 0.3
    },
    "last_update": "2025-11-22T10:35:42",
    "last_heartbeat": "2025-11-22T10:35:50"
  }
}
```

### Estados de Vinculación

| Estado | Descripción |
|--------|-------------|
| `waiting` | QR generado, esperando escaneo desde móvil |
| `paired` | Móvil y proyección vinculados correctamente |
| `disconnected` | Conexión perdida (timeout de heartbeat) |

### Ciclo de Vida

```
1. proyeccion.php carga → genera pair_code → estado: "waiting"
   ↓
2. móvil escanea QR → vincular_proyeccion.php → estado: "paired"
   ↓
3. Ambos dispositivos mantienen heartbeat cada 5s
   ↓
4. Si heartbeat falla >15s → estado: "disconnected"
   ↓
5. Usuario cierra proyección → archivo se elimina
```

---

## 🔄 APIs - Especificación Detallada

### 1. Generar Código de Emparejamiento (Proyección)

**Endpoint**: `GET /api/generar_codigo_emparejamiento.php`

**Llamado por**: `proyeccion.php` al cargar (sin autenticación)

**Response**:
```json
{
  "success": true,
  "pair_code": "A7K9-M2X1",
  "qr_data": {
    "type": "projection_pair",
    "code": "A7K9-M2X1",
    "timestamp": "2025-11-22T10:30:00",
    "server_url": "https://simplementi.example.com"
  },
  "qr_image": "data:image/png;base64,iVBORw0KGgoAAAANSUhE...",
  "expires_in": 30
}
```

**Proceso del servidor**:
```php
// 1. Generar código único de 8 caracteres (formato: XXXX-XXXX)
$pair_code = generarCodigoEmparejamiento(); // ej: "A7K9-M2X1"

// 2. Crear archivo de vinculación
$data = [
  'pair_code' => $pair_code,
  'created_at' => date('c'),
  'expires_at' => date('c', time() + 30), // 30 segundos
  'status' => 'waiting',
  'qr_data' => [
    'type' => 'projection_pair',
    'code' => $pair_code,
    'timestamp' => date('c'),
    'server_url' => getServerUrl()
  ]
];

file_put_contents("data/projection_links/{$pair_code}.json", json_encode($data));

// 3. Generar QR como imagen base64
$qr_image = generarQRBase64(json_encode($data['qr_data']));

// 4. Retornar
return json_encode([
  'success' => true,
  'pair_code' => $pair_code,
  'qr_data' => $data['qr_data'],
  'qr_image' => $qr_image,
  'expires_in' => 30
]);
```

---

### 2. Vincular Proyección (desde Móvil)

**Endpoint**: `POST /api/vincular_proyeccion.php`

**Llamado por**: Control móvil tras escanear QR

**Request**:
```json
{
  "qr_data": {
    "type": "projection_pair",
    "code": "A7K9-M2X1",
    "timestamp": "2025-11-22T10:30:00",
    "server_url": "https://simplementi.example.com"
  },
  "session_id": "ABC123"
}
```

**Headers**:
```
Cookie: PHPSESSID=xyz789... (sesión autenticada del docente en móvil)
```

**Response (success)**:
```json
{
  "success": true,
  "pair_code": "A7K9-M2X1",
  "session": {
    "session_id": "ABC123",
    "presentation_id": "demo_test",
    "current_slide": 1,
    "total_slides": 10
  },
  "message": "Proyección vinculada correctamente"
}
```

**Response (error)**:
```json
{
  "success": false,
  "error": "qr_expired|already_paired|invalid_session",
  "message": "El código QR ha expirado. Genera uno nuevo."
}
```

**Proceso del servidor**:
```php
// 1. Validar sesión del móvil
session_start();
if (!isset($_SESSION['auth_test'])) {
  return error('invalid_session', 'No estás autenticado');
}

// 2. Validar QR no expirado (<30s)
$qr_data = json_decode($_POST['qr_data'], true);
$pair_code = $qr_data['code'];
$timestamp = strtotime($qr_data['timestamp']);

if (time() - $timestamp > 30) {
  return error('qr_expired', 'El código QR ha expirado');
}

// 3. Cargar archivo de vinculación
$link_file = "data/projection_links/{$pair_code}.json";
if (!file_exists($link_file)) {
  return error('invalid_code', 'Código inválido');
}

$link = json_decode(file_get_contents($link_file), true);

// 4. Verificar no ya emparejado
if ($link['status'] === 'paired') {
  return error('already_paired', 'Esta proyección ya está vinculada');
}

// 5. Actualizar vinculación
$link['status'] = 'paired';
$link['session'] = [
  'session_id' => $_POST['session_id'],
  'presentation_id' => obtenerPresentacionId($_POST['session_id']),
  'created_by' => $_SESSION['user_email'] ?? 'unknown'
];
$link['mobile_device'] = [
  'session_token' => session_id(),
  'user_agent' => $_SERVER['HTTP_USER_AGENT'],
  'ip' => $_SERVER['REMOTE_ADDR'],
  'paired_at' => date('c')
];

file_put_contents($link_file, json_encode($link));

// 6. Notificar a proyección vía SSE (si está conectada)
notificarProyeccion($pair_code, 'pair_success', $link['session']);

return success([
  'pair_code' => $pair_code,
  'session' => $link['session']
]);
```

---

### 3. Control Móvil - Avanzar Slide

**Endpoint**: `POST /api/control-movil/avanzar.php`

**Request**:
```json
{
  "pair_code": "A7K9-M2X1"
}
```

**Headers**:
```
Cookie: PHPSESSID=xyz789... (sesión autenticada)
```

**Response**:
```json
{
  "success": true,
  "current_slide": 4,
  "total_slides": 10,
  "next_preview": {
    "type": "question",
    "title": "¿Cuál es la capital de Francia?"
  }
}
```

**Proceso del lado del cliente**:
```javascript
// OPTIMIZACIÓN: Actualización optimista
function avanzarSlide() {
  // 1. Actualizar UI inmediatamente (optimistic update)
  currentSlide++;
  actualizarVistaLocal();

  // 2. Enviar al servidor en background
  fetch('/api/control-movil/avanzar.php', {
    method: 'POST',
    body: JSON.stringify({pair_code: pairCode})
  })
  .then(res => res.json())
  .then(data => {
    // 3. Sincronizar si hubo cambios desde otros dispositivos
    if (data.current_slide !== currentSlide) {
      currentSlide = data.current_slide;
      actualizarVistaLocal();
    }
  });
}
```

---

### 4. Actualizar Puntero

**Endpoint**: `POST /api/control-movil/actualizar_puntero.php`

**Request**:
```json
{
  "pair_code": "A7K9-M2X1",
  "x": 0.5,
  "y": 0.3,
  "enabled": true
}
```

**Response**:
```json
{
  "success": true
}
```

**Optimización del lado del cliente**:
```javascript
// Throttling: Solo enviar cada 50ms
let lastPointerSend = 0;
const POINTER_THROTTLE = 50; // ms

function updatePointer(x, y) {
  const now = Date.now();

  // Actualizar canvas local inmediatamente (0 latencia)
  drawPointerLocal(x, y);

  // Enviar al servidor solo si pasó el throttle
  if (now - lastPointerSend >= POINTER_THROTTLE) {
    lastPointerSend = now;

    fetch('/api/control-movil/actualizar_puntero.php', {
      method: 'POST',
      body: JSON.stringify({
        pair_code: pairCode,
        x: x / window.innerWidth,  // Normalizado 0-1
        y: y / window.innerHeight,
        enabled: true
      })
    });
  }
}
```

---

### 5. Stream de Estado (Proyección)

**Endpoint**: `GET /api/proyeccion/stream-state.php?pair_code=A7K9-M2X1`

**Tecnología**: Server-Sent Events (SSE)

**Response Stream**:
```
event: pair_success
data: {"session_id": "ABC123", "presentation_id": "demo_test", "current_slide": 1}

event: slide_change
data: {"slide": 4, "type": "pdf"}

event: pointer_update
data: {"x": 0.5, "y": 0.3, "enabled": true}

event: interaction
data: {"type": "raise_hand", "count": 3}

event: heartbeat
data: {"timestamp": "2025-11-22T10:35:50"}
```

**Cliente (Proyección)**:
```javascript
const eventSource = new EventSource(
  '/api/proyeccion/stream-state.php?pair_code=' + pairCode
);

// Evento inicial cuando móvil escanea QR
eventSource.addEventListener('pair_success', (e) => {
  const data = JSON.parse(e.data);
  iniciarProyeccion(data.session_id, data.presentation_id);
});

eventSource.addEventListener('slide_change', (e) => {
  const data = JSON.parse(e.data);
  cambiarSlide(data.slide);
});

eventSource.addEventListener('pointer_update', (e) => {
  const data = JSON.parse(e.data);
  actualizarPuntero(data.x, data.y);
});
```

---

### 6. Estado Actual (Control Móvil)

**Endpoint**: `GET /api/control-movil/estado.php?pair_code=A7K9-M2X1`

**Response**:
```json
{
  "success": true,
  "session": {
    "current_slide": 4,
    "total_slides": 10,
    "participants_count": 15
  },
  "interactions": {
    "hands_raised": [
      {
        "id": "p001",
        "nombre": "Juan Pérez",
        "timestamp": "2025-11-22T10:35:00"
      }
    ],
    "questions": [
      {
        "id": "q123",
        "participante": "María García",
        "question": "¿Qué es MVC?",
        "timestamp": "2025-11-22T10:34:00",
        "respondida": false
      }
    ],
    "understanding": {
      "confused": 2,
      "understood": 13
    },
    "recent_reactions": ["👍", "❤️", "👏"]
  },
  "next_preview": {
    "type": "question",
    "title": "Pregunta 3"
  }
}
```

---

## 🎨 Interfaces de Usuario

### Control Móvil (Mobile-First)

```
┌─────────────────────────┐
│  ← SimpleMenti Control  │ ← Header fijo
├─────────────────────────┤
│                         │
│   ┌─────────────────┐   │
│   │                 │   │ ← Preview slide actual
│   │   Slide 4/10    │   │   (miniatura)
│   │                 │   │
│   └─────────────────┘   │
│                         │
│   ◄──  [■]  ──►         │ ← Controles navegación
│                         │   (grandes, touch-friendly)
│                         │
├─────────────────────────┤
│ 🎯 Puntero Laser   [ON] │ ← Toggle puntero
├─────────────────────────┤
│                         │
│  👥 15 participantes    │
│                         │
│  🙋 Manos (3)           │ ← Tabs interacciones
│    • Juan Pérez         │
│    • Ana López          │
│    • Carlos Ruiz        │
│                         │
│  💬 Preguntas (2)       │
│    María: "¿Qué es...?" │
│    [Responder] [✓]      │
│                         │
│  📊 Comprensión         │
│    😕 2  |  😊 13       │
│                         │
└─────────────────────────┘
```

### Proyección (Fullscreen)

```
┌──────────────────────────────────────────┐
│                                          │
│                                          │
│                                          │
│            SLIDE CONTENT                 │
│                                          │
│              ⊙ ← Puntero laser           │
│                                          │
│                                          │
│                                          │
│                                          │
└──────────────────────────────────────────┘
     ↑
     Sin controles, solo contenido
```

---

## ⚡ Optimizaciones Cliente-Servidor

### 1. Procesamiento del Lado del Cliente

**Responsabilidades del Cliente (Móvil)**:
- ✅ Rendering de preview de slides (desde caché)
- ✅ Animaciones de transición
- ✅ Validación de input
- ✅ Cálculo de coordenadas normalizadas del puntero
- ✅ Throttling de eventos touch/mouse
- ✅ Caché de interacciones recientes (5 min)
- ✅ Compresión de datos antes de enviar

**Responsabilidades del Cliente (Proyección)**:
- ✅ Rendering de slides fullscreen
- ✅ Interpolación suave del puntero (entre updates)
- ✅ Precarga de siguiente slide
- ✅ Transiciones CSS hardware-accelerated
- ✅ Canvas rendering del puntero

**Responsabilidades del Servidor (Mínimas)**:
- ❌ Solo validación de token
- ❌ Actualización de estado en JSON
- ❌ Broadcast de cambios (SSE)
- ❌ NO renderiza HTML innecesario
- ❌ NO procesa imágenes en cada request

### 2. Estrategias de Caché

```javascript
// Service Worker para caché agresivo
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open('presentation-v1').then((cache) => {
      return cache.addAll([
        '/css/control-movil.css',
        '/js/control-movil.js',
        '/img/slides/*.png',  // Todas las slides
        // Recursos estáticos
      ]);
    })
  );
});

// Cache-first para slides
self.addEventListener('fetch', (event) => {
  if (event.request.url.includes('/img/slides/')) {
    event.respondWith(
      caches.match(event.request).then((response) => {
        return response || fetch(event.request);
      })
    );
  }
});
```

### 3. Compresión de Payload

```javascript
// Comprimir estado antes de enviar
function comprimirEstado(estado) {
  return {
    s: estado.current_slide,        // slide
    p: estado.participants_count,   // participants
    h: estado.hands_raised.length,  // hands
    q: estado.questions.length      // questions
  };
}

// Descomprimir en servidor si es necesario
```

### 4. Actualización Optimista

```javascript
// No esperar respuesta del servidor para actualizar UI
async function avanzarSlide() {
  // 1. Actualizar UI inmediatamente
  currentSlide++;
  renderSlide(currentSlide);

  // 2. Enviar al servidor en background (no await)
  fetch('/api/control-movil/avanzar.php', {
    method: 'POST',
    body: JSON.stringify({token})
  }).catch(err => {
    // 3. Rollback si falla
    currentSlide--;
    renderSlide(currentSlide);
    showError('Error al avanzar');
  });
}
```

---

## 🔒 Seguridad

### 1. Token JWT

```php
// Generación
$payload = [
  'session_id' => $session_id,
  'token' => $token,
  'iat' => time(),
  'exp' => time() + 86400  // 24 horas
];
$jwt = JWT::encode($payload, SECRET_KEY, 'HS256');
```

### 2. Validación de Dispositivo

```php
function validarToken($token) {
  // 1. Verificar token existe
  $file = "data/presentation_tokens/{$token}.json";
  if (!file_exists($file)) return false;

  // 2. Verificar no expiró
  $data = json_decode(file_get_contents($file), true);
  if (strtotime($data['expires_at']) < time()) return false;

  // 3. Verificar JWT
  try {
    JWT::decode($data['jwt'], SECRET_KEY, ['HS256']);
  } catch (Exception $e) {
    return false;
  }

  // 4. Opcional: Verificar IP (puede cambiar en redes móviles)
  // if ($data['mobile_device']['ip'] !== $_SERVER['REMOTE_ADDR']) {
  //   return false;
  // }

  return true;
}
```

### 3. Rate Limiting

```php
// Limitar requests del puntero a 20/segundo
$key = "pointer_rate_{$token}";
$current = apcu_fetch($key) ?: 0;

if ($current > 20) {
  http_response_code(429);
  die(json_encode(['error' => 'Rate limit exceeded']));
}

apcu_store($key, $current + 1, 1); // TTL 1 segundo
```

---

## 🚀 Plan de Implementación

### Fase 1: Backend Core (2-3 días)
- [ ] Sistema de generación de tokens JWT
- [ ] APIs de control móvil (avanzar, retroceder, estado)
- [ ] API de validación de token
- [ ] SSE stream para proyección
- [ ] Modelo de datos (presentation_tokens)

### Fase 2: Vista Proyección (1-2 días)
- [ ] proyeccion.php (entrada con token)
- [ ] Sincronización SSE
- [ ] Rendering de slides fullscreen
- [ ] Canvas para puntero virtual
- [ ] Transiciones suaves

### Fase 3: Control Móvil (2-3 días)
- [ ] control-movil.php (interfaz móvil)
- [ ] Navegación con swipe
- [ ] Panel de interacciones optimizado
- [ ] Touchpad para puntero
- [ ] Preview de slides

### Fase 4: Integración (1 día)
- [ ] Botón "Activar Control Móvil" en presentador.php
- [ ] Modal con QR + ID
- [ ] Pruebas de sincronización
- [ ] Manejo de desconexiones

### Fase 5: Optimización (1-2 días)
- [ ] Service Worker para caché
- [ ] Throttling de eventos
- [ ] Compresión de payload
- [ ] Lazy loading de slides
- [ ] Performance testing

### Fase 6: Testing & Documentación (1 día)
- [ ] Pruebas en diferentes dispositivos
- [ ] Documentación de usuario
- [ ] Video tutorial

**Total estimado**: 8-12 días de desarrollo

---

## 📊 Métricas de Éxito

- **Latencia**: < 200ms entre acción móvil y actualización proyección
- **Uso de datos**: < 50KB/min en móvil durante presentación activa
- **Carga servidor**: < 5% CPU con 10 presentaciones simultáneas
- **Compatibilidad**: iOS 12+, Android 8+, Chrome/Safari/Firefox
- **Offline**: Funcionar hasta 30s sin conexión (caché)

---

## 🔮 Futuras Mejoras

1. **WebRTC**: Usar WebRTC DataChannel para latencia < 50ms
2. **Multi-presentador**: Permitir co-presentadores con permisos
3. **Grabación**: Grabar sesión con timestamps de interacciones
4. **Analytics**: Dashboard de engagement en tiempo real
5. **Dibujo colaborativo**: Estudiantes pueden anotar desde sus móviles
6. **Modo Picture-in-Picture**: Ver proyección + controles simultáneamente
7. **Gestos avanzados**: Control por voz, gestos con acelerómetro
8. **Modo offline**: Sincronización diferida cuando se recupere conexión

---

## 📝 Notas Técnicas

### Por qué SSE en lugar de WebSockets

- ✅ Más simple de implementar en PHP
- ✅ Unidireccional (servidor → cliente) suficiente para proyección
- ✅ Reconexión automática
- ✅ Compatible con proxies/firewalls
- ✅ Menor overhead que WebSocket para este caso de uso

### Alternativas evaluadas

| Tecnología | Pros | Contras | Decisión |
|-----------|------|---------|----------|
| WebSockets | Bidireccional, baja latencia | Complejo en PHP, requiere Ratchet/Socket.io | ❌ No por ahora |
| SSE | Simple, reconexión auto | Solo servidor→cliente | ✅ Ideal para proyección |
| Long Polling | Compatible | Alta latencia, muchos requests | ❌ Obsoleto |
| Firebase | Tiempo real robusto | Dependencia externa, costo | ❌ Evitar dependencias |

---

## 🎓 Conclusión

Este sistema transforma SimpleMenti en una herramienta de presentación moderna comparable a soluciones comerciales, con la ventaja de:

- **Control total**: Sin dependencias de terceros
- **Privacidad**: Datos en servidor propio
- **Flexibilidad**: Personalizable a necesidades educativas
- **Costo**: Sin suscripciones ni límites artificiales

La arquitectura propuesta balancea rendimiento, simplicidad y escalabilidad, manteniendo la filosofía del proyecto de ser una solución open-source accesible.
