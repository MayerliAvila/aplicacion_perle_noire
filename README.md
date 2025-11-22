La rama producción contiene la versión estable, optimizada y lista para uso real del sistema administrativo de Perle Noire.
Aquí se almacena el código final que ha superado todas las etapas de desarrollo, pruebas e integración.

Esta rama representa el estado actual del sistema tal como se encuentra implementado para los usuarios finales.

🏆 Objetivo de la Rama

Garantizar que el entorno productivo:

Sea estable y libre de errores conocidos.

Refleje la última versión aprobada del sistema.

Permita un funcionamiento confiable para clientes, colaboradores y administradores.

Mantenga la integridad de los procesos de:

Agendamiento de citas

Gestión de servicios y personal

Facturación

Control de disponibilidad

Consultas y reportes administrativos

🔐 Características de Producción

Código completamente probado y validado.

Seguridad reforzada (manejo de credenciales, cifrado, roles, etc.).

Rendimiento optimizado para uso real.

Configuración lista para despliegue en servidores.

Integración con bases de datos reales y servicios externos.

🔄 Flujo de Integración

El flujo de trabajo hacia esta rama sigue un proceso riguroso:

Desarrollo → se crea o mejora funcionalidad en develop.

Pruebas → la funcionalidad pasa a prueba para validación.

Revisión y aprobación → se verifica que cumple con estándares técnicos.

Merge a producción → solo cuando está 100% estable.

Despliegue final → versión activa para los usuarios.

🛑 Restricciones y Buenas Prácticas

No se debe desarrollar directamente en esta rama.

Todos los cambios deben pasar por revisión y pruebas previas.

No debe contener código temporal, experimental o sin validar.

Se recomienda usar tags o releases para cada versión estable.
