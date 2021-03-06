<?php

// カスタムヘッダー画像の設置
$custom_header_defaults = array(
	//key =>valueの形
	//get_bloginfoでhpまでのurlを取得し、続いて画像のパスまでを追加する
	'default-image' => get_bloginfo('template_url') . '/img/logo.png',
	'header-text' => false, //ヘッダー画像上にテキストをかぶせる
);
// カスタムヘッダー機能を有効にする（管理画面に「ヘッダー」が現れる）
// add_theme_supportはwp独自の関数 第一引数にはなんの機能か（ここではcustom-header）,
//    第2引数にはその設定を入れる（ここでは上で設定した内容）
add_theme_support('custom-header', $custom_header_defaults);

// カスタムメニューの設置
// ここのmainmenuはwp_nav_menu>'theme_location' =>'mainmenu',で指定したもの
register_nav_menu('mainmenu', 'メインメニュー');

// ページネーション引数には最大ページ数と一覧に何ページ表示するかその表示範囲（range）を渡している
function pagenation($pages = '', $range = 2)
{
	$showitems = ($range * 2) * 1; //表示するページ数（5ページを表示）

	global $paged; //現在のページ数（paged自体はwpで用意されていグローバル変数）
	if (empty($paged)) $paged = 1; //デフォルトのページ

	if ($pages == '') {
		global $wp_query;
		$pages = $wp_query->max_num_pages; //全ページ数を取得
		if (!$pages) { //全ページ数がからの場合は、１とする
			$pages = 1;
		}
	}
	//ページ数が１ではないとき（複数のページがある場合）ページネーションが走る
	if (1 != $pages) { //全ページ１でない場合はページネーションを表示する
		// 表示するページネーションのHTML。"はそのまま記述不可なので前に\をつけて表示させる
		echo "<div class=\"pagenation\">\n";
		echo "<ul>\n";

		//prev 現在のページ値が1より大きい場合は表示
		// get_pagenum_linkはwp関数。引数に現在のページ−１を入れて前ページへのリンクとしている
		if ($paged > 1) echo "<li class=\"prev\"><a href='" . get_pagenum_link($paged - 1) . "'>Prev</a></li>\n";
		for ($i = 1; $i <= $pages; $i++) {
			if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
				//三項演算子での条件分岐
				echo ($paged == $i) ? "<li class=\"active\">" . $i . "</li>\n" : "<li><a href='" . get_pagenum_link($i) . "'>" . $i . "</a></li>\n";
			}
		}
		//next 総ページ数より現在のページ数が小さい場合は表示
		if ($paged < $pages) echo "<li class=\"next\"><a href=\"" . get_pagenum_link($paged + 1) . "\">Next</a></li>\n";
		echo "</ul>\n";
		echo "</div>\n";
	}
}

// =====================
// カスタムフィールド
// =====================
// 新しくカスタムフィールドを作成する場合、add_action('admin_menu', '作成した関数名');
// 作成したカスタムフィールドは、add_action('save_post', '保存する役割の関数');

// 投稿ページへ表示するカスタムボックスを定義する
add_action('admin_menu', 'add_custom_inputbox');
// 追加した表示項目のデータ更新・保存のためのアクションフック
add_action('save_post', 'save_custom_postdata');

function add_custom_inputbox()
{
	// add_meta_boxはwp関数
	//第一引数：編集画面のhtmlに挿入されるid属性名
	//第二引数：管理画面に表示されるカスタムフィールド名
	//第三引数：メタボックスの中に出力される関数名（custom_areaは下で定義）
	//第四引数：管理画面に表示するカスタムフィールドの場所（postなら投稿、pageなら固定ページ）
	//  (aboutはhome画面（固定ページ）に表示させたいため、pageを指定)
	//第五引数：配置される順序（normalは順序指定なし）
	add_meta_box('about_id', 'ABOUT入力欄', 'custom_area', 'page', 'normal');
}

// 管理画面位表示される内容
function custom_area()
{
	// 必ずカスタムフィールド内ではglobal変数として$postを読み込むこと
	global $post;
	// $post->ID,'about'はaboutをkeyをしてDBへ保存している
	echo 'コメント：<textarea cols="50" rows="5" name="about_msg">' . get_post_meta($post->ID, 'about', true) . '</textarea><br>';
}

//投稿ボタンを押した際のデータの更新と保存
function save_custom_postdata($post_id)
{
	$about_msg = '';
	//カスタムフィールドに入力された情報を取り出す(上のcustom_area内でname=about_msgとしている。この情報を抜き出す)
	if (isset($_POST['about_msg'])) {
		$about_msg = $_POST['about_msg'];
	}
	//内容が変わっていた場合、保存していた情報を更新する
	if ($about_msg != get_post_meta($post_id, 'about', true)) {
		// DBへ保存する場合はupdate_post_meta（Value,'key',保存する対象）
		update_post_meta($post_id, 'about', $about_msg);
	} elseif ($about_msg == '') {
		// 入力された情報が空なら、DBの情報も空にする。
		// delete_post_meta（Value,'key',条件（））
		delete_post_meta($post_id, 'about', get_post_meta($post_id, 'about', true));
	}
}

