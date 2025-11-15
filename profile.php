<?php
session_start();
require 'db.php';

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Fetch user data from the database
$username = $_SESSION['user'];
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If user not found in DB (edge case), destroy session and redirect
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch wishlisted destinations for the logged-in user
$wishlisted_destinations = [];
if ($user['id']) {
    $wishlist_stmt = $conn->prepare("
        SELECT d.id, d.title, d.image_url 
        FROM wishlist w
        JOIN destinations d ON w.destination_id = d.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $wishlist_stmt->bind_param("i", $user['id']);
    $wishlist_stmt->execute();
    $wishlisted_destinations = $wishlist_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch blog count for the user
    $blogs_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM blogs WHERE user_id = ?");
    $blogs_count_stmt->bind_param("i", $user['id']);
    $blogs_count_stmt->execute();
    $blogs_count_result = $blogs_count_stmt->get_result()->fetch_assoc();
    $blogs_count = $blogs_count_result['count'] ?? 0;
} else {
    $blogs_count = 0;
}

// Fetch recent blogs for the user
$recent_blogs = [];
if ($user['id']) {
    $blogs_stmt = $conn->prepare("
        SELECT id, title, content, image 
        FROM blogs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 2
    ");
    $blogs_stmt->bind_param("i", $user['id']);
    $blogs_stmt->execute();
    $recent_blogs = $blogs_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$wishlist_count = count($wishlisted_destinations);
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#0f1724; /* deep navy */
      --card:#0b1220;
      --muted:#9aa4b2;
      --accent:#1d9bf0; /* twitter-like */
      --glass: rgba(255,255,255,0.03);
    }
    *{box-sizing:border-box}
    body{
      margin:0;min-height:100vh;
      font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,Arial;
      background:linear-gradient(180deg,var(--bg) 0%, #071027 100%);
      padding:32px;color:#e6eef8;
    }

    .page-container {
        display: flex;
        gap: 24px;
        align-items: flex-start;
        max-width: 1400px;
        margin: 0 auto;
    }

    .profile-card{
      width:100%;max-width:600px;background:linear-gradient(180deg,var(--card), #071224);
      border-radius:14px;padding:28px;box-shadow:0 10px 30px rgba(2,6,23,0.7);display:grid;grid-template-columns:260px 1fr;gap:24px;align-items:start;border:1px solid rgba(255,255,255,0.04);
    }

    /* Left column */
    .left{
      display:flex;flex-direction:column;gap:18px;align-items:center;padding:8px 6px;
    }
    .avatar-wrap{position:relative; cursor:pointer;}
    .avatar{width:160px;height:160px;border-radius:50%;object-fit:cover;border:6px solid rgba(255,255,255,0.06);box-shadow:0 6px 20px rgba(13,30,55,0.6)}
    .badge{position:absolute;right:0;bottom:4px;background:linear-gradient(90deg,var(--accent),#4cc9ff);padding:6px 10px;border-radius:999px;font-weight:700;font-size:12px;color:#01203a;border:2px solid rgba(255,255,255,0.08)}

    .name{font-size:20px;font-weight:700;margin-top:6px}
    .sub{color:var(--muted);font-size:13px;margin-top:6px;text-align:center}

    /* Styles for inline editing */
    .profile-details-edit-mode input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #e6eef8; padding: 6px 8px; font-family: inherit; font-size: 14px; margin-bottom: 6px; text-align: center; }
    .profile-details-edit-mode .name-group { display: flex; gap: 6px; }

    .btn-remove-avatar { background: transparent; border: 1px solid rgba(217, 83, 79, 0.5); color: #d9534f; padding: 4px 10px; font-size: 12px; border-radius: 6px; cursor: pointer; margin-top: 8px; transition: all 0.3s; }
    .btn-remove-avatar:hover { background: rgba(217, 83, 79, 0.1); }
    /* Right column */
    .right{display:flex;flex-direction:column;gap:16px}
    .top-stats{display:flex;gap:12px}
    .stat{flex:1;background:var(--glass);border-radius:12px;padding:14px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.03)}
    .stat .num{font-weight:800;font-size:20px}
    .stat .lbl{font-size:13px;color:var(--muted);margin-top:4px}

    .bio-card{background:linear-gradient(180deg, rgba(255,255,255,0.02), transparent);padding:14px;border-radius:12px;border:1px solid rgba(255,255,255,0.02);color:var(--muted)}

    .meta-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:12px}
    .meta{background:rgba(255,255,255,0.02);padding:10px;border-radius:10px;font-size:14px}
    .meta strong{display:block;font-weight:700;margin-bottom:6px}
    #bio-edit-controls svg, #bio-edit-controls button { cursor: pointer; }
    #bioTextarea { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e6eef8; padding: 8px; font-family: inherit; font-size: 14px; min-height: 100px; resize: vertical; margin-top: 10px; }
    .btn-save { background: linear-gradient(90deg,var(--accent),#3bb0ff); color: #021426; padding: 4px 10px; font-size: 12px; border-radius: 6px; border: none; font-weight: 600; }

    /* Confirmation Bubble Styles */
    .confirm-bubble-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 36, 0.5);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .confirm-bubble-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    .confirm-bubble-content {
        background: var(--card);
        padding: 24px 32px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        text-align: center;
        max-width: 400px;
        transform: scale(0.95);
        transition: transform 0.3s ease;
    }
    .confirm-bubble-overlay.show .confirm-bubble-content {
        transform: scale(1);
    }
    .confirm-bubble-content h4 { margin: 0 0 10px 0; font-size: 18px; color: #fff; }
    .confirm-bubble-content p { margin: 0 0 20px 0; color: var(--muted); font-size: 14px; }
    .confirm-bubble-actions { display: flex; gap: 12px; justify-content: center; }
    .confirm-bubble-actions button { padding: 8px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; font-family: inherit; transition: background-color 0.3s; }
    .confirm-bubble-actions .btn-cancel { background: var(--glass); color: var(--muted); border: 1px solid rgba(255,255,255,0.1); }
    .confirm-bubble-actions .btn-cancel:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .confirm-bubble-actions .btn-confirm { background: #d9534f; color: #fff; }
    .confirm-bubble-actions .btn-confirm:hover { background: #c9302c; }


    .recent-posts{margin-top:6px}
    .post{padding:12px;border-radius:10px;background:rgba(255,255,255,0.01);border:1px solid rgba(255,255,255,0.02);margin-bottom:10px}
    .post h4{margin:0 0 6px 0}
    .post p{margin:0;color:var(--muted);font-size:13px}

    /* Wishlist Section Styles */
    .wishlist-section {
        margin-top: 24px;
    }
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-top: 12px;
    }
    .wishlist-item {
        background: var(--glass);
        border-radius: 8px;
        overflow: hidden;
        text-align: center;
        text-decoration: none;
        color: #e6eef8;
    }
    .wishlist-item img { width: 100%; height: 100px; object-fit: cover; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .wishlist-item .item-content { position: relative; }
    .wishlist-item .remove-wishlist-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(11, 18, 32, 0.7);
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        color: #fff;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
    }
    .wishlist-item span { display: block; padding: 8px; font-size: 13px; font-weight: 500; }
    /* small screens */
    @media (max-width:820px){
      .page-container { flex-direction: column; align-items: center; }
      .profile-card{grid-template-columns:1fr;max-width:600px}
      .left{flex-direction:row;align-items:center}
      .left .avatar{width:90px;height:90px}
      .name{font-size:16px}
      .blog-previews { width: 100%; max-width: 600px; }
    }

    /* Blog Preview Section */
    .blog-previews { flex: 1; background: var(--card); border-radius: 14px; padding: 28px; border: 1px solid rgba(255,255,255,0.04); }
    .blog-previews h2 { margin-top: 0; font-size: 20px; border-left: 3px solid var(--accent); padding-left: 12px; }
    .blog-post-preview { background: var(--glass); border: 1px solid rgba(255,255,255,0.05); padding: 15px; margin-bottom: 15px; border-radius: 10px; }
    .blog-post-preview h4 { margin: 0 0 8px 0; font-size: 16px; color: #fff; }
    .blog-post-preview .meta { font-size: 12px; color: var(--muted); margin-bottom: 10px; }
    .blog-post-preview p { line-height: 1.6; font-size: 14px; color: #d1dce8; margin: 0; }
    .blog-post-preview .blog-image { width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 15px; border: 1px solid var(--glass); }
    .blog-post-preview a { color: var(--accent); font-weight: 600; font-size: 13px; text-decoration: none; }
    .blog-post-preview a:hover { text-decoration: underline; }


  </style>
</head>
<body>

  <div class="profile-card" id="profileCard">

    <div class="left">
      <!-- Hidden form for file input -->
      <form id="avatarForm" style="display: none;">
          <input type="file" id="avatarInput" name="avatar" accept="image/jpeg, image/png, image/gif">
      </form>
      <div class="avatar-wrap" id="avatarContainer" title="Change profile picture">
        <img id="avatar" class="avatar" src="" alt="Profile">
        <!-- The '+' button to change the avatar -->
        <div class="badge" id="editAvatarBtn"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></div>
      </div>
      <button id="removeAvatarBtn" class="btn-remove-avatar" style="display: none;">Remove Picture</button>

      <div id="profile-details-container" style="text-align:center; width: 100%;">
        <div style="position: relative;">
            <div class="name" id="fullname">First Last</div>
            <div id="profile-edit-controls" style="position: absolute; top: 0; right: 0;"></div>
        </div>
        <div class="sub" id="username">@username</div>
        <div class="sub" id="country">Country</div>
      </div>
    </div>

    <div class="right">
      <!-- Top horizontal stats similar to the request -->
      <div class="top-stats">
        <div class="stat">
          <div class="num" id="wishlistCount"><?php echo $wishlist_count; ?></div>
          <div class="lbl">Wishlist</div>
        </div>
        <div class="stat">
          <div class="num" id="wisdomLevel">0</div>
          <div class="lbl">Level</div>
        </div>
        <div class="stat">
          <div class="num" id="blogsCount"><?php echo $blogs_count; ?></div>
          <div class="lbl">Blogs Posted</div>
        </div>
      </div>

      <div class="bio-card">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <strong style="color:#dbefff">About</strong>
            <span id="bio-edit-controls" style="margin-left: 8px; vertical-align: middle;"></span>
          </div>
          <small style="color:var(--muted);font-size:13px">Member since <span id="memberSince">—</span></small>
        </div>

        <div class="meta-grid">
          <div class="meta"><strong>Location</strong><span id="locationMeta">—</span></div>
          <div class="meta"><strong>Website</strong><span id="websiteMeta">—</span></div>
          <div class="meta"><strong>Followers</strong><span id="followersMeta">0</span></div>
          <div class="meta"><strong>Following</strong><span id="followingMeta">0</span></div>
        </div>

        <!-- Bio content will be displayed here -->
        <p id="bio" style="margin-top:16px;color:var(--muted);font-size:14px; border-top: 1px solid var(--glass); padding-top: 16px;">This user hasn't written a bio yet.</p>
      </div>

      <div class="recent-posts">
        <strong style="display:block;margin-bottom:8px">Recent posts</strong>
        <!-- Example posts, replace with dynamic list -->
        <div class="post"><h4>Welcome to my profile</h4><p>First blog post intro — a short summary to entice clicks.</p></div>
      </div>

      <!-- Wishlist Section -->
      <div class="wishlist-section">
          <strong style="display:block;margin-bottom:8px">My Wishlist</strong>
          <?php if (!empty($wishlisted_destinations)): ?>
              <div class="wishlist-grid">
                  <?php foreach ($wishlisted_destinations as $dest): ?>                      
                      <div class="wishlist-item" data-destination-id="<?php echo $dest['id']; ?>">
                          <div class="item-content">
                              <a href="destination_detail.php?id=<?php echo $dest['id']; ?>">
                                  <img src="<?php echo htmlspecialchars($dest['image_url'] ?? 'img/default_destination.jpg'); ?>" alt="<?php echo htmlspecialchars($dest['title']); ?>">
                              </a>
                              <button class="remove-wishlist-btn" title="Remove from wishlist">&times;</button>
                          </div>
                          <a href="destination_detail.php?id=<?php echo $dest['id']; ?>"><span><?php echo htmlspecialchars($dest['title']); ?></span></a>
                      </div>
                  <?php endforeach; ?>
              </div>
          <?php else: ?>
              <p style="color:var(--muted);font-size:14px;">Your wishlist is empty. Start exploring <a href="destinations.php" style="color:var(--accent); text-decoration:underline;">destinations</a>!</p>
          <?php endif; ?>
      </div>


    </div>
  </div>

  <!-- Confirmation Bubble for Avatar Removal -->
  <div id="confirm-bubble" class="confirm-bubble-overlay">
    <div class="confirm-bubble-content">
        <h4>Remove Picture</h4>
        <p>Are you sure you want to remove your profile picture? This action cannot be undone.</p>
        <div class="confirm-bubble-actions">
            <button id="cancelRemoveBtn" class="btn-cancel">Cancel</button>
            <button id="confirmRemoveBtn" class="btn-confirm">Confirm</button>
        </div>
    </div>
  </div>


  <script>
    // User data is now passed from PHP
    const userData = <?php echo json_encode($user); ?>;

    function populate(u){
      document.getElementById('fullname').textContent = u.firstname + ' ' + u.lastname;
      document.getElementById('username').textContent = '@' + u.username;
      document.getElementById('country').textContent = u.country;
      const removeAvatarBtn = document.getElementById('removeAvatarBtn');
      // If user has an avatar, display it. Otherwise, use a default user icon.
      if(u.avatar) {
          // Add a cache-busting query parameter to ensure the new image loads
          document.getElementById('avatar').style.padding = '0';
          document.getElementById('avatar').src = u.avatar + '?t=' + new Date().getTime();
          removeAvatarBtn.style.display = 'inline-block';
      } else {
          document.getElementById('avatar').src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%239aa4b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
          document.getElementById('avatar').style.padding = '40px'; // Add padding to the default SVG
          removeAvatarBtn.style.display = 'none';
      }
      // document.getElementById('wishlistCount').textContent = u.wishlist ?? 0; // This is now handled by PHP
      document.getElementById('wisdomLevel').textContent = u.wisdom ?? 0;
      // document.getElementById('blogsCount').textContent = u.blogs ?? 0; // This is now handled by PHP
      document.getElementById('bio').textContent = u.bio || "This user hasn't written a bio yet.";
      document.getElementById('memberSince').textContent = u.member_since || '—';
      document.getElementById('locationMeta').textContent = u.country || '—';
      document.getElementById('websiteMeta').textContent = u.website || '—';
      document.getElementById('followersMeta').textContent = u.followers ?? 0;
      document.getElementById('followingMeta').textContent = u.following ?? 0;
    }

    populate(userData);

    // --- Avatar Upload Logic ---
    const avatarContainer = document.getElementById('avatarContainer');
    const avatarInput = document.getElementById('avatarInput');

    // When the avatar container (or the '+' badge) is clicked, trigger the hidden file input
    avatarContainer.addEventListener('click', () => {
        avatarInput.click();
    });

    // When a file is selected, automatically upload it
    avatarInput.addEventListener('change', () => {
        const file = avatarInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('avatar', file);

        // Provide visual feedback during upload
        document.getElementById('avatar').style.opacity = '0.5';

        fetch('upload_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('avatar').style.opacity = '1';
            if (data.success) {
                // Update the image source on the page instantly
                document.getElementById('avatar').src = data.filePath + '?t=' + new Date().getTime();
                // Reset padding in case it was a default icon before
                document.getElementById('avatar').style.padding = '0';
                // Show the remove button now that an avatar exists
                document.getElementById('removeAvatarBtn').style.display = 'inline-block';
            } else {
                alert('Upload failed: ' + data.message);
            }
        })
        .catch(error => {
            document.getElementById('avatar').style.opacity = '1';
            alert('An error occurred during upload.');
            console.error('Error:', error);
        });
    });

    // --- Avatar Removal Logic ---
    const removeAvatarBtn = document.getElementById('removeAvatarBtn');
    const confirmBubble = document.getElementById('confirm-bubble');
    const confirmRemoveBtn = document.getElementById('confirmRemoveBtn');
    const cancelRemoveBtn = document.getElementById('cancelRemoveBtn');

    removeAvatarBtn.addEventListener('click', () => {
        confirmBubble.classList.add('show');
    });

    cancelRemoveBtn.addEventListener('click', () => {
        confirmBubble.classList.remove('show');
    });

    confirmRemoveBtn.addEventListener('click', () => {
        confirmRemoveBtn.textContent = 'Removing...';
        confirmRemoveBtn.disabled = true;

        fetch('remove_avatar.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userData.avatar = null;
                    populate(userData);
                } else {
                    alert('Failed to remove picture: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                confirmBubble.classList.remove('show');
                confirmRemoveBtn.textContent = 'Confirm';
                confirmRemoveBtn.disabled = false;
            });
        });

    // --- Bio Edit Logic ---
    const bioContainer = document.querySelector('.bio-card');
    const bioTextElement = document.getElementById('bio');
    const bioControls = document.getElementById('bio-edit-controls');

    // Create icons and buttons
    const editIcon = `<svg id="editBioBtn" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" title="Edit bio"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`;
    const saveButton = `<button id="saveBioBtn" class="btn btn-save">Save</button>`;

    // Initial state: show edit icon
    bioControls.innerHTML = editIcon;

    bioControls.addEventListener('click', (event) => {
        const target = event.target.closest('#editBioBtn, #saveBioBtn');
        if (!target) return;

        if (target.id === 'editBioBtn') {
            // --- Enter Edit Mode ---
            const textarea = document.createElement('textarea');
            textarea.id = 'bioTextarea';
            textarea.value = userData.bio || ''; // Use the actual data, not the display text
            
            // Replace the bio paragraph with the textarea for editing
            bioTextElement.replaceWith(textarea);
            textarea.focus();

            // Change icon to save button
            bioControls.innerHTML = saveButton;

        } else if (target.id === 'saveBioBtn') {
            // --- Save and Exit Edit Mode ---
            const textarea = document.getElementById('bioTextarea');
            const newBio = textarea.value.trim();

            target.disabled = true;
            target.textContent = 'Saving...';

            fetch('update_bio.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bio: newBio })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userData.bio = newBio; // Update local data object first
                } else {
                    alert('Failed to save bio: ' + data.message);
                }
            })
            .finally(() => {
                // Revert UI
                const currentTextarea = document.getElementById('bioTextarea');
                if (currentTextarea) {
                    currentTextarea.replaceWith(bioTextElement);
                }
                populate(userData); // Now populate the UI with the updated data
                bioControls.innerHTML = editIcon;
            });
        }
    });

    // --- Profile Details Edit Logic ---
    const detailsContainer = document.getElementById('profile-details-container');
    const profileEditControls = document.getElementById('profile-edit-controls');
    const editProfileIcon = `<svg id="editProfileBtn" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" title="Edit profile" style="cursor:pointer;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`;
    const saveProfileButton = `<button id="saveProfileBtn" class="btn-save">Save</button>`;

    profileEditControls.innerHTML = editProfileIcon;

    profileEditControls.addEventListener('click', (event) => {
        const target = event.target.closest('#editProfileBtn, #saveProfileBtn');
        if (!target) return;

        if (target.id === 'editProfileBtn') {
            // --- Enter Edit Mode ---
            detailsContainer.classList.add('profile-details-edit-mode');
            document.getElementById('fullname').innerHTML = `<div class="name-group"><input id="editFirstname" value="${userData.firstname}" placeholder="First"><input id="editLastname" value="${userData.lastname}" placeholder="Last"></div>`;
            document.getElementById('username').innerHTML = `<input id="editUsername" value="${userData.username}" placeholder="Username">`;
            document.getElementById('country').innerHTML = `<input id="editCountry" value="${userData.country}" placeholder="Country">`;
            profileEditControls.innerHTML = saveProfileButton;
        } else if (target.id === 'saveProfileBtn') {
            // --- Save and Exit Edit Mode ---
            const newFirstname = document.getElementById('editFirstname').value.trim();
            const newLastname = document.getElementById('editLastname').value.trim();
            const newUsername = document.getElementById('editUsername').value.trim();
            const newCountry = document.getElementById('editCountry').value.trim();

            target.disabled = true;
            target.textContent = 'Saving...';

            const updatedData = {
                firstname: newFirstname,
                lastname: newLastname,
                username: newUsername,
                country: newCountry
            };

            fetch('update_profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update local userData object with the new values
                    Object.assign(userData, updatedData);
                } else {
                    alert('Failed to save profile: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                // Revert UI from inputs back to text
                detailsContainer.classList.remove('profile-details-edit-mode');
                document.getElementById('fullname').textContent = userData.firstname + ' ' + userData.lastname;
                document.getElementById('username').textContent = '@' + userData.username;
                document.getElementById('country').textContent = userData.country;
                
                // Also update the location meta box if it exists
                if (document.getElementById('locationMeta')) {
                    document.getElementById('locationMeta').textContent = userData.country || '—';
                }

                profileEditControls.innerHTML = editProfileIcon;
            });
        }
    });

    // --- Wishlist Removal Logic ---
    document.querySelector('.wishlist-grid')?.addEventListener('click', async (event) => {
        const removeBtn = event.target.closest('.remove-wishlist-btn');
        if (!removeBtn) return;

        const wishlistItem = removeBtn.closest('.wishlist-item');
        const destinationId = wishlistItem.dataset.destinationId;

        if (!destinationId) return;

        // Optimistically remove the item from the UI
        wishlistItem.style.transition = 'opacity 0.3s ease';
        wishlistItem.style.opacity = '0.5';

        try {
            const response = await fetch('toggle_wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ destination_id: destinationId })
            });
            const data = await response.json();

            if (data.success && !data.is_wishlisted) {
                // Removal was successful on the server, remove from DOM
                wishlistItem.remove();
                // Update the count
                const wishlistCountEl = document.getElementById('wishlistCount');
                wishlistCountEl.textContent = parseInt(wishlistCountEl.textContent) - 1;
            } else {
                // If it failed, revert the UI change
                wishlistItem.style.opacity = '1';
                alert('Error removing from wishlist: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            wishlistItem.style.opacity = '1';
            console.error('Error:', error);
        }
    });
  </script>
</body>
</html>
