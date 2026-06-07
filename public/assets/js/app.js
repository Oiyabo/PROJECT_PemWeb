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
			e.stopPropagation();
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
			isMobileSidebar() &&
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
});
