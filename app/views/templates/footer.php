      </main>
    </div>
  </div>

  <script>
    lucide.createIcons();

    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');

    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('toggled');
      });
    }

    if (sidebarClose && sidebar) {
      sidebarClose.addEventListener('click', () => {
        sidebar.classList.remove('toggled');
      });
    }

    document.addEventListener('click', (e) => {
      if (
        window.matchMedia('(orientation: portrait)').matches &&
        sidebar &&
        sidebar.classList.contains('toggled') &&
        !sidebar.contains(e.target) &&
        sidebarToggle &&
        !sidebarToggle.contains(e.target)
      ) {
        sidebar.classList.remove('toggled');
      }
    });

    const flashMessages = document.querySelectorAll('.flash-message');

    flashMessages.forEach(msg => {
      setTimeout(() => {
        msg.style.transition = 'opacity 0.5s';
        msg.style.opacity = '0';

        setTimeout(() => {
          msg.remove();
        }, 500);
      }, 4000);
    });
  </script>
</body>
</html>