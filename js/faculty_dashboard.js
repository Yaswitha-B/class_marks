document.addEventListener('DOMContentLoaded', () => {
    const sectionSelect = document.getElementById('sectionSelect');
    const marksTable = document.getElementById('marksTable');
    const marksModal = document.getElementById('marksModal');
    const marksForm = document.getElementById('marksForm');

    // Load faculty's sections
    loadSections();

    // Section change handler
    sectionSelect.addEventListener('change', () => {
        if (sectionSelect.value) loadMarks(sectionSelect.value);
        else clearMarksTable();
    });

    // Form submit handler
    marksForm.addEventListener('submit', (e) => {
        e.preventDefault();
        saveMarks();
    });
});

// Load sections assigned to faculty
function loadSections() {
    fetch('faculty_dashboard.php?action=fetch_sections')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('sectionSelect');
            select.innerHTML = '<option value="">Choose a section...</option>';
            data.sections.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.section_id;
                opt.textContent = `${s.section_id} - ${s.course_name} (${s.year})`;
                opt.dataset.courseId = s.course_id;
                select.appendChild(opt);
            });
        });
}

// Load marks for selected section
function loadMarks(sectionId) {
    fetch(`faculty_dashboard.php?action=fetch_marks&data=${encodeURIComponent(JSON.stringify({section_id: sectionId}))}`)
        .then(r => r.json())
        .then(data => {
            const tbody = document.querySelector('#marksTable tbody');
            tbody.innerHTML = '';
            data.marks.forEach(m => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${m.student_id}</td>
                    <td>${m.student_name}</td>
                    <td>${m.course_name}</td>
                    <td>${m.mid1 || '-'}</td>
                    <td>${m.asgn1 || '-'}</td>
                    <td>${m.mid2 || '-'}</td>
                    <td>${m.asgn2 || '-'}</td>
                    <td>${m.sem || '-'}</td>
                    <td>
                        <button onclick="editMarks(this)">Edit</button>
                        <button onclick="deleteMarks(this)">Delete</button>
                    </td>
                `;
                tr.dataset.studentId = m.student_id;
                tr.dataset.courseId = m.course_id;
                tr.dataset.mid1 = m.mid1 || '';
                tr.dataset.asgn1 = m.asgn1 || '';
                tr.dataset.mid2 = m.mid2 || '';
                tr.dataset.asgn2 = m.asgn2 || '';
                tr.dataset.sem = m.sem || '';
                tbody.appendChild(tr);
            });
        });
}

// Add new marks
function addNewMarks() {
    const select = document.getElementById('sectionSelect');
    if (!select.value) {
        alert('Please select a section first');
        return;
    }

    document.getElementById('studentId').value = '';
    document.getElementById('courseId').value = select.selectedOptions[0].dataset.courseId;
    document.getElementById('mid1').value = '';
    document.getElementById('asgn1').value = '';
    document.getElementById('mid2').value = '';
    document.getElementById('asgn2').value = '';
    document.getElementById('sem').value = '';
    
    openModal();
}

// Edit marks
function editMarks(btn) {
    const row = btn.closest('tr');
    document.getElementById('studentId').value = row.dataset.studentId;
    document.getElementById('courseId').value = row.dataset.courseId;
    document.getElementById('mid1').value = row.dataset.mid1;
    document.getElementById('asgn1').value = row.dataset.asgn1;
    document.getElementById('mid2').value = row.dataset.mid2;
    document.getElementById('asgn2').value = row.dataset.asgn2;
    document.getElementById('sem').value = row.dataset.sem;
    
    openModal();
}

// Save marks
function saveMarks() {
    const data = {
        student_id: document.getElementById('studentId').value,
        course_id: document.getElementById('courseId').value,
        mid1: document.getElementById('mid1').value || null,
        asgn1: document.getElementById('asgn1').value || null,
        mid2: document.getElementById('mid2').value || null,
        asgn2: document.getElementById('asgn2').value || null,
        sem: document.getElementById('sem').value || null
    };

    const action = data.student_id ? 'update_marks' : 'add_marks';

    fetch(`faculty_dashboard.php?action=${action}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'data=' + encodeURIComponent(JSON.stringify(data))
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            closeModal();
            loadMarks(document.getElementById('sectionSelect').value);
        } else {
            alert(res.error || 'Failed to save marks');
        }
    });
}

// Delete marks
function deleteMarks(btn) {
    if (!confirm('Are you sure you want to delete these marks?')) return;

    const row = btn.closest('tr');
    const data = {
        student_id: row.dataset.studentId,
        course_id: row.dataset.courseId
    };

    fetch('faculty_dashboard.php?action=delete_marks', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'data=' + encodeURIComponent(JSON.stringify(data))
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            row.remove();
        } else {
            alert(res.error || 'Failed to delete marks');
        }
    });
}

// Modal helpers
function openModal() {
    document.getElementById('marksModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('marksModal').style.display = 'none';
}

function clearMarksTable() {
    document.querySelector('#marksTable tbody').innerHTML = '';
}