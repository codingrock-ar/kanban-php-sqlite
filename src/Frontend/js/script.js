/** Kanban Flow - Modern Frontend Logic **/

const API_PATH = 'api.php';

let projects = [];
let assignees = [];
let allTasks = [];
let archivedPage = 1;

async function fetchProjects() {
    try {
        const response = await fetch(`${API_PATH}?route=projects`);
        const data = await response.json();
        projects = data.projects || [];
        populateSelect('taskProject', projects, 'Sin proyecto');
        renderProjectList();
    } catch (e) { console.error("Projects fetch error:", e); }
}

function renderProjectList() {
    const list = document.getElementById('projectList');
    if (!list) return;
    list.innerHTML = projects.map(p => `
        <div style="display:flex; justify-content:space-between; align-items:center; padding:8px; border-bottom:1px solid #eee;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:12px; height:12px; border-radius:50%; background:${p.color}"></div>
                <span>${p.name}</span>
            </div>
            <button onclick="deleteProject(${p.id})" style="background:none; border:none; cursor:pointer; color:#ef4444;">🗑️</button>
        </div>
    `).join('') || '<div style="color:#94a3b8; font-size:12px; text-align:center;">No hay proyectos</div>';
}

window.deleteProject = async function(id) {
    if (!confirm('¿Eliminar proyecto?')) return;
    await fetch(`${API_PATH}?route=projects/delete`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id })
    });
    fetchProjects();
}

async function fetchAssignees() {
    try {
        const response = await fetch(`${API_PATH}?route=assignees`);
        const data = await response.json();
        assignees = data.assignees || [];
        populateSelect('taskAssignee', assignees, 'Sin asignar');
        renderAssigneeList();
    } catch (e) { console.error("Assignees fetch error:", e); }
}

function renderAssigneeList() {
    const list = document.getElementById('assigneeList');
    if (!list) return;
    list.innerHTML = assignees.map(a => `
        <div style="display:flex; justify-content:space-between; align-items:center; padding:8px; border-bottom:1px solid #eee;">
            <span>👤 ${a.name}</span>
            <button onclick="deleteAssignee(${a.id})" style="background:none; border:none; cursor:pointer; color:#ef4444;">🗑️</button>
        </div>
    `).join('') || '<div style="color:#94a3b8; font-size:12px; text-align:center;">No hay integrantes</div>';
}

window.deleteAssignee = async function(id) {
    if (!confirm('¿Eliminar integrante?')) return;
    await fetch(`${API_PATH}?route=assignees/delete`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id })
    });
    fetchAssignees();
}

function populateSelect(id, items, defaultText) {
    const select = document.getElementById(id);
    if (!select) return;
    select.innerHTML = `<option value="">${defaultText}</option>` + 
        items.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
}

async function fetchTasks() {
    try {
        await Promise.all([fetchProjects(), fetchAssignees()]);
        const response = await fetch(`${API_PATH}?route=tasks`);
        if (!response.ok) throw new Error("API Error");
        
        const data = await response.json();
        if (!data || !data.tasks) return;

        allTasks = data.tasks;
        renderBoard(data.tasks);
    } catch (e) {
        console.error("Fetch error:", e);
    }
}

