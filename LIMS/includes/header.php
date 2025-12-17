<style> 
    .site-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #9333b8;
        color: white;
        padding: 30px;
        flex-wrap: wrap;
    }

    .header-left {
        flex: 1;
        min-width: 250px;
    }

    .header-left h1 {
        margin: 0;
        font-size: 1.8rem;
    }

    .navbar {
        margin-top: 25px;
    }

    .navbar a {
        background-color: #ea7a18;
        color: white;
        padding: 8px 16px;
        margin-right: 8px;
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .navbar a:hover {
        background-color: #c86515;
    }

    .logo {
        text-align: right;
    }

    .logo img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid white;
        object-fit: cover;
    }

    @media screen and (max-width: 768px) {
        .site-header {
            flex-direction: column;
            text-align: center;
        }

        .logo {
            margin-top: 10px;
        }

        .navbar a {
            display: inline-block;
            margin: 5px 5px;
        }
    }
</style>

<header class="site-header">
    <div class="header-left">
        <h1><b>Dar es Salaam Tumaini University</b></h1>
        <nav class="navbar">
            <a href="front/student_lost.html">Lost ID</a>
            <a href="front/track_status.php">Track ID</a>
            <a href="front/login.php">Login</a>
            <a href="https://osim.dartu.ac.tz">OSIM</a>
        </nav>
    </div>
    <div class="logo">
        <img src="assets/dar1.png" alt="DarTU Logo">
    </div>
</header>
