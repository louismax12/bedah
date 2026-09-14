# Panduan Prompt (AI Code Generator) - Sistem Rehabilitasi Medik RS RKZ

Jika *tools* AI (seperti Google IDX, v0.dev, atau Claude) mengalami *error* karena menampung perintah yang terlalu besar (karena harus membuat keseluruhan aplikasi sekaligus), kita harus memecah instruksinya menjadi **langkah demi langkah (Step-by-Step)**. 

Silakan berikan instruksi di bawah ini secara bertahap kepada AI Anda. Biarkan AI menyelesaikan *Step 1* terlebih dahulu, lalu lanjutkan dengan *Step 2* di obrolan yang sama, dan seterusnya.

---

### Step 1: Membuat Layout Utama & Dashboard Admin
**Copy-paste teks ini sebagai instruksi pertama:**

> Act as a Senior Full-Stack Developer. I want to build a "Hospital Medical Rehabilitation System". For this first step, please build the Main Layout and the Admin Dashboard.
> 
> **Requirements:**
> - Use React + Tailwind CSS (or Next.js).
> - Design a 'Premium Enterprise Theme': light mode, slate-gray backgrounds, clean white cards with `rounded-xl`, subtle drop-shadows. Color palette: Trustworthy Blue (primary) and Slate.
> - Build a Collapsible Sidebar with navigation items: Dashboard Admin, Master Patient, Visit Registration, Doctor Dashboard, Reports.
> - Build a Top Navbar with a user avatar.
> - Build the Admin Dashboard page inside the main content area, featuring summary stat cards (Total Patients, Today's Visits) and a beautiful welcome banner with a blue gradient.
> 
> Please generate the code for this layout first.

---

### Step 2: Membuat Halaman Master Pasien & Kunjungan
**Copy-paste teks ini HANYA JIKA Step 1 sudah selesai dengan sukses:**

> Great! Now, let's add two new pages that load in the main content area when I click the sidebar links.
> 
> **1. Master Pasien (Patient Master):**
> - Create a clean data table listing patients (Medical Record No., Name, DoB, Phone). Use zebra striping and a light-blue header.
> - Add a modern form above the table to register a new patient.
> 
> **2. Registrasi Kunjungan (Visit Registration):**
> - Create a form to assign a patient to a doctor's queue. Inputs: Registration No, Select Patient, Select Doctor, Date.
> - Below the form, add a table showing today's active queue with elegant status badges (e.g., green pill for Completed, gray for Draft).
> 
> Please wire these pages to the sidebar navigation using state or routing.

---

### Step 3: Membuat Dashboard Dokter (Antrian Pemeriksaan)
**Copy-paste teks ini HANYA JIKA Step 2 sudah selesai:**

> Awesome! Let's build the Doctor's Dashboard page now.
> 
> **Requirements for Doctor Dashboard:**
> - Create a dedicated dashboard view for doctors. It should have a clean welcome header.
> - The main focus is the "Today's Patient Queue" table/list. 
> - The queue list should look highly polished with large, readable text for the patient's name and Medical Record number.
> - Each row in the queue must have a prominent "Examine / Anamnesis" button (in deep blue or emerald) to start the examination process.
> - Use beautiful pill-shaped badges for the status (e.g., 'Waiting', 'In Progress', 'Done').

---

### Step 4: Membuat Halaman Anamnesis & Body Mapping (Fitur Inti)
**Copy-paste teks ini HANYA JIKA Step 3 sudah selesai:**

> Excellent! Now for the most crucial part: The Doctor's Examination (Anamnesis) page, which opens when a doctor clicks 'Examine' on the queue.
> 
> **Requirements for Anamnesis Page:**
> - **Dynamic Specialization Form:** Add a dropdown to select form type (General, Musculoskeletal, Cardiorespiratory, Neuromuscular).
> - **Conditional Medical Fields:** If 'Musculoskeletal' or 'General', show inputs for "Range of Motion (LGS)" and "Swelling". If 'Cardiorespiratory', hide those inputs.
> - Include common textareas: Admission Diagnosis, Main Complaint, Present Illness History, Physical Palpation, Action Plan.
> 
> **Interactive Body Mapping Component:**
> - On the right side of the page, add a placeholder image of a human body silhouette.
> - Implement an onClick function on the image. When clicked, it should drop a red "Pin/Marker" at the (X, Y) coordinates.
> - When dropping a pin, show a small modal/dialog asking the doctor to select a symptom (Tingling, Pain, Numbness, Wound). 
> - Display the list of all dropped pins in a small table below the image.
> 
> Please integrate this beautifully into the app.

---

### Step 5: Membuat Menu Laporan & Statistik (Opsional namun disarankan)
**Copy-paste teks ini HANYA JIKA Anda butuh fitur tambahan laporan:**

> Perfect! For the final step, let's build a "Reports & Statistics" page for the hospital management.
> 
> **Requirements for Reports Page:**
> - Build a page that displays analytical charts. Use placeholder chart components (like Recharts or simple CSS bar charts).
> - Include a "Visits per Specialization" bar chart and a "Patient Demographics" pie chart.
> - Add a date range filter at the top of the page.
> - Add a data table at the bottom showing a summary log of all recent completed examinations, with an "Export to PDF/Excel" placeholder button.
> - Keep the design consistent with our Premium Enterprise theme.
