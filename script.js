// =================================
// CONFIGURACIÓN
// =================================
// Ruta correcta sin duplicados
const API_CURSOS = "api/cursos.php";
const API_ESTUDIANTES = "api/estudiantes.php";

// =================================
// NAVEGACIÓN
// =================================
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    const pages = document.querySelectorAll('.page');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            navLinks.forEach(l => l.classList.remove('active'));
            pages.forEach(p => p.classList.remove('active'));
            
            this.classList.add('active');
            
            const targetPage = this.getAttribute('data-page');
            
            if (targetPage === 'logout') {
                if (confirm('¿Está seguro que desea cerrar sesión?')) {
                    alert('Sesión cerrada correctamente');
                }
                return;
            }
            
            const pageElement = document.getElementById(targetPage);
            if (pageElement) {
                pageElement.classList.add('active');
                
                // Cargar cursos cuando se accede a la página de cursos
                if (targetPage === 'courses') {
                    cargarTarjetasCursos();
                }
                
                // Cargar estudiantes cuando se accede a la página de estudiantes
                if (targetPage === 'students') {
                    listarEstudiantes();
                }
            }
        });
    });
    
    // Cargar cursos al iniciar
    listarCursos();
});

// =================================
// FUNCIONALIDAD DE CURSOS
// =================================

// Referencias a elementos del formulario
const formDash = document.getElementById("formCursoDash");
const btnNuevo = document.getElementById("btnAgregarCursoDash");
const btnCancelar = document.getElementById("cancelarCursoDash");
const btnGuardar = document.getElementById("guardarCursoDash");

// Mostrar/ocultar formulario
btnNuevo.addEventListener("click", () => {
    const isVisible = formDash.style.display !== "none";
    formDash.style.display = isVisible ? "none" : "block";
    
    if (!isVisible) {
        // Limpiar formulario al abrir
        document.getElementById("nombreCursoDash").value = "";
        document.getElementById("codigoCursoDash").value = "";
        document.getElementById("descripcionCursoDash").value = "";
        document.getElementById("estadoCursoDash").value = "Activo";
    }
});

btnCancelar.addEventListener("click", () => {
    formDash.style.display = "none";
    // Limpiar campos
    document.getElementById("nombreCursoDash").value = "";
    document.getElementById("codigoCursoDash").value = "";
    document.getElementById("descripcionCursoDash").value = "";
});

// Guardar nuevo curso
btnGuardar.addEventListener("click", async () => {
    const nombre = document.getElementById("nombreCursoDash").value.trim();
    const codigo = document.getElementById("codigoCursoDash").value.trim();
    const descripcion = document.getElementById("descripcionCursoDash").value.trim();
    const estado = document.getElementById("estadoCursoDash").value;

    // Validación
    if (!nombre || !codigo) {
        alert("⚠️ Por favor completa el nombre y código del curso.");
        return;
    }

    // Deshabilitar botón mientras se guarda
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    try {
        console.log("Enviando a:", API_CURSOS); // Debug
        console.log("Datos:", { nombre, codigo, descripcion, estado }); // Debug
        
        const res = await fetch(API_CURSOS, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ 
                nombre, 
                codigo, 
                descripcion, 
                estado,
                id_docente: 1 // ID del docente Javier V. Vaca
            })
        });

        console.log("Status:", res.status); // Debug
        console.log("Response OK:", res.ok); // Debug

        if (!res.ok) {
            throw new Error(`HTTP Error: ${res.status} - ${res.statusText}`);
        }

        const data = await res.json();
        console.log("Respuesta del servidor:", data); // Debug

        if (data.success) {
            alert("✅ Curso agregado correctamente");
            
            // Limpiar formulario
            document.getElementById("nombreCursoDash").value = "";
            document.getElementById("codigoCursoDash").value = "";
            document.getElementById("descripcionCursoDash").value = "";
            document.getElementById("estadoCursoDash").value = "Activo";
            
            // Ocultar formulario
            formDash.style.display = "none";
            
            // Recargar lista de cursos
            listarCursos();
            cargarTarjetasCursos();
        } else {
            alert("⚠️ Error: " + (data.error || "No se pudo agregar el curso."));
        }
    } catch (err) {
        console.error("Error completo:", err);
        alert(`❌ Error de conexión: ${err.message}\n\nRevisa la consola (F12) para más detalles.`);
    } finally {
        // Rehabilitar botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar Curso';
    }
});

