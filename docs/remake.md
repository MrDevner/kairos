# Rediseño visual de Kairos — Paleta institucional "Opción 1" (Azul + Verde-agua)

## Contexto
Kairos es un sistema de control de asistencia biométrico desarrollado en Laravel con Bootstrap 5.
Necesito rediseñar el layout principal (`app.blade.php`) y todos los estilos asociados para adoptar
una nueva paleta de colores institucional, más clara, profesional y agradable a la vista.

## Paleta de colores a implementar

| Uso                          | Color       | Variable sugerida       |
|-------------------------------|-------------|--------------------------|
| Primario (azul institucional) | `#2E5EAA`   | `--bs-primary`           |
| Primario claro (fondos/cards) | `#E8F1FC`   | `--kairos-primary-light` |
| Secundario (verde-agua, OK)   | `#4CAF93`   | `--bs-secondary`         |
| Acento (naranja, alertas)     | `#F2A65A`   | `--kairos-accent`        |
| Fondo general                 | `#F7F9FC`   | `--bs-light` / `--kairos-bg` |
| Texto principal               | `#2B2D42`   | `--bs-dark` / `--kairos-text` |

## Fase 1: Auditoría (no tocar código todavía)
1. Localizar y listar:
    - `resources/views/layouts/app.blade.php` (o el layout principal equivalente)
    - Todos los archivos CSS/SCSS custom (no los de vendor/Bootstrap)
    - Cualquier archivo de configuración de Tailwind/Bootstrap si existe (`tailwind.config.js`, variables SCSS de Bootstrap)
    - Componentes Blade reutilizables que definan colores inline (badges de estado, alerts, sidebar, navbar)
2. Reportarme un resumen de:
    - Qué colores hardcodeados existen actualmente (hex, rgb, clases Bootstrap tipo `bg-primary`, `text-danger`, etc.)
    - Si el proyecto usa Bootstrap vía CDN, npm compilado con Vite, o SCSS custom
    - Estructura del layout actual (navbar, sidebar, footer, breadcrumbs, etc.)

## Fase 2: Definición de variables globales
1. Crear (o actualizar) un archivo de variables CSS en `resources/css/variables.css` (o el equivalente según cómo compile el proyecto) con las variables de la tabla de arriba.
2. Si el proyecto usa SCSS de Bootstrap, sobreescribir las variables de Bootstrap ANTES del `@import` de Bootstrap (`$primary`, `$secondary`, `$light`, `$dark`, `$body-bg`, etc.) en lugar de solo usar CSS variables, para que los componentes de Bootstrap (botones, badges, alerts) hereden el cambio automáticamente.
3. Definir también:
    - Un color de estado "ausente/error" (mantener rojo estándar `#DC3545` o similar, ajustado a la paleta si es necesario)
    - Sombras suaves (`box-shadow`) coherentes con un estilo "claro y profesional" (ej: `0 2px 8px rgba(46, 94, 170, 0.08)`)
    - Border-radius consistente para cards y botones (sugerido: `0.5rem`–`0.75rem`)

## Fase 3: Rediseño de `app.blade.php`
1. Navbar:
    - Fondo blanco o `--kairos-primary-light`, con el logo y texto en `--bs-primary`
    - Sombra sutil en vez de borde duro
    - Estados hover con transición suave (`transition: all 0.2s ease`)
2. Sidebar (si existe):
    - Fondo `--kairos-bg`
    - Ítem activo con fondo `--kairos-primary-light` y texto/icono en `--bs-primary`
    - Ítems inactivos en gris neutro, hover con fondo muy claro
3. Cards y contenedores principales:
    - Fondo blanco, borde sutil o sin borde + sombra suave
    - Headers de card con `--kairos-primary-light` de fondo cuando corresponda
4. Tipografía:
    - Verificar fuente actual; si es la default de Bootstrap, sugerir (pero no forzar) una fuente tipo "Inter" o "Public Sans" vía Google Fonts para reforzar la imagen profesional
    - Texto principal en `--kairos-text`, nunca negro puro

## Fase 4: Componentes de estado (clave para Kairos)
Como el sistema maneja asistencia, es importante mapear bien los estados visuales:
- **Presente / A tiempo** → `--bs-secondary` (verde-agua), fondo claro derivado
- **Tardanza** → `--kairos-accent` (naranja)
- **Ausente / Falta** → rojo estándar, sin cambios drásticos
- **Neutro / Pendiente** → gris

Actualizar badges, chips o indicadores de estado en:
- Listados de asistencia
- Dashboard/resumen
- Reportes

## Fase 5: Botones y formularios
1. Botones primarios: `--bs-primary`, hover levemente más oscuro (`darken 8-10%`)
2. Botones secundarios/outline: usar `--bs-secondary`
3. Inputs y selects: bordes en gris claro, focus en `--bs-primary` con `box-shadow` suave (glow), no el azul default de Bootstrap

## Fase 6: Verificación
1. Revisar contraste de accesibilidad (WCAG AA mínimo) entre texto y fondo, especialmente en botones y badges
2. Verificar que no queden clases Bootstrap hardcodeadas (`bg-primary`, `text-primary`, etc.) que no estén tomando las variables sobreescritas
3. Confirmar compilación correcta (`npm run build` o `npm run dev` según Vite)
4. Listar todos los archivos modificados al final

## Restricciones
- No modificar la lógica de negocio, controladores ni rutas
- No romper la responsividad existente
- Mantener compatibilidad con los componentes Blade actuales, solo modificar estilos/clases
- Si algún color hardcodeado está en un `.blade.php` específico (no en CSS), reemplazarlo por la clase o variable correspondiente, no dejarlo hardcodeado

## Entregable esperado
- Variables de color centralizadas y documentadas
- `app.blade.php` actualizado
- Todos los estilos custom actualizados para reflejar la nueva paleta
- Resumen final de archivos tocados y decisiones tomadas
