jQuery(document).ready(function($){	
	$('.wd-social-icons.bb-social_buttons_with_tag .wd-social-icon').each(function(index) {
		socialNetwork = $(this);
		socialNetworkTag = socialNetwork.attr('aria-label').split(' social link').join('');
		socialNetwork.append('<span class="wd-single_text">'+socialNetworkTag+'</span>');
	});
	$('html body.single-post .sidebar-container').removeClass('order-last')
	$('html body.single-post .related-posts-slider').addClass('order-last').insertAfter('html body.single-post .sidebar-container');
	$('html body .wp-block-latest-posts.wp-block-latest-posts__list li').each(function(index) {
		recentPost = $(this);
		recentPost.find('.wp-block-latest-posts__post-title, .wp-block-latest-posts__post-date').wrapAll('<div class="wp-block-latest-posts__info" />');
	});
	$('.wd-carousel-container').each(function(index) {
		relatedPosts = $(this);
		relatedPostsTitle = relatedPosts.find('.slider-title');
		relatedPostsTxt = relatedPosts.find('.slider-title span').text();
		relatedPostsTitle.replaceWith('<h2 class="title slider-title element-title">'+relatedPostsTxt+'</h2>');
	});
	$('.wd-sub-menu li>a:contains("Ver todos los productos")').addClass('bb-view_all_item');
	$('body.woocommerce.archive .wd-off-canvas-btn.wd-action-btn.wd-style-text>a').text('Filtros');
});//DOCUMENT








