<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kanban Premium - PHP + SQLite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="src/Frontend/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="topbar">
        <div class="logo">Kanban<span>Flow</span></div>
        <div class="topbar-actions">
            <button class="btn btn-primary" id="btnNew">
                <span class="icon">+</span> Nueva Tarea
            </button>
            <button class="btn btn-outline" id="btnLogout" onclick="localStorage.clear(); location.reload();">
                <span class="icon">🚪</span> Salir
            </button>
        </div>
    </div>

    <div class="app-layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3>Menú</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <span class="nav-icon">📋</span> Tareas
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">📂</span> Proyectos
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-icon">📊</span> Reportes
                </a>
            </nav>
        </aside>

        <main class="content">
            <div class="board-container">
                <div class="board" id="board">
                    <div class="col" id="backlog">
                        <div class="col-header">
                            <h3>Backlog</h3>
                            <span class="count">0</span>
                        </div>
                        <div class="card-list"></div>
                    </div>
                    <div class="col" id="doing">
                        <div class="col-header">
                            <h3>In Progress</h3>
                            <span class="count">0</span>
                        </div>
                        <div class="card-list"></div>
                    </div>
                    <div class="col" id="done">
                        <div class="col-header">
                            <h3>Done</h3>
                            <span class="count">0</span>
                        </div>
                        <div class="card-list"></div>
                    </div>
                    <div class="col" id="todo">
                        <div class="col-header">
                            <h3>En Revisión</h3>
                            <span class="count">0</span>
                        </div>
                        <div class="card-list"></div>
                    </div>
                </div>
            </div>

            <div id="modalOverlay" class="modal-overlay">
                <div id="newModal" class="modal">
                    <div class="modal-header">
                        <h3>Nueva Tarea</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <form id="newTask" class="modal-body">
                        <div class="form-group">
                            <label>Título</label>
                            <input name="title" placeholder="Ej: Implementar login" required />
                        </div>
                        <div class="form-group">
                            <label>Proyecto</label>
                            <input name="project" placeholder="Nombre del proyecto" />
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="status">
                                    <option>Backlog</option>
                                    <option value="In Progress">Doing</option>
                                    <option>Done</option>
                                    <option>En Revisión</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Prioridad</label>
                                <select name="priority">
                                    <option value="Low">Baja</option>
                                    <option value="Medium">Media</option>
                                    <option value="High">Alta</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Fecha límite</label>
                            <input type="date" name="due_date" />
                        </div>
                        <button class="btn btn-block btn-primary" type="submit">Crear Tarea</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="src/Frontend/js/script.js"></script>
</body>
</html>