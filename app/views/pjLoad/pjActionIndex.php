<?php
mt_srand();
$index = mt_rand(1, 9999);

// Which listing layout to render: theme11 = Compact List (tall), theme12 =
// Card Grid, every other theme keeps the site's original row layout
// untouched. Mirrors the exact theme-detection logic in
// pjFront::afterFilter() (session override, falling back to the site's
// default theme option) so this always agrees with which themeN.css was
// actually loaded for the page.
$current_theme = isset($_SESSION[$controller->defaultTheme]) ? $_SESSION[$controller->defaultTheme] : $tpl['option_arr']['o_theme'];
?>
<div id="pjWrapper">
	<div id="pjSbContainer_<?php echo $index;?>" class="container-fluid pjBlContainer">
		<div class="panel panel-default pjBlMain">

			<?php
			include_once PJ_VIEWS_PATH . 'pjLoad/elements/header.php';

			if (!empty($tpl['page_heading']))
			{
				?>
				<h1 class="pjBlPageTitle"><?php echo pjSanitize::html(stripslashes($tpl['page_heading'])); ?></h1>
				<?php
			}

			if(!empty($tpl['arr']))
			{
				if ($current_theme == 'theme11')
				{
					?>
					<ul class="list-group pjBlBody pjBlBodyCompactTall">
						<?php
						foreach($tpl['arr'] as $v)
						{
							$detail_url = $controller->postDetailUrl($v, true);

							$image_url = PJ_INSTALL_URL . PJ_IMG_PATH . 'frontend/275x175.png';
							if(!empty($v['thumb_path'])){
								$image_url = PJ_INSTALL_URL . $v['thumb_path'];
							}
							$image_alt = pjSanitize::html(stripslashes($v['title']));
							?>
							<li class="list-group-item pjBlPost pjBlPostCompactTall">
								<div class="pjBlPostCompactTallRow">
									<a href="<?php echo $detail_url;?>" class="pjBlPostThumbLink">
										<span class="pjBlPostThumb">
											<img src="<?php echo $image_url;?>" alt="<?php echo $image_alt;?>" />
										</span>
									</a><!-- /.pjBlPostThumbLink -->

									<div class="pjBlPostMain">
										<?php
										if(!empty($v['category']))
										{
											$category_url = $controller->categoryUrl($v['category_id'], true);
											?>
											<div class="pjBlPostCategoryBadge">
												<a href="<?php echo $category_url;?>" class="pjSbCategoryItem" data-id="<?php echo $v['category_id'];?>"><?php echo pjSanitize::clean($v['category']); ?></a>
											</div><!-- /.pjBlPostCategoryBadge -->
											<?php
										}
										?>
										<h2 class="pjBlPostTitle">
											<a href="<?php echo $detail_url;?>"><?php echo pjSanitize::html($v['title']);?></a>
										</h2><!-- /.pjBlPostTitle -->

										<p class="pjBlPostParagraph"><?php echo pjUtil::html2txt($v['body']);?></p><!-- /.pjBlPostParagraph -->

										<ul class="list-inline pjBlPostMeta">
											<li class="pjBlPostAuthor">
												<?php __('label_by', false, true);?> <span><?php echo $v['name']; ?></span>
											</li><!-- /.pjBlPostAuthor -->

											<li class="pjBlPostDate"><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($v['published_date'])), 'Y-m-d', $tpl['option_arr']['o_date_format']); ?></li><!-- /.pjBlPostDate -->
										</ul><!-- /.list-inline pjBlPostMeta -->
									</div><!-- /.pjBlPostMain -->

									<div class="pjBlPostStats">
										<a href="<?php echo $detail_url;?>#pjBlArticleComments" class="pjBlCommentsCounter">
											<span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
											<span><?php echo $v['cnt_comments']; ?></span>
										</a>
										<?php
										if($tpl['option_arr']['o_show_rating'] == 'Yes')
										{
											$avg = floor((float)$v['avg_rate']);
											?>
											<div class="pjBlRate">
												<div class="pjBlStars">
													<input type="radio" name="pjBlStarsRating-<?php echo $v['id'];?>" class="pjSbStarRating pjSbStarRating-<?php echo $v['id'];?>" data-post_id="<?php echo $v['id'];?>" id="pjBlStarsRating-<?php echo $v['id'];?>-0" value="0"<?php echo $avg == 0 ? ' checked' : NULL;?>>

													<?php
													for ($j = 1; $j <= 5; $j++)
													{
														?>
														<label for="pjBlStarsRating-<?php echo $v['id'];?>-<?php echo $j;?>">
															<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
														</label>
														<input type="radio" name="pjBlStarsRating-<?php echo $v['id'];?>" class="pjSbStarRating pjSbStarRating-<?php echo $v['id'];?>" data-post_id="<?php echo $v['id'];?>" id="pjBlStarsRating-<?php echo $v['id'];?>-<?php echo $j;?>" value="<?php echo $j;?>"<?php echo $avg == $j ? ' checked' : NULL;?>>
														<?php
													}
													?>

													<div id="pjSbRatingMessage_<?php echo $v['id'];?>" class="pjBlStarsResult"><?php echo number_format(round((float)$v['avg_rate'], 2), 2) ;?> <?php __('label_of', false, true);?> <?php echo intval($v['cnt']);?> <?php __('label_votes', false, true);?></div><!-- /.pjBlStarsResult -->
												</div><!-- /.pjBlStars -->
											</div><!-- /.pjBlRate -->
											<?php
										}
										?>
									</div><!-- /.pjBlPostStats -->
								</div><!-- /.pjBlPostCompactTallRow -->
							</li><!-- /.list-group-item pjBlPost pjBlPostCompactTall -->
							<?php
						}
						?>
					</ul><!-- /.list-group pjBlBody pjBlBodyCompactTall -->
					<?php
				}
				else if ($current_theme == 'theme12')
				{
					$post_count = count($tpl['arr']);
					?>
					<ul class="list-group pjBlBody pjBlBodyCardGrid<?php echo $post_count == 1 ? ' pjBlBodyCardGridSingle' : '';?>">
						<?php
						foreach($tpl['arr'] as $v)
						{
							$detail_url = $controller->postDetailUrl($v, true);

							$image_url = PJ_INSTALL_URL . PJ_IMG_PATH . 'frontend/275x175.png';
							if(!empty($v['thumb_path'])){
								$image_url = PJ_INSTALL_URL . $v['thumb_path'];
							}
							$image_alt = pjSanitize::html(stripslashes($v['title']));
							?>
							<li class="list-group-item pjBlPost pjBlPostCardGrid">
								<a href="<?php echo $detail_url;?>" class="pjBlPostThumbLink">
									<span class="pjBlPostThumb">
										<img src="<?php echo $image_url;?>" alt="<?php echo $image_alt;?>" />
									</span>
								</a><!-- /.pjBlPostThumbLink -->

								<div class="pjBlPostBody">
									<?php
									if(!empty($v['category']))
									{
										$category_url = $controller->categoryUrl($v['category_id'], true);
										?>
										<div class="pjBlPostCategoryBadge">
											<a href="<?php echo $category_url;?>" class="pjSbCategoryItem" data-id="<?php echo $v['category_id'];?>"><?php echo pjSanitize::clean($v['category']); ?></a>
										</div><!-- /.pjBlPostCategoryBadge -->
										<?php
									}
									?>
									<h2 class="pjBlPostTitle">
										<a href="<?php echo $detail_url;?>"><?php echo pjSanitize::html($v['title']);?></a>
									</h2><!-- /.pjBlPostTitle -->

									<ul class="list-inline pjBlPostMeta">
										<li class="pjBlPostAuthor">
											<?php __('label_by', false, true);?> <span><?php echo $v['name']; ?></span>
										</li><!-- /.pjBlPostAuthor -->

										<li class="pjBlPostDate"><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($v['published_date'])), 'Y-m-d', $tpl['option_arr']['o_date_format']); ?></li><!-- /.pjBlPostDate -->
									</ul><!-- /.list-inline pjBlPostMeta -->

									<p class="pjBlPostParagraph"><?php echo pjUtil::html2txt($v['body']);?></p><!-- /.pjBlPostParagraph -->

									<div class="pjBlPostStatsRow">
										<a href="<?php echo $detail_url;?>#pjBlArticleComments" class="pjBlCommentsCounter">
											<span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
											<span><?php echo $v['cnt_comments']; ?></span>
										</a>
										<?php
										if($tpl['option_arr']['o_show_rating'] == 'Yes')
										{
											$avg = floor((float)$v['avg_rate']);
											?>
											<div class="pjBlRate">
												<div class="pjBlStars">
													<input type="radio" name="pjBlStarsRating-<?php echo $v['id'];?>" class="pjSbStarRating pjSbStarRating-<?php echo $v['id'];?>" data-post_id="<?php echo $v['id'];?>" id="pjBlStarsRating-<?php echo $v['id'];?>-0" value="0"<?php echo $avg == 0 ? ' checked' : NULL;?>>

													<?php
													for ($j = 1; $j <= 5; $j++)
													{
														?>
														<label for="pjBlStarsRating-<?php echo $v['id'];?>-<?php echo $j;?>">
															<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
														</label>
														<input type="radio" name="pjBlStarsRating-<?php echo $v['id'];?>" class="pjSbStarRating pjSbStarRating-<?php echo $v['id'];?>" data-post_id="<?php echo $v['id'];?>" id="pjBlStarsRating-<?php echo $v['id'];?>-<?php echo $j;?>" value="<?php echo $j;?>"<?php echo $avg == $j ? ' checked' : NULL;?>>
														<?php
													}
													?>

													<div id="pjSbRatingMessage_<?php echo $v['id'];?>" class="pjBlStarsResult"><?php echo number_format(round((float)$v['avg_rate'], 2), 2) ;?> <?php __('label_of', false, true);?> <?php echo intval($v['cnt']);?> <?php __('label_votes', false, true);?></div><!-- /.pjBlStarsResult -->
												</div><!-- /.pjBlStars -->
											</div><!-- /.pjBlRate -->
											<?php
										}
										?>
									</div><!-- /.pjBlPostStatsRow -->
								</div><!-- /.pjBlPostBody -->
							</li><!-- /.list-group-item pjBlPost pjBlPostCardGrid -->
							<?php
						}
						?>
					</ul><!-- /.list-group pjBlBody pjBlBodyCardGrid -->
					<?php
				}
				else
				{
					?>
					<ul class="list-group pjBlBody">
						<?php
						foreach($tpl['arr'] as $v)
						{
							$detail_url = $controller->postDetailUrl($v, true);

							$image_url = PJ_INSTALL_URL . PJ_IMG_PATH . 'frontend/275x175.png';
							if(!empty($v['thumb_path'])){
								$image_url = PJ_INSTALL_URL . $v['thumb_path'];
							}
							$image_alt = pjSanitize::html(stripslashes($v['title']));
							?>
							<li class="list-group-item pjBlPost">
								<div class="row">
									<div class="col-lg-3 col-md-3 col-sm-4 col-xs-5 pjBlPostImage">
										<a href="<?php echo $detail_url;?>">
											<span class="img-holder">
												<img src="<?php echo $image_url;?>" alt="<?php echo $image_alt;?>" />

												<div class="pjBlPostImageOverlay">
													<button type="button" class="btn btn-primary"><?php __('front_view_more');?></button>
												</div><!-- /.pjBlPostImageOverlay -->
											</span>
										</a>
									</div><!-- /.col-lg-3 col-md-3 col-sm-4 col-xs-5 pjBlPostImage -->

									<div class="col-lg-9 col-md-9 col-sm-8 col-xs-7 pjBlPostContent">
										<h2 class="pjBlPostTitle">
											<a href="<?php echo $detail_url;?>#pjBlArticleComments" class="pull-right pjBlCommentsCounter">
												<span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
												<span><?php echo $v['cnt_comments']; ?></span>
											</a>
											<a href="<?php echo $detail_url;?>"><?php echo pjSanitize::html($v['title']);?></a>
										</h2><!-- /.pjBlPostTitle -->

										<ul class="list-inline pjBlPostMeta">
											<?php
											if(!empty($v['category']))
											{
												$category_url = $controller->categoryUrl($v['category_id'], true);
												?>
												<li class="pjBlPostCategory">
													<a href="<?php echo $category_url;?>" class="pjSbCategoryItem" data-id="<?php echo $v['category_id'];?>"><?php echo pjSanitize::clean($v['category']); ?></a>
												</li><!-- /.pjBlPostCategory -->
												<?php
											}
											?>

											<li class="pjBlPostAuthor">
												<?php __('label_by', false, true);?> <span><?php echo $v['name']; ?></span>
											</li><!-- /.pjBlPostAuthor -->

											<li class="pjBlPostDate"><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($v['published_date'])), 'Y-m-d', $tpl['option_arr']['o_date_format']); ?></li><!-- /.pjBlPostDate -->
										</ul><!-- /.list-inline pjBlPostMeta -->
										<?php
										if($tpl['option_arr']['o_show_rating'] == 'Yes')
										{
											$avg = floor((float)$v['avg_rate']);
											?>
											<div class="pjBlRate">
												<div class="pjBlStars">
													<input type="radio" name="pjBlStarsRating-<?php echo $v['id'];?>" class="pjSbStarRating pjSbStarRating-<?php echo $v['id'];?>" data-post_id="<?php echo $v['id'];?>" id="pjBlStarsRating-<?php echo $v['id'];?>-0" value="0"<?php echo $avg == 0 ? ' checked' : NULL;?>>

													<?php
													for ($j = 1; $j <= 5; $j++)
													{
														?>
														<label for="pjBlStarsRating-<?php echo $v['id'];?>-<?php echo $j;?>">
															<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
														</label>
														<input type="radio" name="pjBlStarsRating-<?php echo $v['id'];?>" class="pjSbStarRating pjSbStarRating-<?php echo $v['id'];?>" data-post_id="<?php echo $v['id'];?>" id="pjBlStarsRating-<?php echo $v['id'];?>-<?php echo $j;?>" value="<?php echo $j;?>"<?php echo $avg == $j ? ' checked' : NULL;?>>
														<?php
													}
													?>

													<div id="pjSbRatingMessage_<?php echo $v['id'];?>" class="pjBlStarsResult"><?php echo number_format(round((float)$v['avg_rate'], 2), 2) ;?> <?php __('label_of', false, true);?> <?php echo intval($v['cnt']);?> <?php __('label_votes', false, true);?></div><!-- /.pjBlStarsResult -->
												</div><!-- /.pjBlStars -->
											</div><!-- /.pjBlRate -->
											<?php
										}
										?>
										<p class="pjBlPostParagraph"><?php echo pjUtil::html2txt($v['body']);?></p><!-- /.pjBlPostParagraph -->
									</div><!-- /.col-lg-9 col-md-9 col-sm-8 col-xs-7 pjBlPostContent -->
								</div><!-- /.row -->
							</li><!-- /.list-group-item pjBlPost -->
							<?php
						}
						?>
					</ul><!-- /.list-group pjBlBody -->
					<?php
				}
				include_once PJ_VIEWS_PATH . 'pjLoad/elements/paginator.php';
			} else {
				?>
				<ul class="list-group pjBlBody"><li class="list-group-item pjBlPost"><div class="row"><?php __('label_no_post');?></div></li></ul>
				<?php
			}
			?>
		</div><!-- /.panel panel-default pjBlMain -->
	</div><!-- /.container-fluid pjBlContainer -->
</div><!-- /#pjWrapper -->
<?php
include_once PJ_VIEWS_PATH . 'pjLoad/elements/loadjs.php';
?>
