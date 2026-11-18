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
} else {
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/optmenu.php';
	
	pjUtil::printNotice(__('infoProtectionTitle', true), __('infoProtectionBody', true));
	
	if (isset($tpl['arr']))
	{
		if (is_array($tpl['arr']))
		{
			$option_tips = __('option_tips', true, false);
			$count = count($tpl['arr']);
			if ($count > 0)
			{
				?>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionUpdate" method="post" class="form pj-form" id="frmOptions">
					<input type="hidden" name="options_update" value="1" />
					<input type="hidden" name="next_action" value="pjActionProtection" />
					<table class="pj-table" cellpadding="0" cellspacing="0" style="width: 100%">
						<thead>
							<tr>
								<th><?php __('lblOption'); ?></th>
								<th colspan="2"><?php __('lblValue'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							for ($i = 0; $i < $count; $i++)
							{
								if ($tpl['arr'][$i]['tab_id'] != 3 || (int) $tpl['arr'][$i]['is_visible'] === 0) continue;
								?>
								<tr class="pj-table-row-odd">
									<td>
										<?php
										if(isset($option_tips[$tpl['arr'][$i]['key']]) && !empty($option_tips[$tpl['arr'][$i]['key']])){
											?><span class="option-tooltip"><?php __('opt_' . $tpl['arr'][$i]['key']);?></span>&nbsp;<a href="#" class="pj-form-langbar-tip option-tip" title="<?php echo $option_tips[$tpl['arr'][$i]['key']]; ?>"></a><?php
										}else{ 
											__('opt_' . $tpl['arr'][$i]['key']);
										} 
										?>
									</td>
									<td class="tblError" colspan="2">
										<?php
										switch ($tpl['arr'][$i]['type'])
										{
											case 'string':
												switch ($tpl['arr'][$i]['key'])
												{
													case 'o_paypal_address':
														?>
														<span class="pj-form-field-custom pj-form-field-custom-before">
															<span class="pj-form-field-before"><abbr class="pj-form-field-icon-email"></abbr></span>
															<input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field w200 email" value="<?php echo pjSanitize::html(tpl['arr'][$i]['value']); ?>" />
														</span>
														<?php
														break;
													default:
													    ?><input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field w200" value="<?php echo pjSanitize::html(tpl['arr'][$i]['value']); ?>" /><?php
												}
												break;
											case 'text':
											    ?><textarea name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field" style="width: 400px; height: 140px;"><?php echo pjSanitize::html(tpl['arr'][$i]['value']); ?></textarea><?php
												break;
											case 'int':
											    ?><input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field field-int w60 digits" value="<?php echo pjSanitize::html(tpl['arr'][$i]['value']); ?>" /><?php
												break;
											case 'float':
											    ?><input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field field-float w60 number" value="<?php echo pjSanitize::html(tpl['arr'][$i]['value']); ?>" /><?php
												break;
											case 'enum':
											    ?><select name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field">
												<?php
												$default = explode("::", $tpl['arr'][$i]['value']);
												$enum = explode("|", $default[0]);
												
												$enumLabels = array();
												if (!empty($tpl['arr'][$i]['label']) && strpos($tpl['arr'][$i]['label'], "|") !== false)
												{
													$enumLabels = explode("|", $tpl['arr'][$i]['label']);
												}
												$enum_arr = __('enum_arr', true);
												
												foreach ($enum as $k => $el)
												{
													if ($default[1] == $el)
													{
													    ?><option value="<?php echo $default[0].'::'.$el; ?>" selected="selected"><?php echo isset($enum_arr[$el]) ? $enum_arr[$el] : (array_key_exists($k, $enumLabels) ? stripslashes($enumLabels[$k]) : stripslashes($el)); ?></option><?php
													} else {
													    ?><option value="<?php echo $default[0].'::'.$el; ?>"><?php echo ($enum_arr[$el]) ? $enum_arr[$el] : (array_key_exists($k, $enumLabels) ? stripslashes($enumLabels[$k]) : stripslashes($el)); ?></option><?php
													}
												}
												?>
												</select>
												<?php
												break;
										}
										?>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					<p>
						<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
					</p>
				</form>
				
				<?php
			}
		}
	}
	?>
	<script type="text/javascript">
	var myLabel = myLabel || {};
	</script>
	<?php
}
?>