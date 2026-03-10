<?php
session_start();

// --- LOGIN PROTECTION ---
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // redirect if not logged in
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ScreenHub | Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }

  body { background:#0f0f0f; color:#fff; overflow-x: hidden; }

  /* --- TOPBAR --- */
  .topbar { display:flex; justify-content:space-between; align-items:center; background:#111; padding:10px 20px; box-shadow:0 3px 10px rgba(0,0,0,0.5); position:sticky; top:0; z-index:100; }

  .logo { font-family:'Bebas Neue'; font-size:32px; letter-spacing:2px; color:#FF7F50; }

  /* --- NAVIGATION --- */
  .menu { display:flex; gap:25px; position:relative; }
  .menu a {
    color:#ddd;
    text-decoration:none;
    font-weight:500;
    font-size:15px;
    padding:5px 0;
    position:relative;
    transition: transform 0.2s, color 0.3s;
  }

  /* Underline animation */
  .menu a::after {
    content:'';
    position:absolute;
    width:0%;
    height:2px;
    background:#FF7F50;
    left:0;
    bottom:-3px;
    transition: width 0.3s ease;
  }

  .menu a:hover::after {
    width:100%;
  }

  /* Press animation */
  .menu a:active {
    transform: scale(0.9);
    color:#fff;
  }

  /* --- PROFILE DROPDOWN --- */
  .profile-container { position: relative; display: flex; align-items: center; cursor: pointer; padding: 5px 0; }
  .profile-area { display:flex; align-items:center; gap:10px; }
  .profile-area img { width:35px; height:35px; border-radius:4px; object-fit:cover; transition:0.3s; }
  .caret { width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 5px solid #fff; margin-left: 8px; transition: transform 0.3s ease; }
  .profile-dropdown { position: absolute; top: 100%; right: 0; background: rgba(0, 0, 0, 0.95); border: 1px solid rgba(255, 255, 255, 0.15); min-width: 180px; display: none; flex-direction: column; padding: 10px 0; box-shadow: 0 10px 20px rgba(0,0,0,0.5); margin-top: 5px; }
  .profile-dropdown a { color: #fff; padding: 10px 20px; text-decoration: none; font-size: 13px; margin-left: 0 !important; display: block; transition: background 0.2s; }
  .profile-dropdown a:hover { background: rgba(255,255,255,0.1); text-decoration: underline; }
  .profile-dropdown hr { border: none; border-top: 1px solid rgba(255, 255, 255, 0.2); margin: 5px 0; }
  .profile-container:hover .profile-dropdown { display: flex; }
  .profile-container:hover .caret { transform: rotate(180deg); }

  /* --- HERO VIDEO SECTION --- */
  .hero { position:relative; width:100%; height:80vh; overflow:hidden; margin-bottom:30px; }
  .hero-video { position:absolute; width:100%; height:100%; object-fit:cover; top:0; left:0; z-index: -1; }
  .hero-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.3)); z-index:1; }
  .hero-content { position:absolute; bottom:50px; left:50px; z-index:2; max-width:600px; }
  .hero-content h1 { font-size:48px; font-family:'Bebas Neue'; margin-bottom:15px; }
  .hero-content p { font-size:16px; line-height:1.5; margin-bottom:20px; color:#ddd; }
  .hero-buttons button { padding:12px 22px; margin-right:10px; border:none; border-radius:6px; font-weight:600; cursor:pointer; transition:0.3s; }
  .play-btn { background:#FF7F50; color:#fff; }
  .play-btn:hover { background:#ff9b70; }
  .info-btn { background:rgba(109,109,110,0.7); color:#fff; }
  .info-btn:hover { background:rgba(109,109,110,0.9); }

  /* --- FILTER BAR --- */
  .filter-bar { display:flex; justify-content:space-between; align-items:center; padding:15px 20px; background:#111; flex-wrap:wrap; gap:10px; }
  .genres span { margin-right:10px; padding:6px 14px; border-radius:20px; background:#222; cursor:pointer; transition:0.3s; font-size:14px; }
  .genres span:hover { background:#FF7F50; color:#fff; }
  .search-box { display:flex; gap:5px; }
  .search-box input { padding:6px 12px; border-radius:6px; border:none; outline:none; font-size:14px; }
  .search-box button { padding:6px 12px; border:none; background:#FF7F50; color:#fff; border-radius:6px; cursor:pointer; transition:0.3s; }

  /* --- DASHBOARD CONTENT --- */
  main.dashboard { padding:0 20px 50px 20px; }
  .section-title { font-size:28px; margin:20px 0 10px; color:#FF7F50; }

  .highlight-grid { display:flex; flex-wrap:wrap; gap:20px; justify-content:space-between; }
  .highlight-card { position:relative; flex:1 1 48%; min-height:350px; background-size:cover; background-position:center; border-radius:12px; overflow:hidden; cursor:pointer; transition:transform 0.3s; }
  .highlight-card:hover { transform:scale(1.05); }
  .highlight-content { position:absolute; bottom:0; background:linear-gradient(transparent, rgba(0,0,0,0.85)); width:100%; padding:20px; box-sizing:border-box; }
  .highlight-content h3 { margin:0; font-size:22px; }
  .highlight-content .info { font-size:13px; color:#ddd; margin:5px 0; }
  .highlight-content .desc { font-size:15px; margin-bottom:10px; }
  .watch-btn { padding:8px 16px; border:none; background:#FF7F50; border-radius:6px; cursor:pointer; color:#fff; font-weight:500; transition:0.3s; }
  .watch-btn:hover { background:#ff9b70; }

  /* --- MOVIE ROWS --- */
  .movie-row { display:flex; overflow-x:auto; gap:15px; padding: 10px 0 30px 0; cursor: grab; user-select:none; scrollbar-width:none; }
  .movie-row::-webkit-scrollbar { display:none; }
  .movie-row:active { cursor:grabbing; }
  .movie-card { width:140px; height:210px; border-radius:10px; overflow:hidden; flex-shrink:0; transition: transform 0.3s ease; background:#1a1a1a; }
  .movie-card img { width:100%; height:100%; object-fit:cover; pointer-events:none; display:block; }
  .movie-card:hover { transform:scale(1.08); }

  .logout-btn { position:fixed; bottom:20px; right:20px; padding:10px 16px; background:#FF7F50; border:none; border-radius:8px; cursor:pointer; font-weight:600; color:#fff; transition:0.3s; display:none; }
</style>
</head>
<body>

<header class="topbar">
  <div class="logo">SCREENHUB</div>
  <nav class="menu">
    <a href="#">Home</a>
    <a href="#">Discover</a>
    <a href="#">Movies</a>
    <a href="#">TV Series</a>
    <a href="#">Channels</a>
  </nav>

  <div class="profile-container">
    <div class="profile-area" id="profileArea">
      <img id="profileImg" src="https://i.pravatar.cc/150?img=3" alt="Profile">
      <span id="profileName"><?php echo $username; ?></span>
      <div class="caret"></div>
    </div>
    
    <div class="profile-dropdown">
      <a href="profile.php">Manage Profiles</a>
      <a href="#">Account</a>
      <a href="#">Help Center</a>
      <hr>
      <a href="logout.php">Sign out of ScreenHub</a>
    </div>
  </div>
</header>

<!-- ===== HERO VIDEO ===== -->
<section class="hero">
  <video autoplay muted loop class="hero-video">
    <source src="Predator Badlands  Official Trailer - 20th Century Studios (1080p, h264).mp4" type="video/mp4">
  </video>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <h1>Predator: Badlands</h1>
    <p>Banished from his group, a young Predator forms an unexpected partnership with a human survivor in the dangerous Badlands.</p>
    <div class="hero-buttons">
      <button class="play-btn">▶ Play</button>
      <button class="info-btn">ℹ More Info</button>
    </div>
  </div>
</section>

<section class="filter-bar">
  <div class="genres">
    <span>Action</span><span>Adventure</span><span>Animation</span><span>Comedy</span><span>Crime</span><span>Drama</span><span>Fantasy</span>
  </div>
  <div class="search-box">
    <input type="text" placeholder="Search movies or TV shows">
    <button>Search</button>
  </div>
</section>

<main class="dashboard">
  <h2 class="section-title">Highlights</h2>
  <div class="highlight-grid">
    <div class="highlight-card" style="background-image:url('predator_badlands_ver5.jpg')">
      <div class="highlight-content">
        <h3>Predator: Badlands</h3>
        <p class="info">IMDb 7.3 • Action, Sci-Fi • 1h 47m</p>
        <p class="desc">Banished from his group, a young Predator forms an unexpected partnership...</p>
        <button class="watch-btn">▶ Watch Now</button>
      </div>
    </div>
    <div class="highlight-card" style="background-image:url('frankenstein_ver9.jpg')">
      <div class="highlight-content">
        <h3>Frankenstein</h3>
        <p class="info">IMDb 7.5 • Drama, Horror • 2h 29m</p>
        <p class="desc">In the pursuit of scientific glory, Dr. Victor Frankenstein defies the laws of nature...</p>
        <button class="watch-btn">▶ Watch Now</button>
      </div>
    </div>
  </div>

  <h2 class="section-title">Latest Movies</h2>
  <div class="movie-row">
    <div class="movie-card"><img src="toy_story_five.jpg"></div>
    <div class="movie-card"><img src="captain_america_brave_new_world.jpg"></div>
    <div class="movie-card"><img src="thunderbolts.jpg"></div>
    <div class="movie-card"><img src="ballerina.jpg"></div>
    <div class="movie-card"><img src="avengers_doomsday.jpg"></div>
    <div class="movie-card"><img src="conjuring_last_rites.jpg"></div>
    <div class="movie-card"><img src="smile_two.jpg"></div>
    <div class="movie-card"><img src="scream_seven.jpg"></div>
    <div class="movie-card"><img src="running_man.jpg"></div>
    <div class="movie-card"><img src="wicked_ver2.jpg"></div>
    <div class="movie-card"><img src="sinister.jpg"></div>
    <div class="movie-card"><img src="it.jpg"></div>
    <div class="movie-card"><img src="john_wick.jpg"></div>
    <div class="movie-card"><img src="deliver_us_from_evil.jpg"></div>
    <div class="movie-card"><img src="shining_ver2.jpg"></div>
  </div>

  <h2 class="section-title">Popular on ScreenHub</h2>
  <div class="movie-row">
    <div class="movie-card"><img src="https://static.wikia.nocookie.net/thesquidgame/images/7/7d/GdWDj4ZaoAEgw26.jpg/revision/latest?cb=20241214072040"></div>
    <div class="movie-card"><img src="https://dnm.nflximg.net/api/v6/2DuQlx0fM4wd1nzqm5BFBi6ILa8/AAAAQaABPKhcZssj_ndHel2IijK8vCVD5YjLRj3X9Db3YWxhGK-cm6OVrXpu5rswYOWfvnBwNm54e8RFMXJBrAg72QXnUQH2DPA0WcSk1riw8R5MYgGp9z0ziPlpCuTGEh8JL65bnE4m1xkm3dwG6gmDoi5V.jpg?r=cc8"></div>
    <div class="movie-card"><img src="https://m.media-amazon.com/images/M/MV5BMDE1NjNmZjgtZTg0OC00NjkxLWEzYzItMDNkMTc3YjgxZWQyXkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg"></div>
    <div class="movie-card"><img src="https://upload.wikimedia.org/wikipedia/en/6/6c/The_Crown_season_1.jpeg"></div>
    <div class="movie-card"><img src="https://m.media-amazon.com/images/I/61boFr6SYZL.jpg"></div>
    <div class="movie-card"><img src="https://m.media-amazon.com/images/S/compressed.photo.goodreads.com/books/1506806332i/35395986.jpg"></div>
    <div class="movie-card"><img src="https://resizing.flixster.com/lpJkDxnEFNQT1OWJjnmYfvpAHJ0=/ems.cHJkLWVtcy1hc3NldHMvdHZzZXJpZXMvUlRUVjI2NjgyOS53ZWJw"></div>
    <div class="movie-card"><img src="https://m.media-amazon.com/images/M/MV5BYjA3NDkwNzktNjJkYi00ODNhLWFhYzQtYzk5NjU4MDM0OWZmXkEyXkFqcGc@._V1_.jpg"></div>
    <div class="movie-card"><img src="https://dnm.nflximg.net/api/v6/mAcAr9TxZIVbINe88xb3Teg5_OA/AAAABQy-HH9EQheOt3yhvkFNJ6UD581ekiegoq_jibPvlouqFRGBZIUY2bfVJ-KtifSrspzV9jBfK7O-BYEeEswFA3UWz2zJv5Il3cuC.jpg?r=7d3"></div>
    <div class="movie-card"><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQrpQa4kPsu6vyXAGfOOHSuscIGwdQuxyOs0Lp-EndlqkhFdcxNPg0kt-lws9e49GAqx2wxRg&s=10"></div>
    <div class="movie-card"><img src="https://upload.wikimedia.org/wikipedia/en/2/29/Movie_poster_for_%22Scary_Movie%22.jpg"></div>
    <div class="movie-card"><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRERn_SoBQnaWZziqldbUJ1_LYBQYmW1SRPlZXvAXh2E0HsQDPY3Aow5UTnUuCP3qJrTQlq&s=10"></div>
    <div class="movie-card"><img src="https://upload.wikimedia.org/wikipedia/en/1/10/Godzilla_%282014%29_poster.jpg"></div>
  </div>
</main>

<button class="logout-btn" id="logoutBtn">Logout</button>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // --- DRAG TO SCROLL (REUSABLE FOR ALL ROWS) ---
  const sliders = document.querySelectorAll('.movie-row');
  
  sliders.forEach(slider => {
    let isDown = false;
    let startX;
    let scrollLeft;

    slider.addEventListener('mousedown', (e) => {
      isDown = true;
      startX = e.pageX - slider.offsetLeft;
      scrollLeft = slider.scrollLeft;
    });
    
    slider.addEventListener('mouseleave', () => isDown = false);
    slider.addEventListener('mouseup', () => isDown = false);
    
    slider.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - slider.offsetLeft;
      const walk = (x - startX) * 2; 
      slider.scrollLeft = scrollLeft - walk;
    });
  });

  // --- NAVIGATION ON PROFILE CLICK ---
  document.getElementById("profileArea").addEventListener("click", (e) => {
    if(e.target.closest('.profile-dropdown')) return;
    window.location.href = "profile.php";
  });
});
</script>

</body>
</html>