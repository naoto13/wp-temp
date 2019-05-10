
<?php
/*
Template Name: Contact ~お問い合わせ~
*/
?>
<!-- ヘッター -->
<?php get_header(); ?>

        <!-- メニュー -->
		<?php get_template_part(' content ',' menu ');?>
		
		<div id="main">

			<!-- Contact -->
			<section id="contact" class="site-width">
			<h1 class="title"><?php echo get_the_title(); ?></h1>
				<?php if(have_posts()) : //WordPressループ
					while (have_posts()) : the_post();  //繰り返し処理開始 ?>
						<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<?php the_content(); ?>
						</div>
						<?php endwhile; //繰り返し処理終了
					else :	//ここから記事が見つからなかった場合の処理 ?>
						<div class="post">
							<h2>記事はありません</h2>
							<p>お探しの記事は見つかりませんでした</p>
						</div>
					<?php endif; //WPのループ終了?>

			</section>


		</div>
<!-- フッター -->
<?php get_footer(); ?>