// Función para listar cursos en la tabla del dashboard
async function listarCursos() {
    try {
        const res = await fetch(API_CURSOS);
        
        if (!res.ok) {
            throw new Error("HTTP " + res.status);
        }
        
        const cursos = await res.json();
        const tbody = document.getElementById("tbodyCursos");
        
        if (!tbody) return;

        if (cursos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">
                        No hay cursos registrados. Crea tu primer curso usando el botón "Nuevo Curso".
                    </td>
                </tr>
            `;
            // Actualizar contador
            document.getElementById("totalCursos").textContent = "0";
            return;
        }

        tbody.innerHTML = cursos.map(c => `
            <tr>
                <td>${escapeHtml(c.nombre)}</td>
                <td>${escapeHtml(c.codigo)}</td>
                <td>${c.num_estudiantes || 0}</td>
                <td>${c.num_actividades || 0}</td>
                <td>
                    <span class="status ${c.estado === 'Activo' ? 'active' : 'pending'}">
                        ${escapeHtml(c.estado)}
                    </span>
                </td>
                <td>
                    <div class="actions">
                        <button class="btn-icon edit" title="Editar" data-id="${c.id_curso}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon delete" title="Eliminar" data-id="${c.id_curso}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join("");

        // Actualizar contador de cursos
        const cursosActivos = cursos.filter(c => c.estado === 'Activo').length;
        document.getElementById("totalCursos").textContent = cursosActivos;

        // Agregar eventos a botones de eliminar
        document.querySelectorAll(".btn-icon.delete").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.getAttribute("data-id");
                eliminarCurso(id);
            });
        });

        // Agregar eventos a botones de editar (para futuro)
        document.querySelectorAll(".btn-icon.edit").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.getAttribute("data-id");
                alert("Función de editar en desarrollo. ID: " + id);
            });
        });

    } catch (err) {
        console.error("Error al listar cursos:", err);
        const tbody = document.getElementById("tbodyCursos");
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; color: red; padding: 20px;">
                        ❌ Error al cargar los cursos. Verifica la conexión con el servidor.
                    </td>
                </tr>
            `;
        }
    }
}

// Función para eliminar curso
async function eliminarCurso(id) {
    if (!confirm("¿Seguro que deseas eliminar este curso? Esta acción no se puede deshacer.")) {
        return;
    }

    try {
        const res = await fetch(API_CURSOS + "?id=" + id, { 
            method: "DELETE" 
        });
        
        const data = await res.json();

        if (data.success) {
            alert("✅ Curso eliminado correctamente");
            listarCursos();
            cargarTarjetasCursos();
        } else {
            alert("⚠️ Error al eliminar: " + (data.error || ""));
        }
    } catch (err) {
        console.error("Error al eliminar curso:", err);
        alert("❌ No se pudo eliminar el curso.");
    }
}

// Función para cargar tarjetas de cursos en la página "Mis Cursos"
async function cargarTarjetasCursos() {
    try {
        const res = await fetch(API_CURSOS);
        const cursos = await res.json();
        
        const container = document.getElementById("courseCardsContainer");
        if (!container) return;

        if (cursos.length === 0) {
            container.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <i class="fas fa-book" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p>No hay cursos registrados.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = cursos.map(c => `
            <div class="course-card">
                <div class="course-header">
                    <h3>${escapeHtml(c.nombre)}</h3>
                    <p>Código: ${escapeHtml(c.codigo)}</p>
                </div>
                <div class="course-body">
                    <p>${escapeHtml(c.descripcion || 'Sin descripción')}</p>
                    <div class="course-stats">
                        <div class="course-stat">
                            <div class="course-stat-value">${c.num_estudiantes || 0}</div>
                            <div class="course-stat-label">Estudiantes</div>
                        </div>
                        <div class="course-stat">
                            <div class="course-stat-value">${c.num_actividades || 0}</div>
                            <div class="course-stat-label">Actividades</div>
                        </div>
                        <div class="course-stat">
                            <div class="course-stat-value">-</div>
                            <div class="course-stat-label">Promedio</div>
                        </div>
                    </div>
                </div>
            </div>
        `).join("");

    } catch (err) {
        console.error("Error al cargar tarjetas:", err);
    }
}

// Función para búsqueda de cursos
const buscarInput = document.getElementById("buscarCurso");
if (buscarInput) {
    buscarInput.addEventListener("input", function() {
        const termino = this.value.toLowerCase();
        const filas = document.querySelectorAll("#tbodyCursos tr");
        
        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            fila.style.display = texto.includes(termino) ? "" : "none";
        });
    });
}

// Utilidad para prevenir inyección HTML
function escapeHtml(str) {
    if (!str) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// =================================
// FUNCIONALIDAD DE ESTUDIANTES
// =================================

let estudianteEditando = null;

// Referencias a elementos del formulario de estudiantes
const formEstudiante = document.getElementById("formEstudiante");
const btnAgregarEstudiante = document.getElementById("btnAgregarEstudiante");
const btnCancelarEstudiante = document.getElementById("cancelarEstudiante");
const btnGuardarEstudiante = document.getElementById("guardarEstudiante");

// Mostrar/ocultar formulario de estudiante
if (btnAgregarEstudiante) {
    btnAgregarEstudiante.addEventListener("click", () => {
        estudianteEditando = null;
        document.getElementById("tituloFormEstudiante").textContent = "Nuevo Estudiante";
        formEstudiante.style.display = formEstudiante.style.display === "none" ? "block" : "none";
        
        if (formEstudiante.style.display === "block") {
            limpiarFormularioEstudiante();
            cargarCursosEnSelect();
        }
    });
}

if (btnCancelarEstudiante) {
    btnCancelarEstudiante.addEventListener("click", () => {
        formEstudiante.style.display = "none";
        limpiarFormularioEstudiante();
        estudianteEditando = null;
    });
}

// Guardar estudiante (crear o actualizar)
if (btnGuardarEstudiante) {
    btnGuardarEstudiante.addEventListener("click", async () => {
        const nombre = document.getElementById("nombreEstudiante").value.trim();
        const id_curso = document.getElementById("cursoEstudiante").value;
        const nota_final = document.getElementById("notaEstudiante").value;

        if (!nombre) {
            alert("⚠️ Por favor ingresa el nombre del estudiante.");
            return;
        }

        btnGuardarEstudiante.disabled = true;
        btnGuardarEstudiante.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        try {
            const url = estudianteEditando 
                ? API_ESTUDIANTES 
                : API_ESTUDIANTES;
            
            const method = estudianteEditando ? "PUT" : "POST";
            
            const body = {
                nombre,
                id_curso: id_curso || null,
                nota_final: nota_final || null
            };

            if (estudianteEditando) {
                body.id_estudiante = estudianteEditando;
            }

            const res = await fetch(url, {
                method: method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(body)
            });

            const data = await res.json();

            if (data.success) {
                alert(estudianteEditando 
                    ? "✅ Estudiante actualizado correctamente" 
                    : "✅ Estudiante agregado correctamente");
                
                limpiarFormularioEstudiante();
                formEstudiante.style.display = "none";
                estudianteEditando = null;
                listarEstudiantes();
            } else {
                alert("⚠️ Error: " + (data.error || "No se pudo guardar el estudiante."));
            }
        } catch (err) {
            console.error("Error al guardar estudiante:", err);
            alert("❌ Error de conexión con el servidor.");
        } finally {
            btnGuardarEstudiante.disabled = false;
            btnGuardarEstudiante.innerHTML = '<i class="fas fa-save"></i> Guardar Estudiante';
        }
    });
}

// Función para listar estudiantes
async function listarEstudiantes() {
    try {
        const res = await fetch(API_ESTUDIANTES);
        
        if (!res.ok) {
            throw new Error("HTTP " + res.status);
        }
        
        const estudiantes = await res.json();
        const tbody = document.getElementById("tbodyEstudiantes");
        
        if (!tbody) return;

        if (estudiantes.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        No hay estudiantes registrados. Agrega el primer estudiante usando el botón "Agregar Estudiante".
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = estudiantes.map(e => `
            <tr>
                <td>${e.id_estudiante}</td>
                <td>${escapeHtml(e.nombre)}</td>
                <td>${escapeHtml(e.curso_nombre || 'Sin curso')}</td>
                <td>${e.nota_final !== null ? parseFloat(e.nota_final).toFixed(1) : '-'}</td>
                <td>
                    <div class="actions">
                        <button class="btn-icon edit" title="Editar" data-id="${e.id_estudiante}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon delete" title="Eliminar" data-id="${e.id_estudiante}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join("");

        // Eventos para editar
        document.querySelectorAll("#tbodyEstudiantes .btn-icon.edit").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.getAttribute("data-id");
                editarEstudiante(id);
            });
        });

        // Eventos para eliminar
        document.querySelectorAll("#tbodyEstudiantes .btn-icon.delete").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.getAttribute("data-id");
                eliminarEstudiante(id);
            });
        });

    } catch (err) {
        console.error("Error al listar estudiantes:", err);
        const tbody = document.getElementById("tbodyEstudiantes");
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; color: red; padding: 20px;">
                        ❌ Error al cargar los estudiantes.
                    </td>
                </tr>
            `;
        }
    }
}

// Función para cargar cursos en el select
async function cargarCursosEnSelect() {
    try {
        const res = await fetch(API_CURSOS);
        const cursos = await res.json();
        
        const select = document.getElementById("cursoEstudiante");
        if (!select) return;
        
        select.innerHTML = '<option value="">Seleccione un curso</option>';
        
        cursos.forEach(curso => {
            const option = document.createElement("option");
            option.value = curso.id_curso;
            option.textContent = `${curso.nombre} (${curso.codigo})`;
            select.appendChild(option);
        });
    } catch (err) {
        console.error("Error al cargar cursos:", err);
    }
}

// Función para editar estudiante
async function editarEstudiante(id) {
    try {
        const res = await fetch(`${API_ESTUDIANTES}?id=${id}`);
        const estudiante = await res.json();
        
        if (estudiante && !estudiante.error) {
            estudianteEditando = id;
            document.getElementById("tituloFormEstudiante").textContent = "Editar Estudiante";
            document.getElementById("nombreEstudiante").value = estudiante.nombre || "";
            document.getElementById("notaEstudiante").value = estudiante.nota_final || "";
            
            await cargarCursosEnSelect();
            document.getElementById("cursoEstudiante").value = estudiante.id_curso || "";
            
            formEstudiante.style.display = "block";
        }
    } catch (err) {
        console.error("Error al cargar estudiante:", err);
        alert("❌ Error al cargar los datos del estudiante.");
    }
}

// Función para eliminar estudiante
async function eliminarEstudiante(id) {
    if (!confirm("¿Seguro que deseas eliminar este estudiante? Esta acción no se puede deshacer.")) {
        return;
    }

    try {
        const res = await fetch(`${API_ESTUDIANTES}?id=${id}`, { 
            method: "DELETE" 
        });
        
        const data = await res.json();

        if (data.success) {
            alert("✅ Estudiante eliminado correctamente");
            listarEstudiantes();
        } else {
            alert("⚠️ Error al eliminar: " + (data.error || ""));
        }
    } catch (err) {
        console.error("Error al eliminar estudiante:", err);
        alert("❌ No se pudo eliminar el estudiante.");
    }
}

// Función para limpiar formulario
function limpiarFormularioEstudiante() {
    document.getElementById("nombreEstudiante").value = "";
    document.getElementById("cursoEstudiante").value = "";
    document.getElementById("notaEstudiante").value = "";
}

// Búsqueda de estudiantes
const buscarEstudianteInput = document.getElementById("buscarEstudiante");
if (buscarEstudianteInput) {
    buscarEstudianteInput.addEventListener("input", function() {
        const termino = this.value.toLowerCase();
        const filas = document.querySelectorAll("#tbodyEstudiantes tr");
        
        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            fila.style.display = texto.includes(termino) ? "" : "none";
        });
    });
}