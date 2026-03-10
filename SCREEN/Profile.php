<?php
session_start();

// --- LOGIN PROTECTION ---
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // redirect if not logged in
    exit();
}

$username = $_SESSION['username'];

// Optional: default avatar
$defaultAvatar = "https://i.pravatar.cc/150?img=3";

// For demo: store avatar in session (replace with DB in real app)
if(!isset($_SESSION['avatar'])){
    $_SESSION['avatar'] = $defaultAvatar;
}
$avatar = $_SESSION['avatar'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ScreenHub | Profile Settings</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { margin:0; font-family:'Inter', sans-serif; background:#0f0f0f; color:#fff; display: flex; flex-direction: column; min-height: 100vh; }
  header { background:#111; padding:15px 20px; display:flex; justify-content:center; border-bottom: 1px solid #222; }
  .logo { font-family:'Bebas Neue'; font-size:30px; letter-spacing:2px; color:#FF7F50; text-decoration: none; }

  .profile-container { max-width: 450px; width: 90%; margin: 40px auto; background: #141414; padding: 40px; border-radius: 8px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); }
  .profile-container h2 { margin-top: 0; font-weight: 500; color: #fff; font-size: 28px; margin-bottom: 25px; text-align: center; }

  /* --- AVATAR SELECTION --- */
  .avatar-label { font-size: 14px; color: #8c8c8c; margin-bottom: 10px; display: block; }
  .avatar-section { display: flex; gap: 12px; margin-bottom: 30px; flex-wrap: wrap; align-items: center; }
  
  .avatar-section img, .upload-box {
    width: 65px; height: 65px; border-radius: 4px; cursor: pointer;
    border: 3px solid transparent; transition: 0.2s; background: #333; object-fit: cover;
  }
  
  .avatar-section img.selected, .upload-box.selected { border-color: #FF7F50; transform: scale(1.05); }

  /* --- UPLOAD BOX STYLE --- */
  .upload-box {
    display: flex; align-items: center; justify-content: center;
    border: 2px dashed #555; background: #222; position: relative; overflow: hidden;
  }
  .upload-box:hover { border-color: #FF7F50; }
  .upload-box span { font-size: 24px; color: #8c8c8c; pointer-events: none; }
  .upload-preview { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: none; }

  /* --- INPUT FIELDS --- */
  .profile-field { position: relative; margin-bottom: 20px; }
  .profile-field input {
    width: 100%; padding: 22px 12px 10px; background: #333; border: none;
    border-bottom: 2px solid transparent; border-radius: 4px; color: #fff;
    font-size: 16px; box-sizing: border-box; outline: none; transition: 0.2s;
  }
  .profile-field input:focus { background: #454545; border-bottom-color: #FF7F50; }
  .profile-field label { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #8c8c8c; pointer-events: none; transition: 0.2s; font-size: 16px; }

  .profile-field input:focus + label,
  .profile-field input:not(:placeholder-shown) + label { top: 10px; transform: translateY(0); font-size: 11px; font-weight: 700; color: #FF7F50; }

  .show-hide-btn { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #8c8c8c; cursor: pointer; font-size: 12px; font-weight: 600; }

  .save-btn { width: 100%; padding: 14px; border: none; border-radius: 4px; background: #FF7F50; color: #fff; font-weight: 600; font-size: 16px; cursor: pointer; transition: 0.2s; margin-top: 10px; }
  .save-btn:hover { background: #ff6333; }
  .back-link { text-align: center; color: #8c8c8c; text-decoration: none; font-size: 14px; margin-top: 15px; display: block; }

  .error { color: #eb3942; font-size: 13px; margin-bottom: 15px; display: none; text-align: center; }
  .success { color: #46d369; font-size: 13px; margin-bottom: 15px; display: none; text-align: center; }
</style>
</head>
<body>

<header><a href="dashboard.php" class="logo">SCREENHUB</a></header>

<div class="profile-container">
  <h2>Edit Profile</h2>

  <span class="avatar-label">Choose an Avatar</span>
  <div class="avatar-section" id="avatarSection">
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ_SPusQKlIbccNAg4XvAfxv_LGwBrvq9mygg&s"/>
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ8eqdxnZ5tNfHI_g1onJIHUW-L9ZLternX-A&s"/>
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTiY2OGOnZYE4qfqLrhLJiqgMrmKfxbcHIWTA&s"/>
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQyJuXLvgfguKV65sqbXWOvdjobbwbcoTFiQQ&s"/>
    
    <div class="upload-box" id="uploadBox">
      <span>+</span>
      <img id="uploadPreview" class="upload-preview" />
      <input type="file" id="fileInput" accept="image/*" style="display: none;">
    </div>
  </div>

  <form id="profileForm" method="POST">
    <div class="profile-field">
      <input type="text" id="username" name="username" placeholder=" " value="<?php echo htmlspecialchars($username); ?>" required>
      <label>Username</label>
    </div>

    <div style="color: #8c8c8c; font-size: 12px; margin-bottom: 15px;">Change Password (optional)</div>

    <div class="profile-field">
      <input type="password" id="password" name="password" placeholder=" ">
      <label>New Password</label>
      <button type="button" class="show-hide-btn" data-target="password">SHOW</button>
    </div>

    <div class="profile-field">
      <input type="password" id="confirmPassword" name="confirmPassword" placeholder=" ">
      <label>Confirm New Password</label>
      <button type="button" class="show-hide-btn" data-target="confirmPassword">SHOW</button>
    </div>

    <div id="errorBox" class="error"></div>
    <div id="successBox" class="success">Changes saved!</div>

    <button type="submit" class="save-btn">Save Settings</button>
    <a href="dashboard.php" class="back-link">Cancel</a>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const avatars = document.querySelectorAll("#avatarSection > img");
  const uploadBox = document.getElementById("uploadBox");
  const uploadPreview = document.getElementById("uploadPreview");
  const fileInput = document.getElementById("fileInput");

  let selectedAvatar = "<?php echo $avatar; ?>";

  // 1. Highlight selected avatar
  function setInitialSelection() {
    let matched = false;
    avatars.forEach(img => {
      if(img.src === selectedAvatar){
        img.classList.add("selected");
        matched = true;
      }
    });
    if(!matched && selectedAvatar.startsWith("data:image")){
      uploadPreview.src = selectedAvatar;
      uploadPreview.style.display = "block";
      uploadBox.classList.add("selected");
    }
  }
  setInitialSelection();

  // 2. Avatar click
  avatars.forEach(img => {
    img.addEventListener("click", () => {
      avatars.forEach(i => i.classList.remove("selected"));
      uploadBox.classList.remove("selected");
      img.classList.add("selected");
      selectedAvatar = img.src;
    });
  });

  // 3. Upload box
  uploadBox.addEventListener("click", () => fileInput.click());
  fileInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if(file){
      const reader = new FileReader();
      reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
          const canvas = document.createElement("canvas");
          const MAX_WIDTH = 150;
          const scaleSize = MAX_WIDTH / img.width;
          canvas.width = MAX_WIDTH;
          canvas.height = img.height * scaleSize;
          const ctx = canvas.getContext("2d");
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
          const resizedDataUrl = canvas.toDataURL("image/jpeg", 0.7);
          uploadPreview.src = resizedDataUrl;
          uploadPreview.style.display = "block";
          avatars.forEach(i => i.classList.remove("selected"));
          uploadBox.classList.add("selected");
          selectedAvatar = resizedDataUrl;
        };
        img.src = event.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

  // 4. Show/hide password
  document.querySelectorAll(".show-hide-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      const input = document.getElementById(btn.dataset.target);
      input.type = input.type === "password" ? "text" : "password";
      btn.textContent = input.type === "password" ? "SHOW" : "HIDE";
    });
  });

  // 5. Save form
  const form = document.getElementById("profileForm");
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const errorBox = document.getElementById("errorBox");

    if(password !== "" && password !== confirmPassword){
      errorBox.textContent = "Passwords do not match!";
      errorBox.style.display = "block";
      return;
    }

    // Save to server or session (demo: sessionStorage via JS)
    fetch('update_profile.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({username, password, avatar: selectedAvatar})
    }).then(res => res.json()).then(data => {
      if(data.success){
        document.getElementById("successBox").style.display = "block";
        setTimeout(() => window.location.href = "dashboard.php", 1000);
      } else {
        errorBox.textContent = data.error || "Something went wrong!";
        errorBox.style.display = "block";
        // Fallback: redirect after 2 seconds even if error
        setTimeout(() => window.location.href = "dashboard.php", 2000);
      }
    }).catch(() => {
      // Fallback: redirect after 2 seconds if fetch fails
      setTimeout(() => window.location.href = "dashboard.php", 2000);
    });
  });
});
</script>
</body>
</html>