document.addEventListener('DOMContentLoaded', () => {
	lucide.createIcons();

	const sidebar = document.querySelector('.sidebar');
	const sidebarToggle = document.getElementById('sidebarToggle');
	const sidebarClose = document.getElementById('sidebarClose');
	const mobileSidebarQuery = window.matchMedia(
		'(max-width: 768px), ((orientation: portrait) and (max-width: 1024px))'
	);

	function isMobileSidebar() {
		return mobileSidebarQuery.matches;
	}

	if (sidebarToggle && sidebar) {
		sidebarToggle.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopImmediatePropagation();
			sidebar.classList.toggle('toggled');
		});
	}

	if (sidebarClose && sidebar) {
		sidebarClose.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopImmediatePropagation();
			sidebar.classList.remove('toggled');
		});
	}

	document.addEventListener('click', (e) => {
		if (
			isMobileSidebar() &&
			sidebar &&
			sidebar.classList.contains('toggled') &&
			!sidebar.contains(e.target) &&
			!e.target.closest('#sidebarToggle')
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

	const notificationToggle = document.getElementById('notificationToggle');
	const notificationDropdown = document.getElementById('notificationDropdown');

	if (notificationToggle && notificationDropdown) {
		notificationToggle.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopImmediatePropagation();
			notificationDropdown.classList.toggle('show');

			if (notificationDropdown.classList.contains('show')) {
				const badge = notificationToggle.querySelector('.notification-badge');
				if (badge) {
					badge.remove();
				}

				const role = notificationToggle.getAttribute('data-role') || 'pelanggan';
				fetch(`${window.APP_BASEURL || ''}/${role}/marknotifread`, {
					method: 'POST'
				}).catch(err => console.error(err));
			}
		});

		notificationDropdown.addEventListener('click', (e) => {
			e.stopPropagation();
		});

		document.addEventListener('click', () => {
			notificationDropdown.classList.remove('show');
		});
	}
});