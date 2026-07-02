# 🏥 MedFlow - Sistema Integrado de Gestión Hospitalaria

MedFlow es un ecosistema modular e inteligente diseñado para optimizar los procesos de admisión, clasificación de gravedad (Triaje) y despacho médico en instituciones de salud de alta complejidad. La plataforma interconecta el área administrativa, el cuerpo médico y el control operativo en tiempo real.

---

## 🚀 Características y Módulos Principales

*   **📝 Admisión y Registro (`ingresar_paciente.php`):** Captura inicial centralizada de datos demográficos y cobertura de salud de pacientes.
*   **🩺 Clasificación de Triaje (`triaje.html`):** Evaluación algorítmica reactiva de signos vitales para determinar la prioridad clínica.
*   **📋 Tablero de Urgencias (`dashboard.html`):** Monitoreo visual en tiempo real de colas de atención y médicos asignados.
*   **📊 Analítica y Gráficas (`dashboard_estadisticas.html`):** Panel gerencial con métricas KPI y gráficas interactivas (Barras/Torta) mediante Chart.js para el análisis de cobertura por EPS y niveles de triaje.
*   **👨‍⚕️ Evolución Médica (`atencion.html`):** Consultorio digital para diagnósticos, planes de manejo y prescripción automática de medicamentos.
*   **📑 Cuentas y Liquidación (`facturacion.html`):** Gestión administrativa de altas hospitalarias, cálculo de tiempos de atención y auditoría de copagos.
*   **🛏️ Control de Hospitalización (`hospitalizacion.html`):** Gestión y asignación dinámica de camas y pisos físicos en tiempo real.
*   **🖨️ Formato Único de Egreso (`imprimir_historia.html`):** Generación inteligente y automatizada de hojas de egreso optimizadas para impresión física o PDF, incluyendo firmas médicas institucionales directas desde la base de datos.

---

## 🛠️ Stack Tecnológico

*   **Frontend:** HTML5, CSS3 (Media Queries para optimización `@media print`), JavaScript (Vanilla / ES6), Chart.js (Visualización de datos).
*   **Backend:** PHP 8 (Arquitectura modular basada en API REST y controladores orientados a objetos).
*   **Base de Datos:** MySQL / MariaDB (Control de relaciones e integridad referencial).
*   **Servidor:** Apache HTTP Server (Entorno local gestionado con XAMPP).

---

## 🗺️ Próximas Mejoras (Roadmap de Desarrollo)

Para elevar la plataforma a un estándar de software empresarial, se ha trazado la implementación de los siguientes cuatro pilares técnicos:

1.  **🔒 Auditoría de Seguridad (Logs de Transacciones):** Creación de un sistema interno de rastreo e historial (`auditoria_sistema`) para registrar con precisión qué usuario realiza operaciones de creación o edición de historias clínicas y facturas.
2.  **📱 Experiencia de Usuario Adaptativa (UX/UI):** Rediseño responsivo utilizando frameworks modernos para garantizar una navegación fluida del personal asistencial desde tablets y dispositivos móviles.
3.  **📈 Exportación de Datos Operativos:** Desarrollo de scripts de extracción en PHP para exportar las sábanas de facturación y flujos de pacientes directamente a archivos legibles por hojas de cálculo (.csv / .xlsx).
4.  **💬 Módulo de Notificaciones y Cuidado Remoto:** Integración de servicios automatizados de comunicación (Email/SMTP o APIs de mensajería) para el envío directo de recordatorios de citas de control y certificaciones de incapacidad a los pacientes.

---

## ✒️ Desarrollado por
* **Carlos arteaga ** - *Desarrolladora Full Stack / Creadora del Ecosistema MedFlow*