<style>
    /* Sidebar */
nav {
    width: 250px;
    background: #141e30;
    min-height: 100vh;
    padding-top: 20px;
    color: #fff;
    box-sizing: border-box;
}

nav a, nav button {
    display: block;
    width: 100%;
    padding: 12px 20px;
    color: #fff;
    text-align: left;
    text-decoration: none;
    font-size: 16px;
    border: none;
    background: none;
    cursor: pointer;
    transition: background 0.3s;
}
nav a:hover, nav button:hover { background: #00cc7a; }
nav button:focus { outline: none; }
button::after { content: " ▾"; float: right; }

/* Collapsible submenu */
.submenu { display: none; background: #1c1c1c; }
.submenu a {
    padding-left: 35px;
    font-size: 14px;
    color: #fff;
    text-decoration: none;
}
.submenu a:hover { background: #00cc7a; }
</style>
<nav>
    <a href="index.php">🏠 Dashboard</a>

    <!-- Collapsible Manage -->
    <button onclick="toggleMenu('manageMenu')">🛠️ Manage</button>
    <div class="submenu" id="manageMenu">
        <a href="/pinaki/admin/manage_songs/songs.php">🎵 Songs</a>
        <a href="/pinaki/admin/manage_artists/artists.php">🎤 Artists</a>
        <a href="/pinaki/admin/manage_genres/genres.php">🎶 Genres</a>
        <a href="/pinaki/admin/manage_categories/categories.php">📁 Categories</a>
        <a href="/pinaki/admin/manage_users/users.php">👥 Users</a>
    </div>

    <a href="/pinaki/admin/feedback.php">💡 User Feedback</a>
    <a href="/pinaki/admin/profile.php">👤 Profile</a>
    <a href="/pinaki/admin/logout_admin.php">🚪 Logout</a>
</nav>