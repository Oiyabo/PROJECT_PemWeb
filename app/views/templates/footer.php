</main>
</div>
</div>

<?php if (isset($user)): ?>
	<script>
		window.APP_SESSION = {
			timeout: <?= (int) SESSION_TIMEOUT ?>,
			warning: <?= (int) SESSION_WARNING ?>,
			serverNow: <?= time() ?>,
			expiresAt: <?= app_session_expires_at() ?>,
			extendUrl: <?= json_encode(BASEURL . '/auth/extendSession', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
			logoutUrl: <?= json_encode(BASEURL . '/auth/logout', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
		};
	</script>
	<script src="<?= BASEURL ?>/assets/js/session-timeout.js"></script>
<?php endif; ?>

<script src="<?= BASEURL ?>/assets/js/app.js"></script>
</body>

</html>