<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Dashboard</title><link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
<a class="brand" href="../index.html">Intern<span>Connect</span></a>
<div><a href="dashboard.php">Admin Dashboard</a><a href="../auth/logout.php" class="nav-btn">Logout</a></div>
</nav>
<main class="dashboard">
<div class="welcome"><div><span class="badge">Administrator</span><h1>Admin Dashboard</h1><p>Manage internships and student applications.</p></div></div>
<div class="stats-grid" id="stats"></div>

<section class="panel">
<h2>Add New Internship</h2>
<div id="message" class="message"></div>
<form id="internshipForm" class="form-grid">
<label>Title<input id="title" required placeholder="Web Developer Intern"></label>
<label>Company<input id="company" required placeholder="ABC Technologies"></label>
<label>Location<input id="location" required placeholder="Ahmedabad / Remote"></label>
<label>Duration<input id="duration" required placeholder="3 Months"></label>
<label>Stipend<input id="stipend" required placeholder="₹10,000/month"></label>
<label>Deadline<input id="deadline" type="date" required></label>
<label class="full-field">Required Skills<input id="skills_required" required placeholder="HTML, CSS, JavaScript, PHP"></label>
<label class="full-field">Description<textarea id="description" required></textarea></label>
<button class="btn primary" type="submit">Add Internship</button>
</form>
</section>

<section class="panel">
<h2>Internships</h2>
<div class="table-wrap"><table><thead><tr><th>Title</th><th>Company</th><th>Location</th><th>Deadline</th><th>Action</th></tr></thead><tbody id="internRows"></tbody></table></div>
</section>

<section class="panel">
<h2>Student Applications</h2>
<div class="table-wrap"><table><thead><tr><th>Student</th><th>Internship</th><th>Company</th><th>Applied</th><th>Status</th></tr></thead><tbody id="appRows"></tbody></table></div>
</section>
</main>
<script>
async function api(action, options={}){
 const r=await fetch("api.php?action="+action,options);
 if(r.status===401){location.href="../login.html";return null;}
 return r.json();
}
async function load(){
 const s=await api("stats");
 document.getElementById("stats").innerHTML=`
 <div class="stat"><span>Students</span><strong>${s.students}</strong></div>
 <div class="stat"><span>Internships</span><strong>${s.internships}</strong></div>
 <div class="stat"><span>Applications</span><strong>${s.applications}</strong></div>
 <div class="stat"><span>Selected</span><strong>${s.selected}</strong></div>`;

 const interns=await api("internships");
 document.getElementById("internRows").innerHTML=interns.map(x=>`
 <tr><td>${x.title}</td><td>${x.company}</td><td>${x.location}</td><td>${x.deadline}</td>
 <td><button class="table-btn danger" onclick="deleteInternship(${x.id})">Delete</button></td></tr>`).join("");

 const apps=await api("applications");
 document.getElementById("appRows").innerHTML=apps.length?apps.map(x=>`
 <tr><td>${x.student_name}<br><small>${x.email}</small></td><td>${x.title}</td><td>${x.company}</td><td>${x.applied_at}</td>
 <td><select onchange="updateStatus(${x.id},this.value)">
 <option ${x.status==="Pending"?"selected":""}>Pending</option>
 <option ${x.status==="Shortlisted"?"selected":""}>Shortlisted</option>
 <option ${x.status==="Selected"?"selected":""}>Selected</option>
 <option ${x.status==="Rejected"?"selected":""}>Rejected</option>
 </select></td></tr>`).join(""):`<tr><td colspan="5">No applications yet.</td></tr>`;
}
document.getElementById("internshipForm").addEventListener("submit",async e=>{
 e.preventDefault();
 const data={title:title.value,company:company.value,location:location.value,duration:duration.value,stipend:stipend.value,deadline:deadline.value,skills_required:skills_required.value,description:description.value};
 const d=await api("add_internship",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(data)});
 const m=document.getElementById("message");m.textContent=d.message;m.className="message "+(d.success?"success":"error");
 if(d.success){e.target.reset();load();}
});
async function updateStatus(id,status){
 const d=await api("update_status",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id,status})});
 if(d.success) load();
}
async function deleteInternship(id){
 if(!confirm("Delete this internship? Applications for it will also be removed."))return;
 const d=await api("delete_internship",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id})});
 if(d.success) load();
}
load();
</script>
</body>
</html>