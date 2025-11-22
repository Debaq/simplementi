# Diseño Técnico: Sistema de Presentación con Control Móvil

## 📱 Visión General

Sistema de presentación dual que permite al docente controlar una proyección desde su dispositivo móvil mediante un ID de presentación especial, optimizado para reducir carga del servidor mediante procesamiento del lado del cliente.

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

### Flujo de Activación

```
1. Docente en presentador.php
   └─► Click "Activar Control Móvil"
       └─► POST /api/generar_id_presentacion.php
           ├─ Genera ID único: PRE-X7K9M
           ├─ Crea token JWT
           ├─ Guarda en data/presentation_tokens/ABC123.json
           └─ Muestra QR + ID en pantalla

2. Docente escanea QR desde móvil
   └─► Abre: control-movil.php?token=PRE-X7K9M
       └─► Valida token
           └─► Muestra interfaz de control

3. PC en aula
   └─► Navega a: proyeccion.php
       └─► Ingresa ID: PRE-X7K9M
           └─► Valida token
               └─► Inicia modo proyección fullscreen
                   └─► Conecta SSE para sincronización
```

---

## 📁 Estructura de Archivos Nuevos

```
/simplementi/
│
├── control-movil.php              # Interfaz de control para móvil
├── proyeccion.php                 # Vista de proyección para PC aula
│
├── api/
│   ├── generar_id_presentacion.php    # Genera ID + token JWT
│   ├── validar_token_presentacion.php # Valida token activo
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
    └── presentation_tokens/
        └── {session_id}.json          # Token + metadata sesión
```

---

## 💾 Modelo de Datos

### Archivo: `data/presentation_tokens/{session_id}.json`

```json
{
  "session_id": "ABC123",
  "presentation_id": "demo_test",
  "token": "PRE-X7K9M",
  "jwt": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "created_at": "2025-11-22T10:30:00",
  "expires_at": "2025-11-23T10:30:00",
  "mobile_device": {
    "user_agent": "Mozilla/5.0 (iPhone...)",
    "ip": "192.168.1.100",
    "connected_at": "2025-11-22T10:31:15"
  },
  "projection_device": {
    "user_agent": "Mozilla/5.0 (Windows...)",
    "ip": "192.168.1.50",
    "connected_at": "2025-11-22T10:32:00"
  },
  "state": {
    "current_slide": 3,
    "pointer": {
      "enabled": true,
      "x": 0.5,
      "y": 0.3
    },
    "last_update": "2025-11-22T10:35:42"
  }
}
```

---

## 🔄 APIs - Especificación Detallada

### 1. Generar ID de Presentación

**Endpoint**: `POST /api/generar_id_presentacion.php`

**Request**:
```json
{
  "codigo_sesion": "ABC123"
}
```

**Response**:
```json
{
  "success": true,
  "token": "PRE-X7K9M",
  "qr_url": "https://example.com/control-movil.php?token=PRE-X7K9M",
  "expires_at": "2025-11-23T10:30:00"
}
```

---

### 2. Control Móvil - Avanzar Slide

**Endpoint**: `POST /api/control-movil/avanzar.php`

**Request**:
```json
{
  "token": "PRE-X7K9M"
}
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
    body: JSON.stringify({token: presentationToken})
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

### 3. Actualizar Puntero

**Endpoint**: `POST /api/control-movil/actualizar_puntero.php`

**Request**:
```json
{
  "token": "PRE-X7K9M",
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
        token: presentationToken,
        x: x / window.innerWidth,  // Normalizado 0-1
        y: y / window.innerHeight,
        enabled: true
      })
    });
  }
}
```

---

### 4. Stream de Estado (Proyección)

**Endpoint**: `GET /api/proyeccion/stream-state.php?token=PRE-X7K9M`

**Tecnología**: Server-Sent Events (SSE)

**Response Stream**:
```
event: slide_change
data: {"slide": 4, "type": "pdf"}

event: pointer_update
data: {"x": 0.5, "y": 0.3, "enabled": true}

event: interaction
data: {"type": "raise_hand", "count": 3}
```

**Cliente (Proyección)**:
```javascript
const eventSource = new EventSource(
  '/api/proyeccion/stream-state.php?token=' + token
);

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

### 5. Estado Actual (Control Móvil)

**Endpoint**: `GET /api/control-movil/estado.php?token=PRE-X7K9M`

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
