</main>
</div>
</div>

<?php if (isset($user)): ?>
	<script>
		window.APP_SESSION = {
			timeout: <?= (int) SESSION_TIMEOUT ?>,
			warning: <?= (int) SESSION_WARNING ?>,
			expiresAt: <?= (int) ($_SESSION['last_activity'] ?? time()) + (int) SESSION_TIMEOUT ?>,
			extendUrl: <?= json_encode(BASEURL . '/auth/extendSession', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
			logoutUrl: <?= json_encode(BASEURL . '/auth/logout', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
		};
	</script>
	<script src="<?= BASEURL ?>/assets/js/session-timeout.js"></script>
<?php endif; ?>

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
			window.innerWidth <= 768 &&
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
			msg.style.transition = 'all 0.5s ease';
			msg.style.opacity = '0';
			msg.style.transform = 'translateY(-20px)';

			setTimeout(() => {
				msg.remove();
			}, 500);
		}, 4000);
	});

	const navLinks = document.querySelectorAll('.nav-item');
	navLinks.forEach(link => {
		link.addEventListener('click', function () {
			navLinks.forEach(l => l.classList.remove('active'));
			this.classList.add('active');
		});
	});
</script>
</body>

</html>