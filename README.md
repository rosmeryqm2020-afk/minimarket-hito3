
### Sistema de Gestión de Productos – Minimar
**Asignatura:** Programación III  
**Proyecto:** Hito 3 – Evaluación práctica  
**Arquitectura:** Frontend PHP + API REST Quarkus + MySQL Docker
### 1. Descripción
Sistema para administrar los productos de un minimarket mediante una arquitectura de servicios independientes.
Permite:
- Consultar productos activos.
- Registrar nuevos productos.
- Editar información de productos.
- Eliminar productos lógicamente sin borrarlos de MySQL.

### 2. Tecnologías utilizadas
| Componente | Tecnología |
|---|---|
| Backend | Java 25 y Quarkus 3 |
| Frontend | PHP 8.4 con cURL |
| Base de datos | MySQL 8.4 |
| Contenedores | Docker Compose |
| Compilación | Maven Wrapper |
### 3. Arquitectura del sistema
```text
┌───────────────────────────┐
│ FRONTEND PHP              │
│ http://localhost:8000     │
│ Formularios y tabla       │
└─────────────┬─────────────┘
              │ HTTP / JSON
              ▼
┌───────────────────────────┐
│ BACKEND QUARKUS           │
│ http://localhost:8080     │
│ API REST /productos       │
└─────────────┬─────────────┘
              │ JDBC / SQL
              ▼
┌───────────────────────────┐
│ MYSQL 8.4 EN DOCKER       │
│ Puerto local: 3307        │
│ db_minimarket_pro413      │
└───────────────────────────┘
```
El frontend consume exclusivamente la API REST, sin realizar conexiones directas a MySQL.

### 4. Estructura del proyecto

```text
minimarket_hito3/
├── backend/
│   ├── pom.xml
│   ├── mvnw
│   ├── mvnw.cmd
│   ├── .mvn/
│   └── src/main/
│       ├── java/com/minimarket/
│       │   ├── Producto.java
│       │   └── ProductoResource.java
│       └── resources/
│           └── application.properties
├── frontend/
│   └── index.php
├── sql/
│   └── 01-schema.sql
├── docker-compose.yml
├── .env.example
├── .gitignore
└── README.md
```

## #5. Requisitos previos

- Java 25.
- Docker Desktop funcionando.
- PHP 8.4 con extensión cURL activada.
- Git para clonar el repositorio.

Maven Wrapper está incluido en el backend.

### 6. Instalación y ejecución

### Paso 1. Clonar el proyecto

```powershell
git clone https://github.com/rosmeryqm2020-afk/minimarket-hito3.git
cd minimarket-hito3
```

### Paso 2. Configurar variables de entorno

Copiar el archivo de ejemplo:

```powershell
Copy-Item .env.example .env
```

Abrir `.env` y sustituir los valores de ejemplo por contraseñas locales.

### Paso 3. Iniciar MySQL con Docker

Desde la carpeta principal:

```powershell
docker compose up -d
```

Comprobar el contenedor:

```powershell
docker compose ps
```

La base de datos utiliza el puerto local 3307 y un volumen persistente de Docker.

### Paso 4. Cargar la contraseña del backend

En PowerShell, desde la carpeta principal:

```powershell
$env:DB_PASSWORD = (Get-Content .env | Where-Object { $_ -like 'MYSQL_PASSWORD=*' } | Select-Object -First 1).Split('=',2)[1]
```

### Paso 5. Iniciar el backend Quarkus

En la misma terminal:

```powershell
cd backend
.\mvnw.cmd quarkus:dev
```

La API REST se ejecuta en:

http://localhost:8080/productos

### Paso 6. Iniciar el frontend PHP

Abrir otra terminal PowerShell desde la carpeta principal del proyecto:

```powershell
cd frontend
php -S localhost:8000
```

Abrir en el navegador:

http://localhost:8000

### Paso 7. Compilar el backend

Desde la carpeta `backend`:

```powershell
.\mvnw.cmd -DskipTests package
```

El resultado esperado es `BUILD SUCCESS`.

### 7. Endpoints de la API REST

| Método | Endpoint | Función |
|---|---|---|
| GET | /productos | Consultar productos activos |
| POST | /productos | Registrar un producto |
| PUT | /productos/{id} | Editar un producto |
| DELETE | /productos/{id} | Eliminar lógicamente |

La API utiliza JSON para intercambiar información.

### 8. Base de datos

**Base de datos:** `db_minimarket_pro413`  
**Tabla:** `Producto`

| Campo | Tipo |
|---|---|
| id | INT, PRIMARY KEY, AUTO_INCREMENT |
| nombre | VARCHAR(100), NOT NULL |
| proveedor | VARCHAR(100), NOT NULL |
| fecha_vencimiento | DATE, NOT NULL |
| precio | DECIMAL(10,2), NOT NULL |
| stock | INT, NOT NULL |
| eliminado | TINYINT(1), DEFAULT 0 |

Los precios y el stock deben ser mayores o iguales a cero.

El valor `eliminado = 0` identifica un producto activo y `eliminado = 1` identifica uno eliminado lógicamente.

### 9. Pruebas realizadas

- Registro de Leche PIL con precio Bs 12,50 y stock 20.
- Actualización del precio a Bs 15,00 y stock a 30.
- Eliminación lógica y exclusión del listado activo.
- Verificación de `eliminado = 1` en MySQL.
- Persistencia de datos después de reiniciar el contenedor.
- Validaciones HTTP 400 para precio negativo, stock negativo y nombre vacío.
- Compilación del backend con `BUILD SUCCESS`.

### 10. Seguridad

El archivo `.env` almacena las contraseñas locales y está excluido del repositorio mediante `.gitignore`.

El archivo `.env.example` contiene únicamente valores de ejemplo.

La contraseña del backend se obtiene mediante la variable de entorno `DB_PASSWORD`.

### 11. quien lo iso suiiiiiiii

**Estudiante:** Rosmery Quispe Mamani  
**Asignatura:** Programación III  
**Proyecto:** Sistema de Gestión de Productos de un Minimarket

