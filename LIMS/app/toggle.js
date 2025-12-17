    function toggleSidebar() {
      const sidebar = document.getElementById("sidebar");
      sidebar.classList.toggle("visible");
    }

    function toggleSubmenu(event, id) {
      event.preventDefault();
      const submenu = document.getElementById(id);
      submenu.classList.toggle("visible");
    }