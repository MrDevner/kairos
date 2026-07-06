# Sistema de gestión de errores de servidor + Sistema de tickets

Especificación funcional y técnica de dos módulos de Galeon, para usar como referencia al portarlos/reimplementarlos en otra aplicación.

---

## 1. Sistema de gestión de errores del servidor (admin)

### 1.1 Objetivo

Capturar automáticamente **toda excepción no controlada (HTTP 500)** que ocurra en producción, deduplicarla, guardarla en base de datos con contexto de diagnóstico, y darle a los administradores un panel para triage (asignar, agregar notas, marcar como mitigado/solucionado). Al usuario final se le muestra una pantalla de error con un **código de correlación** que puede usar para reportar el problema, sin exponerle el stack trace (salvo que sea admin).

### 1.2 Piezas y flujo

```
Exception no controlada
   → Laravel Exception Handler (app/Exceptions/Handler.php)
       → register(): callback reportable()
           → shouldSkip($e)?  (filtra 4xx / validación / auth / 404)
           → ServerErrorLogger::log($e, $request)   [app/Support/ServerErrorLogger.php]
               → fingerprint = sha256(clase + archivo + línea + mensaje)
               → busca ServerError existente con ese fingerprint y status abierto/en_revision
                    - si existe → incrementa occurrences_count, actualiza last_occurrence_at y correlation_id
                    - si no existe → crea un registro nuevo (UUID)
               → sanitiza params sensibles (password, token, secret, api_key, cvv, etc → [REDACTED])
               → guarda correlation_id en $request->attributes para que la vista lo use
       → render(): si status 500 y no es respuesta JSON → sirve view errors.500
           - pasa correlationId siempre
           - pasa exception (objeto completo) SOLO si el usuario actual tiene permiso administrador
```

Puntos clave de diseño:

- **Deduplicación por fingerprint**: agrupa ocurrencias repetidas del mismo error (misma excepción, mismo archivo/línea/mensaje) en un solo registro, incrementando un contador en vez de crear una fila por request. Si el error ya fue marcado `mitigado`/`solucionado`, una nueva ocurrencia crea un registro **nuevo** (no reabre el viejo), porque el filtro busca solo `abierto`/`en_revision`.
- **El logger nunca puede romper la respuesta al usuario**: todo el cuerpo de `log()` está en un `try/catch` silencioso.
- **Filtrado de ruido**: no se registran `ValidationException`, `AuthenticationException`, `AuthorizationException`, `ModelNotFoundException` ni excepciones HTTP con status < 500 — solo errores reales de servidor.
- **Sanitización de datos sensibles** antes de persistir query/post params.
- **Correlation ID vs Fingerprint**: el fingerprint agrupa errores iguales; el correlation ID es único por *ocurrencia* (UUID nuevo en cada request) y es lo único que se le muestra al usuario final, para poder correlacionar su reporte manual con el registro interno sin exponer detalles.

### 1.3 Modelo de datos (`server_errors`)

Tabla con PK **UUID** (no autoincremental), pensada para poder generarse en el logger sin ida y vuelta a la DB:

| Campo | Tipo | Notas |
|---|---|---|
| `id` | uuid PK | generado en `boot()` del modelo si viene vacío |
| `correlation_id` | uuid unique | de la última ocurrencia |
| `endpoint` | string(500) | URL completa |
| `http_method` | string(10) | |
| `user_id` | FK nullable → users | `nullOnDelete` |
| `ip_address` | string(45) | |
| `user_agent` | text | |
| `request_params` | json | sanitizado |
| `error_message`, `error_class`, `stack_trace`, `file_path`, `line_number` | | detalle de la excepción |
| `error_fingerprint` | char(64), indexado | hash de deduplicación |
| `occurrences_count` | int default 1 | |
| `status` | enum: `abierto`, `en_revision`, `mitigado`, `solucionado` | |
| `assigned_to` | FK nullable → users | |
| `notes` | json | array de `{id, user_id, user_name, content, created_at}` — bitácora tipo timeline, no tabla aparte |
| `last_occurrence_at`, `resolved_at` | timestamps | |

Índices en `error_fingerprint`, `status`, `created_at`, `last_occurrence_at` (todas usadas para filtros del panel).

### 1.4 Panel de administración (`Admin\ServerErrorController`, solo admin)

- **Index**: tabs `activos` (abierto+en_revision) / `cerrados` (mitigado+solucionado), filtros por `status`, `method`, rango de fechas (`desde`/`hasta`, default últimos 30 días) y búsqueda libre (`q` sobre mensaje/endpoint/clase/correlation_id). Paginado.
- **KPIs en el dashboard**: total abiertos, total activos, ocurrencias en las últimas 24h, endpoint con más ocurrencias en 24h, MTTR (tiempo medio de resolución en minutos, calculado con `AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at))`).
- **Show**: detalle completo (stack trace, request params, usuario afectado).
- **Update**: cambia `status` (si pasa a mitigado/solucionado setea `resolved_at`; si vuelve a abierto lo limpia), reasigna `assigned_to`, y permite **agregar notas** (append a un array JSON con autor y timestamp — no hay edición/borrado de notas individuales).
- **Destroy**: borrado duro del registro.
- Todas las acciones quedan protegidas por un check `soloAdmin()` que exige el permiso wildcard de administrador, no roles de soporte genéricos.

