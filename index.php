


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteIt_Admin</title>
    <link rel="stylesheet" href="style.css">
    
</head>

<body class="admin-body">
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">Note<span>It</span><span class="exclamation">!</span></div>
            <div class="nav-admin">
                <div class="nav-item active">
                    <span class="solar--notes-broken"></span>
                    All Notes
                </div>
                <div class="nav-item">
                    <span class="material-symbols--favorite-outline"></span>
                    Favorites
                </div>
                <div class="nav-item">
                    <span class="vaadin--archives"></span>
                    Archives</div>
                
                    <a href="login.html" class="nav-item">
                    <span class="hugeicons--logout-04"></span>
                    Logout
                </a>
                
                
            </div>
            <div class="user-info">
               
                <div class="user-text">
                    <p>Hi Jhonard!<br><span>Welcome back.</span></p>
                </div>
            </div>
            <div class="user-avatar"></div>
           
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="headerA">
                
               <div class="title" id="sectionTitle">All Notes</div>
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search">
                    <button class="add-note-btn"><span>+</span> Add Notes</button>
                </div>
            </div>

            <div class="notes-grid">
                
            
           </div>
        </div>
    </div>

                   <!-- Modal for adding notes -->
            <div id="noteModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:1000; justify-content:center; align-items:center;">
            <div style="background:#fff; padding:30px; border-radius:8px; min-width:300px; max-width:90vw; box-shadow:0 2px 10px rgba(0,0,0,0.2); position:relative;">
                <h2>Add Note</h2>
                <label>Title:</label>
                <input type="text" id="modalTitle" style="width:100%; margin-bottom:10px; padding:8px;">
                <label>Content:</label>
                <textarea id="modalContent" rows="4" style="width:100%; margin-bottom:10px; padding:8px;"></textarea>
                <div class="modal-actions" style="text-align:right;">
                    <button id="closeModal" style="margin-right:10px;">Cancel</button>
                    <button id="saveNote">Save</button>
                </div>
                </div>
            </div>
   <script>
  const addBtn = document.querySelector('.add-note-btn');
  const notesGrid = document.querySelector('.notes-grid');
  const modal = document.getElementById('noteModal');
  const closeModal = document.getElementById('closeModal');
  const saveNote = document.getElementById('saveNote');
  const modalTitle = document.getElementById('modalTitle');
  const modalContent = document.getElementById('modalContent');

  // Load notes from localStorage
  const allNotesNav = document.querySelector('.nav-item.active');
const favNotesNav = document.querySelector('.nav-item:nth-child(2)');
const archNotesNav = document.querySelector('.nav-item:nth-child(3)');

function loadNotes(filter = 'all') {
  notesGrid.innerHTML = '';
  
  // Fetch notes from PHP API
  fetch(`notes.php?status=${filter}`)
    .then(response => response.json())
    .then(notes => {
      if (notes.error) {
        console.error(notes.error);
        return;
      }
      
      notes.forEach(note => {
        const noteCard = document.createElement('div');
        noteCard.className = 'note-card';
        noteCard.innerHTML = `
          <div class="note-title">${note.title}
            <button class="dots" data-id="${note.id}">&#x22EE;</button>
            <div class="note-menu">
              <button class="menu-fav">Add to Favorites</button>
              <button class="menu-arch">Archive</button>
              <button class="menu-del">Delete</button>
            </div>
          </div>
          <div class="note-content">
            <p>${note.content}</p>
          </div>
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <span class="note-indicator"></span>
            <div class="note-date">${new Date(note.date_created).toLocaleDateString()}</div>
          </div>
          <div class="note-status">${note.status !== 'normal' ? note.status : ''}</div>
        `;
        notesGrid.appendChild(noteCard);

        // Dots menu logic
        const dotsBtn = noteCard.querySelector('.dots');
        const menu = noteCard.querySelector('.note-menu');
        dotsBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          document.querySelectorAll('.note-menu').forEach(m => m.style.display = 'none');
          menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Delete
        menu.querySelector('.menu-del').addEventListener('click', function() {
          fetch('notes.php', {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: dotsBtn.dataset.id }),
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) loadNotes(filter);
            else console.error(data.error);
          });
        });

        // Add to Favorites
        menu.querySelector('.menu-fav').addEventListener('click', function() {
          fetch('notes.php', {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: dotsBtn.dataset.id, status: 'Favorite' }),
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) loadNotes(filter);
            else console.error(data.error);
          });
        });

        // Archive
        menu.querySelector('.menu-arch').addEventListener('click', function() {
          fetch('notes.php', {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: dotsBtn.dataset.id, status: 'Archived' }),
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) loadNotes(filter);
            else console.error(data.error);
          });
        });
      });
    })
    .catch(error => console.error('Error loading notes:', error));
}

const sectionTitle = document.getElementById('sectionTitle');
// Sidebar navigation toggle
allNotesNav.addEventListener('click', function() {
  allNotesNav.classList.add('active');
  favNotesNav.classList.remove('active');
  archNotesNav.classList.remove('active');
  sectionTitle.textContent = "All Notes";
  sectionTitle.style.color = "#222";
  loadNotes('all');
});
favNotesNav.addEventListener('click', function() {
  allNotesNav.classList.remove('active');
  favNotesNav.classList.add('active');
  archNotesNav.classList.remove('active');
  sectionTitle.textContent = "★ Favorites";
  sectionTitle.style.color = "#06b399";
  loadNotes('favorite');
});
archNotesNav.addEventListener('click', function() {
  allNotesNav.classList.remove('active');
  favNotesNav.classList.remove('active');
  archNotesNav.classList.add('active');
  sectionTitle.textContent = "🗄️ Archives";
  sectionTitle.style.color = "#ff9800";
  loadNotes('archived');
});
  // Show modal
  addBtn.addEventListener('click', function() {
    modal.style.display = 'flex';
    modalTitle.value = '';
    modalContent.value = '';
    modalTitle.focus();
  });

  // Hide modal
  closeModal.addEventListener('click', function() {
    modal.style.display = 'none';
  });

  // Save note
  saveNote.addEventListener('click', function() {
  const title = modalTitle.value.trim();
  const content = modalContent.value.trim();
  if (!title || !content) {
    alert('Please enter both title and content.');
    return;
  }
  
  fetch('notes.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ title, content }),
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      loadNotes('all');
      modal.style.display = 'none';
    } else {
      console.error(data.error);
    }
  });
});

  // Optional: close modal when clicking outside
  modal.addEventListener('click', function(e) {
    if (e.target === modal) modal.style.display = 'none';
  });

  // Initial load
  loadNotes();
</script>
</body>

</html>


