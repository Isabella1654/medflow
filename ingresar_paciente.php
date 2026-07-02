<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedFlow - Admisión de Pacientes</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f1f5f9; padding: 30px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { max-width: 500px; width: 100%; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h1 { color: #0f172a; font-size: 1.5rem; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; color: #334155; font-size: 0.9rem; }
        input, select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; background-color: #f8fafc; }
        input:focus, select:focus { outline: none; border-color: #0284c7; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }
        .btn-submit { background: #0284c7; color: white; border: none; width: 100%; padding: 12px; font-weight: bold; border-radius: 6px; font-size: 1rem; cursor: pointer; transition: background 0.2s; margin-top: 10px; }
        .btn-submit:hover { background: #0369a1; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: 500; display: none; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body>

<div class="container">
    <h1>📝 Registro de Admisión</h1>
    <div id="alert-box" class="alert"></div>

    <form id="form-admision">
        <div class="form-group">
            <label for="cedula">Documento de Identidad (Cédula)</label>
            <input type="text" id="cedula" required placeholder="Ej: 1002345678">
        </div>
        <div class="form-group">
            <label for="nombre">Nombres</label>
            <input type="text" id="nombre" required placeholder="Ej: Juan Carlos">
        </div>
        <div class="form-group">
            <label for="apellido">Apellidos</label>
            <input type="text" id="apellido" required placeholder="Ej: Pérez Gómez">
        </div>
        <div class="form-group">
            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
            <input type="date" id="fecha_nacimiento" required>
        </div>
        <div class="form-group">
            <label for="eps">Entidad de Salud (EPS)</label>
            <select id="eps" required>
                <option value="">-- Seleccione una EPS --</option>
                <option value="Mutual ser">Mutual ser</option>
                <option value="Sura">Sura</option>
                <option value="Coosalud">Coosalud</option>
                <option value="Nueva EPS">Nueva EPS</option>
                <option value="Sanitas">Sanitas</option>
            </select>
        </div>
        <button type="submit" class="btn-submit">➕ Registrar e Ingresar Paciente</button>
    </form>
</div>

<script>
    document.getElementById('form-admision').addEventListener('submit', function(e) {
        e.preventDefault();
        const alertBox = document.getElementById('alert-box');

        const datos = {
            cedula: document.getElementById('cedula').value,
            nombre: document.getElementById('nombre').value,
            apellido: document.getElementById('apellido').value,
            fecha_nacimiento: document.getElementById('fecha_nacimiento').value,
            eps: document.getElementById('eps').value
        };

        // Enviamos los datos por POST de forma limpia hacia tu backend PHP
        fetch('ingresar_paciente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(res => res.json())
        .then(data => {
            alertBox.style.display = 'block';
            if(data.success) {
                alertBox.className = 'alert alert-success';
                alertBox.innerText = "¡Paciente registrado exitosamente en Admisión!";
                document.getElementById('form-admision').reset();
            } else {
                alertBox.className = 'alert alert-error';
                alertBox.innerText = data.mensaje;
            }
        })
        .catch(err => {
            alertBox.style.display = 'block';
            alertBox.className = 'alert alert-error';
            alertBox.innerText = "Error procesando la solicitud en el servidor de admisión.";
        });
    });
</script>

</body>
</html>