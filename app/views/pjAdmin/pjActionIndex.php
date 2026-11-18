<?php
if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			pjUtil::printNotice(NULL, $status[2]);
			break;
	}
}else{
	?>
	<div class="dashboard_header">
		<div class="item">
			<div class="stat posts">
				<div class="info">
					<abbr><?php echo $tpl['cnt_posts'];?></abbr>
					<?php echo (int) $tpl['cnt_posts'] !== 1 ? strtolower(__('lblPosts', true)) : strtolower(__('lblPost', true)); ?>
				</div>
			</div>
		</div>
		<div class="item">
			<?php
			if($controller->isAdmin())
			{ 
				?>
				<div class="stat comments column3">
					<div class="info">
						<abbr><?php echo $tpl['cnt_waiting_comments'];?></abbr>
						<span><?php echo (int) $tpl['cnt_waiting_comments'] !== 1 ? __('dash_waiting_comments') : __('dash_waiting_comment'); ?></span>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="stat comments">
					<div class="info">
						<abbr><?php echo $tpl['cnt_comments'];?></abbr>
						<?php echo (int) $tpl['cnt_comments'] !== 1 ? strtolower(__('lblComments', true)) : strtolower(__('lblComment', true)); ?>
					</div>
				</div>
				<?php
			}
			?>
			
		</div>
		<div class="item">
			
			<?php
			if($controller->isAdmin())
			{ 
				?>
				<div class="stat authors">
					<div class="info">
						<abbr><?php echo $tpl['cnt_authors'];?></abbr>
						<?php echo (int) $tpl['cnt_authors'] !== 1 ? strtolower(__('lblAuthors', true)) : strtolower(__('lblAuthor', true)); ?>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="stat comments column3">
					<div class="info">
						<abbr><?php echo $tpl['cnt_waiting_comments'];?></abbr>
						<span><?php echo (int) $tpl['cnt_waiting_comments'] !== 1 ? __('dash_waiting_comments') : __('dash_waiting_comment'); ?></span>
					</div>
				</div>
				<?php
			}
			?>
			
		</div>
	</div>
	<div class="dashboard_box">
		<div class="dashboard_top">
			<div class="dashboard_column_top"><?php __('lblLatestPosts');?></div>
			<div class="dashboard_column_top"><?php __('lblLatestComments');?></div>
			<?php
			if($controller->isAdmin())
			{ 
				?>
				<div class="dashboard_column_top"><?php __('lblLastLoggedAuthors');?></div>
				<?php
			}else{
				?>
				<div class="dashboard_column_top"><?php __('dash_quick_links');?></div>
				<?php
			} 
			?>
		</div>
		<div class="dashboard_middle">
			<div class="dashboard_column">
				<?php
				$cnt = count($tpl['post_arr']);
				if ($cnt === 0)
				{
					?><p class="m10"><?php __('lblPostNotFound'); ?></p><?php
				}else{
					foreach ($tpl['post_arr'] as $k => $v)
					{
						?>
						<div class="dashboard_post_row dashboard_row<?php echo $k + 1 !== $cnt ? NULL : ' dashboard_row_last'; ?>">
							<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionUpdate&id=<?php echo $v['id']?>"><?php echo $v['title'];?></a></label>
							<label><?php echo $v['name'];?></label>
							<label><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($v['created'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($v['created'])), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></label>
						</div>
						<?php
					}
				} 
				?>
			</div>
			<div class="dashboard_column">
				<?php
				$cnt = count($tpl['comment_arr']);
				if ($cnt === 0)
				{
					?><p class="m10"><?php __('lblCommentNotFound'); ?></p><?php
				}else{
					foreach ($tpl['comment_arr'] as $k => $v)
					{
						?>
						<div class="dashboard_comment_row dashboard_row<?php echo $k + 1 !== $cnt ? NULL : ' dashboard_row_last'; ?>">
							<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminComments&amp;action=pjActionUpdate&id=<?php echo $v['id']?>"><?php echo $v['name'];?></a></label>
							<label><?php echo $v['email'];?></label>
							<label><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($v['created'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($v['created'])), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></label>
						</div>
						<?php
					}
				} 
				?>
			</div>
			<div class="dashboard_column">
				<?php
				if($controller->isAdmin())
				{
					$cnt = count($tpl['author_arr']);
					if ($cnt === 0)
					{
						?><p class="m10"><?php __('lblAuthorNotFound'); ?></p><?php
					}else{
						foreach ($tpl['author_arr'] as $k => $v)
						{
							?>
							<div class="dashboard_user_row dashboard_row<?php echo $k + 1 !== $cnt ? NULL : ' dashboard_row_last'; ?>">
								<label><?php echo $v['name'];?></label>
								<label><?php echo $v['email'];?></label>
								<label><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($v['last_login'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($v['last_login'])), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></label>
							</div>
							<?php
						}
					}
				}else{
					?>
					<div class="dashboard_user_row dashboard_row">
						<label class="fs15"><a class="block b20" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminPosts&amp;action=pjActionCreate"><?php __('dash_add_post');?></a></label>
						<label class="fs15"><a class="block b20" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminComments&amp;action=pjActionIndex&amp;status=W"><?php __('dash_link_waiting_comments');?></a></label>
						<label class="fs15"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionProfile"><?php __('dash_change_profile');?></a></label>
					</div>
					<?php
				} 
				?>
			</div>
		</div>
		<div class="dashboard_bottom"></div>
	</div>
	<div class="clear_left t20 overflow">
		<div class="float_left black t30 t20"><span class="gray"><?php echo ucfirst(__('lblDashLastLogin', true)); ?>:</span> <?php echo pjUtil::formatDate(date('Y-m-d', strtotime($_SESSION[$controller->defaultUser]['last_login'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ', ' . pjUtil::formatTime(date('H:i:s', strtotime($_SESSION[$controller->defaultUser]['last_login'])), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></div>
		<div class="float_right overflow">
		<?php
		list($hour, $day, $other) = explode("_", date("H:i_l_F d, Y"));
		$days = __('days', true, false);
		?>
			<div class="dashboard_date">
				<abbr><?php echo $days[date('w')]; ?></abbr>
				<?php echo pjUtil::formatDate(date('Y-m-d'), 'Y-m-d', $tpl['option_arr']['o_date_format']); ?>
			</div>
			<div class="dashboard_hour"><?php echo date($tpl['option_arr']['o_time_format'], time()); ?></div>
		</div>
	</div>
	<?php
}
?>