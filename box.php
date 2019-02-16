<?php

/*
Name: Related Post Box
Author: Nadiar AS -- pabelog.com
Description: Adds Related Post with Img to Thesis.
Version: 2.2.5
Class: related_post_box
 */


class related_post_box extends thesis_box
{

	protected function translate()
	{
		$this->title = __('Related Posts Box', 'thesis');
	}

	protected function construct()
	{
		global $thesis;
		if (!$this->_display()) return;
		add_theme_support('post-thumbnails');
		if (empty($thesis->_wp_featured_image_rss)) {
			add_filter('the_content', array($this, 'add_image_to_feed'));
			$thesis->_wp_featured_image_rss = true;
		}
	}

	protected function options()
	{
		global $thesis, $_wp_additional_image_sizes;
		$options = array(
			'full' => __('Full size (default)', 'thesis'),
			'thumbnail' => __('Thumbnail', 'thesis'),
			'medium' => __('Medium', 'thesis'),
			'large' => __('Large', 'thesis')
		);
		if (!empty($_wp_additional_image_sizes))
			foreach ($_wp_additional_image_sizes as $size => $data)
			$options[$size] = $size;
		return array(
			'activate' => array(
				'type' => 'checkbox',
				'label' => __('Display Related Post with Image', 'thesis'),
				'options' => array(
					'img1checktext' => __('Click here to activate Related Post with Image', 'thesis'),
				),
				'default' => array(
					'img1checktext' => false,
					'html' => ''
				)
			),
			'relatedtype' => array(
				'type' => 'radio',
				'label' => __('Show by', 'thesis'),
				'tooltip' => sprintf(__('Choose by what related post will be displayed. More info at <a href="http://www.wpbeginner.com/wp-themes/how-to-add-related-posts-with-a-thumbnail-without-using-plugins/" target="_blank">WPBegginer</a>(default by category)', 'thesis')),
				'options' => array(
					'category' => __('By Category', 'thesis'),
					'tag' => __('By Tag', 'thesis')
				),
				'default' => array(
					'category' => true,
					'html' => ''
				)
			),
			'title' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Title', 'thesis'),
				'tooltip' => sprintf(__('Enter the title of Related Posts you would like to show', 'thesis')),
				'default' => ''
			),
			'container_class' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Title', 'thesis'),
				'tooltip' => sprintf(__('Container class', 'thesis')),
				'default' => 'related_posts_containers'
			),
			'ul_class' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Title', 'thesis'),
				'tooltip' => sprintf(__('ul class', 'thesis')),
				'default' => 'related_posts_list'
			),
			'li_class' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Title', 'thesis'),
				'tooltip' => sprintf(__('li class', 'thesis')),
				'default' => 'related_post_item'
			),
			'number' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => __('Max Number of Related Post', 'thesis'),
				'tooltip' => sprintf(__('Enter the Number of Related Post you want to display(leave blank for 3 posts)', 'thesis')),
				'default' => '4'
			),
			'characters' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => __('Maximum of Characters for the Title', 'thesis'),
				'tooltip' => sprintf(__('Enter Maximum of Characters for the Title(leave blank for 64 character)', 'thesis')),
				'default' => '64'
			),
			'size' => array(
				'type' => 'select',
				'label' => __('Featured Image Size', 'thesis'),
				'tooltip' => sprintf(__('Choose the size of the Feature Image for this location. The list includes <a href="%s">WordPress standard image sizes</a> and any other registered image sizes.', 'thesis'), admin_url('options-media.php')),
				'options' => $options,
				'default' => 'full'
			),
			'link' => array(
				'type' => 'checkbox',
				'options' => array(
					'link' => __('Link image to post', 'thesis')
				),
				'default' => array(
					'link' => true
				)
			),
			'ads' => array(
				'type' => 'textarea',
				'label' => __('Your ads code', 'thesis'),
				'tooltip' => sprintf(__('The best match is 300x600 ads code', 'thesis')),
				'code' => true,
				'default' => ''
			)
		);
	}

	public function html()
	{
		global $thesis, $post;
		// get options
		$options = $thesis->api->get_options($this->_options(), $this->options);
		
		// activate
		if ($options['activate']) {
			/*
			 *	CORE CODE
			 */
			$orig_post = $post;
			global $post;
			$title = !empty($this->options['title']) ? $this->options['title'] : '';
			$number = !empty($this->options['number']) ? $this->options['number'] : '4';
			$size = !empty($this->options['size']) ? $this->options['size'] : 'thumbnail';
			$ads = !empty($this->options['ads']) ? $this->options['ads'] : '';
			$title_chars = !empty($this->options['characters']) ? $this->options['characters'] : '64';
			$dom_container_class = !empty($this->options['container_class']) ? $this->options['container_class'] : 'related_posts_containers';
			$dom_ul_class = !empty($this->options['li_class']) ? $this->options['li_class'] : 'related_posts_list';
			$dom_li_class = !empty($this->options['ul_class']) ? $this->options['ul_class'] : 'related_post_item';

			if ($options['relatedtype'] == 'tag') {
				$tags = wp_get_post_tags($post->ID);
				if ($tags) {
					$tag_ids = array();

					foreach ($tags as $individual_tag) $tag_ids[] = $individual_tag->term_id;
					$args = array(
						'tag__in' => $tag_ids,
						'post__not_in' => array($post->ID),
						'posts_per_page' => $number, // Number of related posts that will be shown.
						'caller_get_posts' => 1
					);
				}
			} else {
				$categories = get_the_category($post->ID);
				if ($categories) {
					$category_ids = array();

					foreach ($categories as $individual_category) $category_ids[] = $individual_category->term_id;

					$args = array(
						'category__in' => $category_ids,
						'post__not_in' => array($post->ID),
						'posts_per_page' => $number, // Number of related posts that will be shown.
						'caller_get_posts' => 1
					);
				}
			} // end if

			$my_query = new wp_query($args);

			if ($my_query->have_posts()) { ?>

				<div id="relatedposts">
          <h3 class="related_post_label"><?php echo $title; ?></h3>
					<div style="width: 100%">
						<!-- First Column -->
						<div class="adpad">
							<?php echo $ads; ?>			
						</div>
						<!-- Second Column Will be related post-->
						<div class="<?php echo $dom_container_class; ?>">
						<!-- Related Post Begin -->
			        <ul class="<?php echo $dom_ul_class; ?>">
							  <?php	while ($my_query->have_posts()) {
									$my_query->the_post(); ?>
									<!-- related post <li> -->
									<li class="<?php echo $dom_li_class; ?>">
										<div class="relatedthumb">
										  <?php
											if (!isset($this->options['link'])) { ?>
											<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php echo the_title(); ?>"><?php the_post_thumbnail($options['size']); ?></a>
										  <?php

										} else {
											the_post_thumbnail($options['size']);
										}
										?>
										</div>
										<div class="relatedcontent">
											<p class="caption aligncenter italic"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php if (strlen($post->post_title) > $title_chars) {
																																																																																																																																																													echo substr(the_title($before = '', $after = '', false), 0, $title_chars) . '...';
																																																																																																																																																												} else {
																																																																																																																																																													the_title();
																																																																																																																																																												} ?></a></a></p>
										</div>
									</li>
								<?php

						} // end while ?> 
							</ul>
						<!-- Related Post End -->
						</div>
					</div>
				</div>
				<?php

		} // end if query
	} // end if activate
	$post = $orig_post;
	wp_reset_query();
		/*
	 *	CORE CODE END
	 */
} // end public funtion
}