function renderBoard(tasks) {
    const columns = {
        backlog: tasks.filter(t => t.status === 'Backlog'),
        doing: tasks.filter(t => t.status === 'In Progress'),
        done: tasks.filter(t => t.status === 'Done'),
        todo: tasks.filter(t => t.status === 'En Revisión')
    };

    const formatDate = (dateStr) => {
        if (!dateStr) return null;
        const d = new Date(dateStr);
        return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
    };

    Object.keys(columns).forEach(colId => {
        const colEl = document.getElementById(colId);
        if (!colEl) return;
        const listEl = colEl.querySelector('.card-list');
        const countEl = colEl.querySelector('.count');
        
        countEl.textContent = columns[colId].length;
        
        if (columns[colId].length === 0) {
            listEl.innerHTML = '<div class="empty-state" style="text-align:center; padding:20px; color:#94a3b8; font-size:13px;">No hay tareas</div>';
            return;
        }

        listEl.innerHTML = columns[colId].map(task => `
            <div class="card" draggable="true" data-id="${task.id}" ondragstart="handleDragStart(event)">
                ${task.project_name ? `
                    <div class="card-project" style="color: ${task.project_color || 'var(--accent)'}">
                        ${task.project_name}
                    </div>
                ` : ''}
                <div class="card-title">${task.title}</div>
                ${task.assignee_name ? `
                    <div class="card-assignee" style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">
                        👤 ${task.assignee_name}
                    </div>
                ` : ''}
                ${task.description ? `
                    <div class="card-desc truncated" id="desc-${task.id}">${task.description}</div>
                    ${task.description.length > 80 ? `<button class="read-more-btn" onclick="toggleDesc(${task.id}, this)">Leer más</button>` : ''}
                ` : ''}
                
                <div class="card-dates" style="font-size: 11px; color: #64748b; margin-top: 8px; display: flex; flex-direction: column; gap: 2px;">
                    <div>📅 Creado: ${formatDate(task.created_at)}</div>
                    ${task.due_date ? `<div style="color: #ef4444; font-weight: 600;">⌛ Límite: ${formatDate(task.due_date)}</div>` : ''}
                </div>

                <div class="card-footer">
                    <span class="card-priority priority-${task.priority || 'Low'}">${task.priority || 'Baja'}</span>
                    <div class="card-actions">
                        ${task.status === 'Done' ? `<button class="action-btn" onclick="archiveTask(${task.id})" title="Archivar">📦</button>` : ''}
                        <button class="action-btn" onclick="editTask(${task.id})" title="Editar">✏️</button>
                        <button class="action-btn" onclick="deleteTask(${task.id})" title="Eliminar">🗑️</button>
                        <select onchange="moveTask(${task.id}, this.value)" style="width: auto; padding: 2px; font-size: 10px; margin-left: 4px;">
                            <option value="" disabled selected>Mover...</option>
                            <option value="Backlog">Backlog</option>
                            <option value="In Progress">Doing</option>
                            <option value="Done">Done</option>
                            <option value="En Revisión">Revisión</option>
                        </select>
                    </div>
                </div>
            </div>
        `).join('');
    });
}

window.moveTask = async function(id, status) {
    try {
        await fetch(`${API_PATH}?route=tasks/move`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id, status })
        });
        fetchTasks();
    } catch (e) {
        console.error("Move error:", e);
    }
}

window.deleteTask = async function(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
        try {
            await fetch(`${API_PATH}?route=tasks/delete`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id })
            });
            fetchTasks();
        } catch (e) {
            console.error("Delete error:", e);
        }
    }
}

window.toggleDesc = function(id, btn) {
    const desc = document.getElementById(`desc-${id}`);
    if (desc.classList.contains('truncated')) {
        desc.classList.remove('truncated');
        btn.textContent = 'Ver menos';
    } else {
        desc.classList.add('truncated');
        btn.textContent = 'Leer más';
    }
}

window.archiveTask = async function(id) {
    if (!confirm('¿Archivar esta tarea? Se ocultará del tablero principal.')) return;
    try {
        await fetch(`${API_PATH}?route=tasks/archive`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id })
        });
        fetchTasks();
    } catch (e) { console.error("Archive error:", e); }
}

window.editTask = function(id) {
    const task = allTasks.find(t => t.id == id);
    if (!task) return;

    document.getElementById('editTaskId').value = task.id;
    document.getElementById('editTaskTitle').value = task.title;
    document.getElementById('editTaskDescription').value = task.description || '';
    document.getElementById('editTaskStatus').value = task.status;
    document.getElementById('editTaskPriority').value = task.priority;
    document.getElementById('editTaskDueDate').value = task.due_date || '';
    
    populateSelect('editTaskProject', projects, 'Sin proyecto');
    populateSelect('editTaskAssignee', assignees, 'Sin asignar');
    
    document.getElementById('editTaskProject').value = task.project_id || '';
    document.getElementById('editTaskAssignee').value = task.assignee_id || '';

    document.getElementById('editModalOverlay').classList.add('active');
}

async function fetchArchivedTasks(page = 1) {
    try {
        const response = await fetch(`${API_PATH}?route=tasks/archived&page=${page}`);
        const data = await response.json();
        renderArchivedList(data);
    } catch (e) { console.error("Archived fetch error:", e); }
}