### 1.5 Vista pública `errors/500.blade.php`

- Siempre muestra: código de correlación (copiable al portapapeles con un botón), fecha/hora, URL que falló, user agent.
- Sección colapsable "¿Qué datos incluir en el reporte?" para guiar al usuario a dar contexto útil.
- Sección de debug (clase, mensaje, archivo:línea, stack trace completo) **solo si es admin** — así un admin que dispara el error en producción puede diagnosticar in situ sin logs de servidor.
- Bloque de "canales de contacto" (abrir ticket / enviar mail) pensado para integrarse con el sistema de tickets.

---

## 2. Sistema de tickets (soporte interno)

### 2.1 Objetivo

Mesa de ayuda interna: cualquier "personal" (no alumno) puede abrir tickets de soporte; un grupo de "soporte"/administradores los atiende, los categoriza, los prioriza y los cierra. Incluye mensajería tipo chat con adjuntos, reasignación con motivo obligatorio, y un mecanismo de **cierre colaborativo** (todas las partes deben aprobar el cierre, salvo que soporte fuerce el estado).

### 2.2 Modelos y tablas

| Modelo | Tabla | Rol |
|---|---|---|
| `Ticket` | `tickets` | entidad principal |
| `TicketCategoria` | `ticket_categorias` | categorías dinámicas (slug/nombre), con soft delete, administrables desde un CRUD propio |
| `TicketMensaje` | `ticket_mensajes` | hilo de conversación del ticket |
| `TicketAdjunto` | `ticket_adjuntos` | adjuntos del ticket inicial |
| `TicketMensajeAdjunto` | `ticket_mensaje_adjuntos` | adjuntos por mensaje |
| `TicketLectura` | `ticket_lecturas` | marca de "leído" por usuario y ticket (para el badge de no-leídos) |
| `TicketSolicitudResolucion` | `ticket_solicitudes_resolucion` | solicitudes de cierre pendientes de aprobación por participante |

**`tickets`**: `titulo`, `descripcion`, `categoria` (string libre, validado contra `TicketCategoria::categorias()`), `estado` (`abierto`/`en_proceso`/`resuelto`/`cerrado`), `prioridad` (`baja`/`media`/`alta`/`urgente`), `id_creador`, `id_abierto_por` (quien lo abrió físicamente — puede diferir del creador si soporte lo abre "en nombre de" alguien), `id_asignado_a`, más columnas agregadas después vía migraciones incrementales: `fecha_limite`, `fecha_cierre`, `categoria_cambio_motivo`, `id_categoria_cambiada_por`.

**`ticket_solicitudes_resolucion`**: por cada intento de cierre se crea **una fila por participante** (`id_ticket`, `id_user`, `es_solicitante`, `aprobado_at`, `estado_propuesto`), con unique `(id_ticket, id_user)`. Se borran todas cuando el ticket efectivamente se cierra o se cancela la solicitud.

### 2.3 Permisos y roles

- `esSoporte()`: admin wildcard o permiso `soporte` de lectura.
- `puedeVerTodos()`: igual que soporte (ve todos los tickets, no solo los propios).
- `puedeCrearTicket()`: soporte, o cualquier usuario "personal" (no alumno).
- `esParticipeDelTicket()`: creador, asignado, o alguien que escribió al menos un mensaje en el ticket.
- Un usuario sin `puedeVerTodos()` solo ve/opera sobre tickets donde participa.

### 2.4 Flujo funcional

**Creación** (`store`): valida título/descripción/categoría/prioridad, permite adjuntos (máx 10MB c/u). Soporte puede designar un `id_creador` distinto de sí mismo (para abrir en nombre de otro). Loguea con auditoría de actividad.

**Listado** (`index`): si no puede ver todos, se filtra a tickets propios/asignados/participados. Orden: por estado (abierto > en_proceso > resuelto > cerrado) y luego por prioridad (urgente > alta > media > baja), y por fecha.

**Detalle** (`show`): al abrir, hace upsert de la marca de lectura (`TicketLectura`) — esto es lo que alimenta el contador de no-leídos.

**Mensajería** (`storeMessage`): cualquier participante puede escribir mientras el ticket no esté `cerrado`. Si soporte responde a un ticket `abierto`, pasa automáticamente a `en_proceso`. Si es un participante normal el que escribe, solo se hace `touch()` (actualiza `updated_at`, que es lo que dispara el "no leído" para el resto).

**Actualización por soporte** (`update`): cambia estado/prioridad/fecha límite/asignado/creador. Si se reasigna un ticket que ya tenía asignado, o se cambia el creador, **exige un motivo** (mínimo 5 caracteres) y lo audita. Al pasar a `resuelto`/`cerrado` setea `fecha_cierre`; si vuelve atrás, la limpia.

