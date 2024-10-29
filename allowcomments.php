<?php
/*
Plugin Name: AllowComments (Разрешатель)
Version: 1.6
Plugin URI: http://iskariot.ru/wordpress/remix/#allow
Description: Allow comments to chosen posts even if option "close comments to old posts" is on. Разрешает комментирование отдельных записей при включенной опции закрытии комментариев после определенного срока (ВП 2.7). Для этого надо включить чекбокс "Allow comments to this post".
Author: Сергей М.
Author URI: http://iskariot.ru/
*/

//добавляем функцию на проверки
add_filter('comments_open', 'addAllowCommentsPost', 99);
add_filter('ping_open', 'addAllowCommentsPost', 99);
function addAllowCommentsPost($post_id){
	global $post,$comment_post_ID;
	
	if(1==$post_id) return $post_id;
	
	//находим текущий ид поста
	if(empty($post_id)) $post_id=$post->ID;
		if(empty($post_id)) $post_id=$comment_post_ID;
	
	//выцепляем мету произвольного поля
	$is_allow_comment = get_post_meta($post_id, 'allow_comments', true);
		if(empty($is_allow_comment)) {$s=get_post_custom_values( 'allow_comments', $post_id );$is_allow_comment=$s[0];}
		
			
	//если это - наш поциент, то возвращаем, что открыто
	if(!empty($is_allow_comment))
		return 'open';
}

//добавляем функцию на вывод постов
add_filter('the_posts', 'addAllowCommentsPosts', 99);
function addAllowCommentsPosts($posts){
	//ничего не делаем, если не надо
	if ( empty($posts) || !is_single() || !get_option('close_comments_for_old_posts') ) return $posts;
	
	//выцепляем мету произвольного поля
	$post_id=$posts[0]->ID;

	$is_allow_comment = get_post_meta($posts[0]->ID, 'allow_comments', true);
		if(empty($is_allow_comment)) {$s=get_post_custom_values( 'allow_comments', $posts[0]->ID );$is_allow_comment=$s[0];}

	//открываем комментарии, если наш поциент
	if(!empty($is_allow_comment))
		$posts[0]->comment_status = 'open';
		$posts[0]->ping_status = 'open';
	
return $posts;
}

//Добавляем в форму отдельный блок с чекбоксом, чтобы каждый раз не писать произвольное поле %)
add_action('simple_edit_form', 'addAllowCommentsForm');
add_action('edit_form_advanced', 'addAllowCommentsForm');
function addAllowCommentsForm() {
	global $post;
	$allow_comments=get_post_meta($post->ID,'allow_comments',true);
	?>
	<div class="postbox" id="allowcommentsdiv">
	<h3><a class="togbox">+</a> Allow comments always!</h3>
	  <div class="inside">
	    <p><input type="checkbox" value="1" name="allow_comments" id="allow_comments" <?php if(!empty($allow_comments)) echo ' checked=""' ?>/> <label for="allow_comments"><strong>Allow comments to this post</strong>, even if option "close comments to old posts" is on!</label></p>
	  </div>
	</div>
	<?php 
}

//сохраняем произвольное поле при сохранении поста
add_action('wp_insert_post', 'addAllowCommentsSave');
function addAllowCommentsSave($pID) {
	$allow_comments=get_post_meta($pID,'allow_comments',true);
	
	//если кастомфилд непустой и не равен дефолтному значению, не трогаем
	//if(!empty($allow_comments)&&($allow_comments!=="true")) return;
	
	//делетим
	if(empty($_POST['allow_comments'])){
		delete_post_meta( $pID, $name);
		}
	//апдейтим мету по чекбоксу
	else {
		add_post_meta ($pID, 'allow_comments', 'true', true) 
			or update_post_meta( $pID, 'allow_comments', 'true' );
		}
	
	}
?>