// =====================
// カスタムヴィジェット
// =====================
// ヴィジェットエリアを作成する関数がどれなのかを登録する
// add_action('widgets_init',任意の関数名);
add_action('widgets_init', 'my_widgets_area');
//ヴィジェット自体の作成する関数がどれなのかを登録する
// add_action('widget_init',function（）{register_widget('クラス名');});
//php5.3以降、仕様変更
add_action('widgets_init', function () {
	register_widget('my_widgets_item1');
});
// ヴィジェット関数の設定
function my_widgets_area()
{
	register_sidebar(array(
		// 管理画面で表示したい名前、id属性名、なんのタグで囲むのか
		'name' =>  'メリットエリア',
		'id' => 'widget_merit',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
	));
	// sidebarのヴィジェットはWPにすでに作成されているので、areaを確保するだけで良い
	register_sidebar(array(
		// dynamic_sidebarに渡す引数はnameでもidでもどちらでも可能
		'name' => 'right sidebar',
		'id' => 'my_sidebar',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		//ヴィジェットのタイトルの前後につけるものを表示
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	));
}

class my_widgets_item1 extends WP_Widget
{
	//初期化（管理画面で表示するヴィジェットの名前を設定する）
	// 初期化用のメソッドは、クラス名と同じ方が良い(my_widgets_item1)
	function my_widgets_item1()
	{
		// 管理画面に表示される名前をメリットヴィジェットへ
		parent::WP_Widget(false, $name = 'メリットヴィジェット');
	}
	//ヴィジェットの入力項目を作成する処理
	function form($instance)
	{
		$title = esc_attr($instance['title']);//esc_attrはhtml,phpタグを無効化するサニタイズ
		$body = esc_attr($instance['body']);
		?>
	<p>
		<!-- ヴィジェットのtitleという入力項目のidを取得している -->
		<label for="<?php echo $this->get_field_id('title'); ?>">
			<?php echo 'タイトル:'; ?>
		</label>
		<!-- ここでlabelのfor属性名とinputのid属性名を同じにすると、上のラベル（タイトルという文字）をクリックした時に入力フォームのinputにフォーカスが当たるようになっている -->
		<!-- 最後にvalueに入れることでDBのデータを表示できるようにする -->
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('body'); ?>">
			<?php echo '内容：'; ?>
		</label>
		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('body'); ?>" name="<?php echo $this->get_field_name('body'); ?>"><?php echo $body; ?></textarea>
	</p>
<?php
}

//ヴィジェットに入力された情報を保存する処理
//wp側の関数。update(新しい,古い)
function update($new_instance, $old_instance)
{
	$instance = $old_instance; //一旦、前のデータを呼び出して
	//下2つはサニタイズ
	//strip_tagsはphp,htmlのタグを取り除く
	$instance['title'] = strip_tags($new_instance['title']);
	//前後の空白を取り除くだけ。なのでbodyはhtml記法のリンク（href）が使用可能
	$instance['body'] = trim($new_instance['body']);

	return $instance; //最後にreturnで返すと、$new_instanceとなる
}

//管理画面から入力されたヴィジェットを画面に表示する処理
// register_sidebarで決められたヴィジェットの中身が$argsのなかに連想配列として渡ってきている
// $argsにはregister_sidebar'で設定した「before_title, after_title, before_widget, after_widget」が入る
// $instance  Widgetの設定項目
// widget(渡すもの,渡す先)
function widget($args, $instance)
{
	//配列を変数に展開（配列のkey名をそのまま変数として使用可能に）
	extract($args);

	//ヴィジェットから入力された情報を取得
	// WPのフィルターフックという機能。apply_filtersは、widget_titleと名前のついた処理を行い、返す
	//DBで取得したものを返すだけではなく、何か処理を挟みたい場合に使用する
	//実際に今回はwidget_titleやwidget_bodyは用意してないので、ただ変数に格納しているだけである
	$title = apply_filters('widget_title', $instance['title']);
	$body = apply_filters('widget_body', $instance['body']);

	//ヴィジェットから入力された情報がある場合、htmlを表示する
	if ($title) {
		?>
		<!-- 今回実装したいのは、パネル状で、タイトルとbodyに別れたやり方 -->
		<section class="panel">
			<h2><?php echo $title; ?></h2>
			<p>
				<?php echo $body; ?>
			</p>
		</section>

	<?php
		}
	}
}
?>