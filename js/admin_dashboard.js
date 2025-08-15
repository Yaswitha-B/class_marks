document.addEventListener('DOMContentLoaded',()=>{
    const tabs=document.querySelectorAll('nav ul li button');
    const sections=document.querySelectorAll('main section');

    tabs.forEach(tab=>{
        tab.addEventListener('click',()=>{
            sections.forEach(s=>s.classList.remove('active'));
            document.getElementById(tab.textContent.toLowerCase().replace(' ','_')).classList.add('active');
        });
    });

    // Fetch all data on load
    fetch('admin_dashboard.php?action=fetch')
    .then(r=>r.json()).then(data=>{
        populateTable('sections',data.section);
        populateTable('faculty',data.faculty);
        populateTable('students',data.student);
        populateTable('courses',data.courses_available);
        populateTable('assigned',data.assigned_class);
    });
});

// Populate tables
function populateTable(type,rows){
    const tbody=document.getElementById(type+'Table')?.querySelector('tbody');
    if(!tbody) return;
    tbody.innerHTML='';
    rows.forEach(r=>{
        const tr=document.createElement('tr');
        if(type==='sections') tr.innerHTML=`<td>${r.section_id}</td><td>${r.year}</td><td>${r.dept}</td><td>${r.faculty_id||''}</td>`;
        if(type==='faculty') tr.innerHTML=`<td>${r.faculty_id}</td><td>${r.dept}</td>`;
        if(type==='students') tr.innerHTML=`<td>${r.student_id}</td><td>${r.section_id}</td>`;
        if(type==='courses') tr.innerHTML=`<td>${r.course_id}</td><td>${r.name}</td><td>${r.year}</td>`;
        if(type==='assigned') tr.innerHTML=`<td>${r.course_id}</td><td>${r.section_id}</td><td>${r.faculty_id}</td>`;
        tbody.appendChild(tr);
    });
}

// Add new rows
window.addSection=()=>addRow('sections');
window.addCourse=()=>addRow('courses');
window.addAssigned=()=>addRow('assigned');

function addRow(type){
    const table=document.getElementById(type+'Table').querySelector('tbody');
    const tr=document.createElement('tr');
    if(type==='sections') tr.innerHTML=`<td contenteditable="true">ID</td><td contenteditable="true">Year</td><td contenteditable="true">Dept</td><td contenteditable="true">FacultyID</td><td><button onclick="saveRow(this,'sections')">Save</button></td>`;
    if(type==='courses') tr.innerHTML=`<td contenteditable="true">ID</td><td contenteditable="true">Name</td><td contenteditable="true">Year</td><td><button onclick="saveRow(this,'courses')">Save</button></td>`;
    if(type==='assigned') tr.innerHTML=`<td contenteditable="true">CourseID</td><td contenteditable="true">SectionID</td><td contenteditable="true">FacultyID</td><td><button onclick="saveRow(this,'assigned')">Save</button></td>`;
    table.appendChild(tr);
}

// Save row via AJAX
window.saveRow=(btn,type)=>{
    const row=btn.closest('tr');
    const cells=row.querySelectorAll('td[contenteditable="true"]');
    let data={};
    if(type==='sections') data={section_id:cells[0].textContent,year:cells[1].textContent,dept:cells[2].textContent,faculty_id:cells[3].textContent};
    if(type==='courses') data={course_id:cells[0].textContent,name:cells[1].textContent,year:cells[2].textContent};
    if(type==='assigned') data={course_id:cells[0].textContent,section_id:cells[1].textContent,faculty_id:cells[2].textContent};

    fetch(`admin_dashboard.php?action=add_${type}`,{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'data='+encodeURIComponent(JSON.stringify(data))
    }).then(r=>r.json()).then(res=>{
        if(res.success){
            cells.forEach(c=>c.setAttribute('contenteditable','false'));
            btn.remove();
        } else alert(res.error);
    });
}

// Tab toggle helper
function showTab(id){
    document.querySelectorAll('main section').forEach(s=>s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}