**Tomar ticket** (`tomar`): atajo para que un miembro de soporte se autoasigne y el ticket pase de `abierto` a `en_proceso`.

**Cambio de categoría** (`cambiarCategoria`): puede hacerlo soporte o el usuario actualmente asignado; exige motivo (mínimo 10 caracteres) y deja rastro de quién cambió y por qué.

**Resolución colaborativa** (lo más particular del sistema):

1. `solicitarResolucion`: cualquier participante (o soporte) pide cerrar. Se crea una fila `TicketSolicitudResolucion` por cada participante del ticket (creador + quien lo abrió + asignado + todo el que escribió un mensaje, únicos). La fila del solicitante queda auto-aprobada. Soporte puede fijar de una vez el estado propuesto (`resuelto` o `cerrado`); si no es soporte, queda pendiente y se decide al momento de la aprobación final.
   - Si el ticket tiene un solo participante, se cierra **inmediatamente** sin esperar aprobaciones.
   - Solo puede haber una solicitud activa por ticket a la vez.
2. `aprobarResolucion`: cada participante aprueba su propia fila pendiente. Cuando el conteo de pendientes llega a 0, el ticket se cierra con el estado propuesto más reciente definido (o `resuelto` por defecto), se borran las solicitudes, y se audita.
3. `cancelarResolucion`: el solicitante original o soporte puede abortar el proceso completo, borrando todas las filas de solicitud.

**Adjuntos**: se guardan en storage (separados para ticket y para mensajes), se descargan validando que el usuario sea participante o tenga visibilidad total.

**Notificaciones (badge de no leídos)** (`Ticket::contarNoLeidosParaUsuario`): cuenta tickets abiertos/en_proceso donde el usuario participa (o, si es admin/soporte, también los **sin asignar**) y que fueron creados/modificados después de su última lectura registrada — usando un `whereNotExists` correlacionado por SQL en vez de traer todo a PHP. Suma también los tickets con una solicitud de resolución pendiente de su aprobación. Este contador alimenta el badge del navbar.

**Auditoría**: cada acción relevante (creación, cambio de estado, reasignación, cambio de categoría, mensajes, resoluciones) se registra en un log de actividad, con autor, entidad afectada y propiedades adicionales (motivo, valores antes/después) cuando aplica.

### 2.5 Categorías de tickets (CRUD aparte, solo admin)

`TicketCategoria` con soft delete: crear (slug autogenerado, rechaza duplicados de slug incluso entre borrados), renombrar, eliminar (soft) y restaurar. Las categorías activas se cachean en memoria para poblar selects y validar el campo `categoria` del ticket.

### 2.6 Rutas relevantes

```
GET    /tickets                          index
GET    /tickets/crear                    create
POST   /tickets                          store
GET    /tickets/categorias               categorías (admin)
POST|PUT|DELETE /tickets/categorias...   CRUD categorías
GET    /tickets/{id}                     show
PUT    /tickets/{id}                     update
GET    /tickets/{id}/tomar               tomar
POST   /tickets/{id}/mensajes            storeMessage
GET    /tickets/adjuntos/{id}            descargarAdjunto
GET    /tickets/mensajes/adjuntos/{id}   descargarAdjuntoMensaje
POST   /tickets/{id}/categoria           cambiarCategoria
POST   /tickets/{id}/solicitar-resolucion
POST   /tickets/{id}/aprobar-resolucion
POST   /tickets/{id}/cancelar-resolucion
```

---

## 3. Consideraciones para portar esto a otra aplicación

Ambos sistemas están razonablemente desacoplados del dominio educativo original — lo único realmente atado al proyecto de origen es la capa de permisos y el helper de sistema/usuario actual. Al implementarlos en otra app conviene indicar explícitamente:

- **Sistema de errores**: prácticamente plug-and-play para cualquier Laravel — solo depende de: (1) un exception handler que ya exista o se cree, (2) una forma de saber "¿es admin?" (reemplazar el chequeo de permisos por lo que use la nueva app: rol, gate, policy, etc.), y (3) decidir si se quiere UUID o bigint como PK de `server_errors` (UUID evita una consulta extra pero requiere el cast de UUID en el modelo).
- **Sistema de tickets**: portable, pero hay que redefinir explícitamente en la app destino: quién puede crear tickets (equivalente a "personal"), quién es "soporte", y quién es "admin total" (wildcard). El mecanismo de resolución colaborativa (n-de-n aprobaciones) es opcional/desacoplable si la otra app solo quiere un flujo simple de estado.
- Ambos usan un paquete de audit log (tipo `spatie/laravel-activitylog`) — si la app destino no lo tiene, hay que instalarlo o sustituir esas llamadas por el mecanismo de logging que ya use.
- El storage de adjuntos usa el disco default de Laravel — revisar configuración de disco/S3 si la nueva app no usa filesystem local.
- La deduplicación por fingerprint y el badge de no-leídos con subquery correlacionada son los dos detalles de implementación menos obvios y más fáciles de omitir por accidente si alguien reimplementa "a ojo" — vale la pena preservarlos explícitamente en el prompt de implementación.
