function showTab(tab) {
        document.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("active"));
    document.querySelectorAll(".tab-content").forEach(content => content.classList.remove("active"));
    document.querySelector(`.tab-btn:nth-child(${tab === 'login' ? 1 : 2})`).classList.add("active");
    document.getElementById(tab).classList.add("active");
    
    // Reset role selection when switching tabs
    document.getElementById("role").value = "";
    document.getElementById("extraFields").innerHTML = "";
    document.getElementById("login_role").value = "";
    document.getElementById("register_role").value = "";
}



function toggleForms() {
    const role = document.getElementById("role").value;
    const extraFields = document.getElementById("extraFields");
    const loginRole = document.getElementById("login_role");
    const registerRole = document.getElementById("register_role");

    // Update hidden role fields
    loginRole.value = role;
    registerRole.value = role;

    // Clear previous extra fields
    extraFields.innerHTML = "";

    if (role === "faculty") {
        // Create department dropdown for faculty
        const deptSelect = document.createElement("select");
        deptSelect.name = "dept";
        deptSelect.required = true;

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "-- Select Department --";
        deptSelect.appendChild(defaultOption);

        departmentsData.forEach(dept => {
            const option = document.createElement("option");
            option.value = dept;
            option.textContent = dept;
            deptSelect.appendChild(option);
        });

        const label = document.createElement("label");
        label.textContent = "Department:";

        extraFields.appendChild(label);
        extraFields.appendChild(deptSelect);
    } else if (role === "student") {
        // Create section dropdown for student
        const sectionSelect = document.createElement("select");
        sectionSelect.name = "section_id";
        sectionSelect.required = true;

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "-- Select Section --";
        sectionSelect.appendChild(defaultOption);

        sectionsData.forEach(section => {
            const option = document.createElement("option");
            option.value = section.section_id;
            option.textContent = section.section_id;
            sectionSelect.appendChild(option);
        });

        const label = document.createElement("label");
        label.textContent = "Section:";

        extraFields.appendChild(label);
        extraFields.appendChild(sectionSelect);
    }
}

