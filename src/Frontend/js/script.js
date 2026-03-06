/** Kanban Flow - Modern Frontend Logic **/

const API_PATH = 'api.php';

async function fetchTasks() {
    try {
        const response = await fetch(`${API_PATH}?route=tasks`);
        if (!response.ok) throw new Error("API Error");
        
        const data = await response.json();
        if (!data || !data.tasks) return;

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
                ${task.project ? `<div class="card-project">${task.project}</div>` : ''}
                <div class="card-title">${task.title}</div>
                ${task.description ? `<div class="card-desc">${task.description}</div>` : ''}
                
                <div class="card-dates" style="font-size: 11px; color: #64748b; margin-top: 8px; display: flex; flex-direction: column; gap: 2px;">
                    <div>📅 Creado: ${formatDate(task.created_at)}</div>
                    ${task.due_date ? `<div style="color: #ef4444; font-weight: 600;">⌛ Límite: ${formatDate(task.due_date)}</div>` : ''}
                </div>

                <div class="card-footer">
                    <span class="card-priority priority-${task.priority || 'Low'}">${task.priority || 'Baja'}</span>
                    <div class="card-actions">
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

window.editTask = function(id) {
    const newTitle = prompt('Nuevo título para la tarea:');
    if (newTitle && newTitle.trim()) {
        fetch(`${API_PATH}?route=tasks/${id}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ title: newTitle })
        }).then(() => fetchTasks());
    }
}

// Drag and Drop implementation (Simplified)
function handleDragStart(e) {
    e.dataTransfer.setData('task-id', e.target.getAttribute('data-id'));
}

document.addEventListener('DOMContentLoaded', () => {
    fetchTasks();

    const modal = document.getElementById('newModal');
    const overlay = document.getElementById('modalOverlay');
    const btnNew = document.getElementById('btnNew');
    const btnClose = document.querySelector('.close-modal');

    const toggleModal = () => overlay.classList.toggle('active');

    btnNew.addEventListener('click', toggleModal);
    btnClose.addEventListener('click', toggleModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) toggleModal();
    });

    document.getElementById('newTask').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const payload = {
            title: fd.get('title'),
            project: fd.get('project'),
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
            toggleModal();
        } catch (e) {
            console.error("Create error:", e);
        }
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