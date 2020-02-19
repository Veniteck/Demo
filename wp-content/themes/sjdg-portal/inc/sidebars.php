<?php
/**
* Sidebars
*/

register_sidebar( [
	'id'            => 'sidebar-products',
	'name'          => 'Sidebar: Products',
	'before_title'  => '<h4 class="widget__title">',
	'after_title'   => '</h4>',
	'before_widget' => '<li class="widget %2$s">',
	'after_widget'  => '</li>'
] );
