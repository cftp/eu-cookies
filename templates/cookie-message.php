<div id="cftpeuc">
	<h2><?php printf( __( 'Cookies on the %s website', 'cftpeuc' ), get_bloginfo( 'name' ) ); ?></h2>
	<p><?php printf( __( 'We use cookies to ensure that we give you the best experience on our website and to provide us with important information about visitors. By continuing to browse the site we&rsquo;ll assume that you are happy to receive all cookies set on the %s website.', 'cftpeuc' ), get_bloginfo( 'name' ) ); ?></p>
	<ul>
		<li><a id="cftpeuc-ok" href="#"><?php _e( 'Continue', 'cftpeuc' ); ?></a></li>
		<?php if ( isset( $this->settings['page'] ) and !empty( $this->settings['page'] ) ) { ?>
			<li><a href="<?php echo get_permalink( $this->settings['page'] ); ?>"><?php _e( 'More information', 'cftpeuc' ); ?></a></li>
		<?php } ?>
	</ul>
</div>