function renderArchivedList(data) {
    const list = document.getElementById('archivedList');
    const { tasks, pages } = data;
    
    archivedPage = data.page || archivedPage;
    document.getElementById('currentPage').textContent = `Página ${archivedPage} de ${pages || 1}`;
    
    list.innerHTML = tasks.map(t => `
        <div style="padding:12px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-weight:600; font-size:14px;">${t.title}</div>
                <div style="font-size:12px; color:#64748b;">${t.project_name || 'Sin proyecto'} • ${t.assignee_name || 'Sin asignar'}</div>
            </div>
            <div style="font-size:11px; color:#94a3b8;">Finalizado: ${t.updated_at ? new Date(t.updated_at).toLocaleDateString() : '-'}</div>
        </div>
    `).join('') || '<div style="text-align:center; color:#94a3b8; padding:20px;">No hay tareas archivadas</div>';
}

// Drag and Drop implementation (Simplified)
function handleDragStart(e) {
    e.dataTransfer.setData('task-id', e.target.getAttribute('data-id'));
}

document.addEventListener('DOMContentLoaded', () => {
    // Logout Logic first
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', () => {
            console.log("Logout triggered");
            localStorage.clear();
            window.location.reload();
        });
    }

    fetchTasks();

    const toggleModal = (id) => document.getElementById(id).classList.toggle('active');
    window.closeModal = (id) => document.getElementById(id).classList.remove('active');
    window.toggleModal = toggleModal; // Export to global scope
    
    btnNew.addEventListener('click', () => toggleModal('modalOverlay'));
    btnClose.addEventListener('click', () => closeModal('modalOverlay'));
    
    document.getElementById('navProjects').addEventListener('click', (e) => {
        e.preventDefault();
        toggleModal('projectModalOverlay');
    });
    
    document.getElementById('navTeam').addEventListener('click', (e) => {
        e.preventDefault();
        toggleModal('teamModalOverlay');
    });

    document.getElementById('navArchived').addEventListener('click', (e) => {
        e.preventDefault();
        archivedPage = 1;
        fetchArchivedTasks(1);
        toggleModal('archivedView');
    });

    document.getElementById('prevPage').onclick = () => {
        if (archivedPage > 1) fetchArchivedTasks(--archivedPage);
    };

    document.getElementById('nextPage').onclick = () => {
        fetchArchivedTasks(++archivedPage);
    };

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });

    document.getElementById('newProject').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        await fetch(`${API_PATH}?route=projects`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ name: fd.get('name'), color: fd.get('color') })
        });
        e.target.reset();
        fetchProjects();
    });

    document.getElementById('newAssignee').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        await fetch(`${API_PATH}?route=assignees`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ name: fd.get('name') })
        });
        e.target.reset();
        fetchAssignees();
    });

    document.getElementById('newTask').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const payload = {
            title: fd.get('title'),
            description: fd.get('description'),
            project_id: fd.get('project_id') || null,
            assignee_id: fd.get('assignee_id') || null,
            status: fd.get('status'),
            priority: fd.get('priority'),
            due_date: fd.get('due_date')
        };

        try {
            await fetch(`${API_PATH}?route=tasks`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            fetchTasks();
            e.target.reset();
            toggleModal('modalOverlay');
        } catch (e) {
            console.error("Create error:", e);
        }
    });

    document.getElementById('editTaskForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const id = fd.get('id');
        const payload = Object.fromEntries(fd.entries());
        delete payload.id;

        try {
            await fetch(`${API_PATH}?route=tasks/${id}`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            fetchTasks();
            closeModal('editModalOverlay');
        } catch (e) { console.error("Update error:", e); }
    });

    // Handle columns as drop zones
    document.querySelectorAll('.col').forEach(col => {
        col.addEventListener('dragover', e => e.preventDefault());
        col.addEventListener('drop', async e => {
            e.preventDefault();
            const id = e.dataTransfer.getData('task-id');
            const status = col.querySelector('h3').textContent;
            
            // Map column titles to internal status names
            let mappedStatus = status;
            if (status === 'Doing') mappedStatus = 'In Progress';
            if (status === 'En Revisión') mappedStatus = 'En Revisión';
            
            await moveTask(id, mappedStatus);
        });
    });
});