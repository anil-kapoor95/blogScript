<?php
mt_srand();
$index = mt_rand(1, 9999);
$pjUseRecaptcha = isset($tpl['option_arr']['o_captcha_provider']) && $tpl['option_arr']['o_captcha_provider'] == 'recaptcha';
$pjRecaptchaSiteKey = isset($tpl['option_arr']['o_recaptcha_site_key']) ? $tpl['option_arr']['o_recaptcha_site_key'] : '';
?>
<div id="pjWrapper">
	<div id="pjSbContainer_<?php echo $index;?>" class="container-fluid pjBlContainer">
		<div class="panel panel-default pjBlMain">
		
			<?php include_once PJ_VIEWS_PATH . 'pjLoad/elements/header.php';?>

			<div class="panel-body pjBlBody">
				<article class="pjBlArticle">
					<?php
					if (@$_GET['controller'] == 'pjLoad' && @$_GET['action'] == 'pjActionView')
					{
						$back = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['PHP_SELF'] .'?controller=pjLoad&amp;action=pjActionIndex'. (isset($_GET['iframe']) ? '&amp;iframe' : NULL);
						if (strpos($back,'facebook') !== false) {
							$back = $_SERVER['SCRIPT_NAME'] . '?controller=pjLoad&amp;action=pjActionIndex';
						}
						?>
						<p><a href="<?php echo $back;?>" class="pjBlBackLink"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span><?php __('label_back');?></a></p>
						<?php
					}
					if(isset($tpl['arr']))
					{ 
					    $title = !empty($tpl['arr']['title']) ? pjSanitize::html(stripslashes($tpl['arr']['title'])) : '';
						$minus_name = $controller->friendlyURL($title, '-');
						$plus_name = str_replace('-', "+", $minus_name);
						$url = str_replace("&", "%26", pjUtil::getPageURL());
						?>
						<header class="pjBlArticleHead">
							<h1 class="pjBlArticleTitle">
								<a href="#pjBlArticleComments" class="pull-right pjBlCommentsCounter">
									<span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
									<span><?php echo $tpl['arr']['cnt_comments']; ?></span>
								</a>
								<?php echo pjSanitize::html($tpl['arr']['title']);?>
							</h1><!-- /.pjBlArticleTitle -->
	
							<ul class="list-inline pjBlArticleMeta">
								<?php
								if(!empty($tpl['arr']['category']))
								{ 
									$category_url = $controller->categoryUrl($tpl['arr']['category_id'], true);
									?>
									<li class="pjBlArticleCategory">
										<a href="<?php echo $category_url;?>"><?php echo pjSanitize::html($tpl['arr']['category']);?></a>
									</li><!-- /.pjBlArticleCategory -->
									<?php
								} 
								?>
	
								<li class="pjBlArticleAuthor">
									<?php __('label_by', false, true);?> <span><?php echo pjSanitize::html($tpl['arr']['name']);?></span>
								</li><!-- /.pjBlArticleAuthor -->
	
								<li class="pjBlArticleDate"><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($tpl['arr']['published_date'])), 'Y-m-d', $tpl['option_arr']['o_date_format']); ?></li><!-- /.pjBlArticleDate -->
							</ul><!-- /.list-inline pjBlArticleMeta -->
							<?php
							if($tpl['option_arr']['o_show_rating'] == 'Yes')
							{
								$avg = floor((float)$tpl['arr']['avg_rate']);
								?>
								<div class="pjBlRate">
									<div class="pjBlStars">
										<input type="radio" name="pjBlStarsRating-<?php echo $tpl['arr']['id'];?>" class="pjSbStarRating pjSbStarRating-<?php echo $tpl['arr']['id'];?>" data-post_id="<?php echo $tpl['arr']['id'];?>" id="pjBlStarsRating-<?php echo $tpl['arr']['id'];?>-0" value="0"<?php echo $avg == 0 ? ' checked' : NULL;?>>
			
										<?php
										for ($j = 1; $j <= 5; $j++) 
										{ 
											?>
											<label for="pjBlStarsRating-<?php echo $tpl['arr']['id'];?>-<?php echo $j;?>">
												<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
											</label>
											<input type="radio" name="pjBlStarsRating-<?php echo $tpl['arr']['id'];?>" class="pjSbStarRating pjSbStarRating-<?php echo $tpl['arr']['id'];?>" data-post_id="<?php echo $tpl['arr']['id'];?>" id="pjBlStarsRating-<?php echo $tpl['arr']['id'];?>-<?php echo $j;?>" value="<?php echo $j;?>"<?php echo $avg == $j ? ' checked' : NULL;?>>
											<?php
										} 
										?>
		
										<div class="pjBlStarsResultCurrent"><?php __('front_your_vote_is');?>: </div><!-- /.pjBlStarsResultCurrent -->
										<div id="pjSbRatingMessage_<?php echo $tpl['arr']['id'];?>" class="pjBlStarsResult"><?php echo number_format(round((float)$tpl['arr']['avg_rate'], 2), 2) ;?> <?php __('label_of', false, true);?> <?php echo intval($tpl['arr']['cnt']);?> <?php __('label_votes', false, true);?></div><!-- /.pjBlStarsResult -->
									</div><!-- /.pjBlStars -->
								</div><!-- /.pjBlRate -->
								<?php
							} 
							?>
						</header><!-- /.pjBlArticleHead -->
	
						<div class="pjBlArticleBody">
							<?php
							$pin_image = '';
							if(!empty($tpl['arr']['source_path']))
							{
								// Use the full, uncropped source upload here - the
								// "medium" rendition is center-cropped to a fixed
								// 480x420 box (for the list/grid cards, where every
								// thumbnail needs the same shape), which chops off
								// a large chunk of wide/landscape images on this
								// single-post page. The full article view should
								// show the complete picture, scaled to fit via CSS
								// (.pjBlArticleBody img already has max-width:100%).
								$image_url = PJ_INSTALL_URL . $tpl['arr']['source_path'];
								$pin_image = $image_url;
								?>

								<img src="<?php echo $image_url;?>" alt="<?php echo $title;?>" />
								<?php
							}
							if (!empty($tpl['arr']['body'])) { 
                                echo stripslashes($tpl['arr']['body']);
							}
							
							$short_description = pjUtil::tokenTruncate(pjUtil::html2txt($tpl['arr']['body']), 160);
							?>
	
						</div><!-- /.pjBlArticleBody -->

						<footer class="pjBlArticleFoot">
							<?php
							if($tpl['option_arr']['o_send_to_friend'] == 'Yes')
							{
								// Fully urlencoded page URL, image URL and title - every share
								// endpoint below gets consistent, safely-encoded values instead
								// of the old mix of raw/half-escaped strings (which broke on
								// titles containing "&", quotes, etc).
								$share_url = urlencode(!empty($tpl['page_url']) ? $tpl['page_url'] : pjUtil::getPageURL());
								$share_image = urlencode($pin_image);
								$share_title = urlencode(stripslashes($tpl['arr']['title']));
								?>
								<div class="pjBlSocials pjBlArticleSocials">
									<ul class="list-inline">

										<li>
											<a href="#" class="btn btn-primary" data-pj-toggle="modal" data-pj-target="#pjBlSendMessage">
												<?php __('label_send_to_friend', false, true);?>
												<span class="glyphicon glyphicon-user" aria-hidden="true"></span>
											</a>
										</li>

										<li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url;?>" class="pjBlLinkFacebook" target="_blank" rel="noopener">facebook</a></li>
										<li><a href="https://twitter.com/intent/tweet?text=<?php echo $share_title;?>&amp;url=<?php echo $share_url;?>" class="pjBlLinkTwitter" target="_blank" rel="noopener">twitter</a></li>
										<li><a href="https://pinterest.com/pin/create/button/?url=<?php echo $share_url; ?>&amp;media=<?php echo $share_image;?>&amp;description=<?php echo $share_title;?>" class="pjBlLinkPinterest" target="_blank" rel="noopener">Pinterest</a></li>
										<li><a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url;?>" class="pjBlLinkLinkedIn" target="_blank" rel="noopener">LinkedIn</a></li>
									</ul><!-- /.list-inline -->
								</div><!-- /.pjBlSocials pjBlArticleSocials -->
								<?php
							} 
							?>
	
	
							<div id="pjBlArticleComments" class="pjBlArticleComments">
								<p class="pjBlArticleCommentsTitle"><?php echo $tpl['arr']['cnt_comments']; ?>&nbsp;<?php echo $tpl['arr']['cnt_comments'] == 1 ? __('label_comment', true, false) : __('label_comments', true, false);?></p><!-- /.pjBlArticleCommentsTitle -->
								<?php
								if(!empty($tpl['comment_arr']))
								{ 
									?>
									<ul class="list-unstyled pjBlComments">
										<?php
										foreach($tpl['comment_arr'] as $v)
										{ 
											?>
											<li class="pjBlComment">
												<header class="pjBlCommentHead">
													<p class="pjBlCommentAuthor"><?php echo pjSanitize::html($v['name']);?></p><!-- /.pjBlCommentAuthor -->
													<p class="pjBlCommentMeta"><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($v['created'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($v['created'])), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></p><!-- /.pjBlCommentMeta -->
												</header><!-- /.pjBlCommentHead -->
			
												<div class="pjBlCommentBody">
													<?php echo !empty($v['comment']) ? nl2br(stripslashes($v['comment'])) : '';?>
												</div><!-- /.pjBlCommentBody -->
											</li><!-- /.pjBlComment -->
											<?php
										} 
										?>
									</ul><!-- /.list-unstyled pjBlComments -->
									<?php
								} else {
									?>
									<ul class="list-unstyled pjBlComments">
										<li class="pjBlComment">
											<?php __('label_comment_not_found', false, true);?>
										</li>
									</ul>
									<?php
								}
								
								if($tpl['option_arr']['o_allow_posting_comment'] == 'Yes')
								{
									$detail_url = $controller->postDetailUrl($tpl['arr'], true);
									?>
									<div class="pjBlForm pjBlCommentsForm">
										<form id="pjSbPostComment_<?php echo $index;?>" name="frmPostComment" action="<?php echo $detail_url; ?>" method="post" data-toggle="validator" role="form">
											<input type="hidden" name="post_comment" value="1" />
											<input type="hidden" name="post_id" value="<?php echo $tpl['arr']['id'];?>" />
											<input type="hidden" name="category_id" value="<?php echo $tpl['arr']['category_id'];?>" />
											<?php
											if(isset($_GET['err']))
											{
												$error_message = __('post_messages', true, false);
												if($_GET['err'] != '9999' && $_GET['err'] != '9998')
												{
													?><div class="form-group text-danger"><?php echo $error_message[$_GET['err']]; ?></div><?php
												}else{
													?><div class="form-group text-success"><?php echo $error_message[$_GET['err']]; ?></div><?php
												}
											} 
											?>
											<div class="form-group">
												<input name="name" class="form-control requierd" placeholder="<?php echo strtoupper(__('label_name', true, false));?>"/>
												
												<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
											</div><!-- /.form-group -->
		
											<div class="form-group">
												<input name="email" class="form-control required email" placeholder="<?php echo strtoupper(__('label_email', true, false));?>"/>
														
												<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
											</div><!-- /.form-group -->
		
											<div class="form-group">
												<?php $comment_placeholder = __('front_enter_comment', true, false);?>
												<textarea name="comment" id="" cols="30" rows="10" class="form-control required" placeholder="<?php echo !empty($comment_placeholder) ? strtoupper($comment_placeholder) : '';?>"></textarea>
														
												<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
											</div><!-- /.form-group -->
		
											<div class="form-group pjBlFormCaptcha">
												<?php if ($pjUseRecaptcha) { ?>
												<div>
													<div class="g-recaptcha" data-sitekey="<?php echo pjSanitize::html($pjRecaptchaSiteKey); ?>"></div>
												</div>
												<?php } else { ?>
												<div>
													<input name="captcha" class="form-control required" maxlength="6" placeholder="<?php echo strtoupper(__('front_enter_captcha', true, false));?>" autocomplete="off"/>
													<img src="<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjFront&amp;action=pjActionCaptcha&amp;rand=<?php echo rand(1, 999999); ?>" alt="Captcha" class="stb-block stb-float-left" />
												</div>
												<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
												<?php } ?>
											</div><!-- /.form-group pjBlFormCaptcha -->
		
											<div class="form-group pjBlFormActions">
												<button type="submit" class="btn btn-primary"><?php __('button_post_comment', false, true);?></button>
											</div><!-- /.form-group pjBlFormActions -->
										</form>
									</div><!-- /.pjBlForm pjBlCommentsForm -->
									<?php
								} 
								?>
							</div><!-- /#pjBlArticleComments.pjBlArticleComments -->
						</footer><!-- /.pjBlArticleFoot -->
						<?php
					} else {
						// $tpl['arr'] is never set when the post doesn't exist, is
						// deactivated, or its category is deactivated (see pjActionView()
						// in pjLoad.controller.php) - previously there was no else branch
						// here at all, so the page rendered with just the "Back" link and
						// an empty body instead of any explanation.
						?>
						<div class="pjBlPostUnavailable">
							<p><?php __('front_post_not_found'); ?></p>
						</div><!-- /.pjBlPostUnavailable -->
						<?php
					}
					?>
				</article><!-- /.pjBlArticle -->
			</div><!-- /.panel-body pjBlBody -->
		</div><!-- /.panel panel-default pjBlMain -->
	</div><!-- /.container-fluid pjBlContainer -->

	<?php
	if(isset($tpl['arr']) && $tpl['option_arr']['o_send_to_friend'] == 'Yes')
	{ 
		?>
		<div id="pjBlSendMessage" class="modal fade pjBlModal" tabindex="-1" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content pjBlForm">
					<form id="pjSbSendToFriend_<?php echo $index;?>" action="#" method="post" data-toggle="validator" role="form">
						<header class="modal-header">
							<button type="button" class="close" data-pj-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						
							<h3 class="modal-title"><?php __('label_send_to_friend', false, true);?></h3><!-- /.modal-title -->
						</header><!-- /.modal-header -->
						
						<div class="modal-body">
							
							<div class="form-group">
								
								<input type="email" name="send_to" class="form-control" placeholder="<?php __('label_email');?>"/>
	
								<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
							</div><!-- /.form-group -->
	
							<div class="form-group">
								<input name="subject" class="form-control" placeholder="<?php echo __('label_subject', true, false);?>"/>
								
								<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
							</div><!-- /.form-group -->
	
							<div class="form-group">
								
								<textarea name="message" cols="30" rows="10" class="form-control" placeholder="<?php __('front_enter_message');?>"><?php echo !empty($tpl['page_url']) ? pjSanitize::html($tpl['page_url']) : pjUtil::getPageURL(); ?></textarea>
	
								<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
							</div><!-- /.form-group -->
	
							<div class="form-group pjBlFormCaptcha">
								<?php if ($pjUseRecaptcha) { ?>
								<div>
									<div class="g-recaptcha" data-sitekey="<?php echo pjSanitize::html($pjRecaptchaSiteKey); ?>"></div>
								</div>
								<?php } else { ?>
								<div>
									<input name="captcha" class="form-control required" maxlength="6" placeholder="<?php __('front_enter_captcha');?>"/>
									<img src="<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjFront&amp;action=pjActionCaptchaSend&amp;rand=<?php echo rand(1, 999999); ?>" alt="Captcha" class="stb-block stb-float-left" />
								</div>
								<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
								<?php } ?>
							</div><!-- /.form-group pjBlFormCaptcha -->
							
							<div style="display: none;"><label id="pjSbSendMessage_<?php echo $index;?>"></label></div>
						</div><!-- /.modal-body -->
						
						<footer class="modal-footer">
							<button type="submit" class="btn btn-primary"><?php __('button_send', false, true);?></button>
						</footer><!-- /.modal-footer -->
					</form>
				</div><!-- /.modal-content pjBlForm -->
			</div><!-- /.modal-dialog -->
		</div><!-- /#pjBlSendMessage.modal fade pjBlModal -->
		<?php
	} 
	?>
</div><!-- /#pjWrapper -->
<?php
if ($pjUseRecaptcha)
{
	?><script src="https://www.google.com/recaptcha/api.js" async defer></script><?php
}
include_once PJ_VIEWS_PATH . 'pjLoad/elements/loadjs.php';
?>