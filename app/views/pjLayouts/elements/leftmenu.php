<?php
if (pjObject::getPlugin('pjOneAdmin') !== NULL && $controller->isAdmin())
{
	$controller->requestAction(array('controller' => 'pjOneAdmin', 'action' => 'pjActionMenu'));
}
?>

<div class="leftmenu-top"></div>
<div class="leftmenu-middle">
	<ul class="menu">
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdmin' && $_GET['action'] == 'pjActionIndex' ? 'menu-focus' : NULL; ?>"><span class="menu-dashboard">&nbsp;</span><?php __('menuDashboard'); ?></a></li>
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminPosts' && in_array($_GET['action'],array('pjActionIndex', 'pjActionExport') ) ? 'menu-focus' : NULL; ?>"><span class="menu-posts">&nbsp;</span><?php __('menuPosts'); ?></a></li>
		
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminCategories&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminCategories' ? 'menu-focus' : NULL; ?>"><span class="menu-categories">&nbsp;</span><?php __('menuCategories'); ?></a></li>
		
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminComments&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminComments' && in_array($_GET['action'],array('pjActionIndex')) ? 'menu-focus' : NULL; ?>"><span class="menu-comments">&nbsp;</span><?php __('menuComments'); ?></a></li>
		<?php
		if ($controller->isAdmin())
		{
			?>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminUsers&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminUsers' && in_array($_GET['action'],array('pjActionIndex')) ? 'menu-focus' : NULL; ?>"><span class="menu-users">&nbsp;</span><?php __('menuAuthors'); ?></a></li>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionIndex" class="<?php echo ($_GET['controller'] == 'pjAdminOptions' && in_array($_GET['action'], array('pjActionIndex', 'pjActionEmailSettings', 'pjActionAddOns', 'pjActionProtection', 'pjActionNotification'))) || in_array($_GET['controller'], array('pjAdminLocales', 'pjBackup', 'pjLocale')) ? 'menu-focus' : NULL; ?>"><span class="menu-options">&nbsp;</span><?php __('menuOptions'); ?></a></li>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionInstall" class="<?php echo $_GET['controller'] == 'pjAdminOptions' && $_GET['action'] == 'pjActionInstall' ? 'menu-focus' : NULL; ?>"><span class="menu-install">&nbsp;</span><?php __('menuInstall'); ?></a></li>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionPreview" class="<?php echo $_GET['controller'] == 'pjAdminOptions' && $_GET['action'] == 'pjActionPreview' ? 'menu-focus' : NULL; ?>"><span class="menu-preview">&nbsp;</span><?php __('menuPreview'); ?></a></li>
			<?php
		}
		if ($controller->isAuthor())
		{
			?><li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionProfile" class="<?php echo $_GET['controller'] == 'pjAdmin' && $_GET['action'] == 'pjActionProfile' ? 'menu-focus' : NULL; ?>"><span class="menu-users">&nbsp;</span><?php __('menuProfile'); ?></a></li><?php
		}
		?>
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionLogout"><span class="menu-logout">&nbsp;</span><?php __('menuLogout'); ?></a></li>
	</ul>
	<div class="menu-request-wrap">
		<a class="menu-request-btn" href="https://www.phpjabbers.com/contact.php" target="_blank" rel="noopener noreferrer">
			<span class="menu-request-icon">
				<svg width="28" height="28" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<circle cx="16" cy="16" r="11" fill="none" stroke="#db8146" stroke-width="2.5"/>
					<circle cx="16" cy="16" r="5.5" fill="none" stroke="#db8146" stroke-width="2.2"/>
					<line x1="23.5" y1="8.5" x2="19" y2="13" stroke="#db8146" stroke-width="2.2"/>
					<line x1="8.5" y1="8.5" x2="13" y2="13" stroke="#db8146" stroke-width="2.2"/>
					<line x1="8.5" y1="23.5" x2="13" y2="19" stroke="#db8146" stroke-width="2.2"/>
					<line x1="23.5" y1="23.5" x2="19" y2="19" stroke="#db8146" stroke-width="2.2"/>
				</svg>
			</span>
			<span class="menu-request-label"><?php __('menuRequestCustomization'); ?></span>
		</a>
	</div>
</div>
<div class="leftmenu-bottom"></div>