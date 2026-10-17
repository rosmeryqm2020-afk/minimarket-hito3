# Sistema de Gestión de Productos - Minimarket

## Descripción
Sistema de microservicios para administrar productos de un minimarket, desarrollado para Programación 3.

Permite consultar, registrar, editar y eliminar lógicamente productos.

## Arquitectura
Frontend PHP (puerto 8000)
        |
        | HTTP / JSON
        v
Backend Java Quarkus REST (puerto 8080)
        |
        | JDBC
        v
MySQL 8.4 en Docker (puerto 3307)

## Tecnologías
- Java 25
- Quarkus 3
- PHP 8.4 con extensión cURL
- MySQL 8.4
- Docker y Docker Compose
- Maven Wrapper

## Estructura
- backend/: API REST Quarkus
- frontend/: interfaz web PHP
- sql/01-schema.sql: estructura de Producto
- docker-compose.yml: contenedor MySQL
- .env.example: plantilla de configuración

## Preparación
Requisitos: Java, Docker Desktop y PHP con cURL.

1. Copiar .env.example como .env.
2. Cambiar las contraseñas de ejemplo por contraseñas locales.
3. Iniciar MySQL:

docker compose up -d

4. Cargar la contraseña del usuario minimarket en PowerShell:

$env:DB_PASSWORD = (Get-Content .env | Where-Object { $_ -like 'MYSQL_PASSWORD=*' } | Select-Object -First 1).Split('=',2)[1]

5. Iniciar el backend:

cd backend
.\mvnw.cmd quarkus:dev

6. En otra terminal iniciar el frontend desde la raíz:

cd frontend
php -S localhost:8000

## Direcciones
- Frontend: http://localhost:8000
- API REST: http://localhost:8080/productos

## Endpoints
- GET /productos: consultar productos activos
- POST /productos: registrar producto
- PUT /productos/{id}: editar producto
- DELETE /productos/{id}: eliminación lógica

## Base de datos
Base: db_minimarket_pro413
Tabla: Producto

Campos: id, nombre, proveedor, fecha_vencimiento, precio, stock y eliminado.

El campo eliminado utiliza 0 para activo y 1 para eliminado lógicamente.

## Pruebas realizadas
- Registro de producto Leche PIL.
- Edición de precio y stock.
- Eliminación lógica.
- Comprobación del registro en MySQL.
- Persistencia tras reiniciar el contenedor.
- Validación de precio negativo, stock negativo y nombre vacío con HTTP 400.

## Seguridad
El archivo .env contiene credenciales locales y está excluido de Git mediante .gitignore.
