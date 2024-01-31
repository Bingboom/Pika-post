<?php
if (!defined('ABSPATH')) exit;
add_action('admin_menu', 'Fwm_Player_menu_page');
register_activation_hook(__FILE__, 'Fwm_Player_install');
register_deactivation_hook(__FILE__, 'Fwm_Player_uninstall');
function Fwm_Player_install() {
  add_option('Fwm_player_st');
  add_option('Fwm_player_css');
  add_option('Fwm_player_qqid');
  add_option('Fwm_player_name');
  add_option('Fwm_player_tips');
  add_option('Fwm_player_auto');
  add_option('Fwm_player_geci');
  add_option('Fwm_player_move');
  add_option('Fwm_player_ajax');
  add_option('Fwm_player_suiji');
  add_option('Fwm_player_style');
  add_option('Fwm_player_title');
  add_option('Fwm_player_random');
  add_option('Fwm_player_jquery');
  add_option('Fwm_player_string');
  add_option('Fwm_player_cookie');
  add_option('Fwm_player_content');
  add_option('Fwm_player_searchform');
  add_option('Fwm_player_style_custom');
  add_option('Fwm_player_jquery_custom');
}
function Fwm_Player_uninstall() {
  deltee_option('Fwm_player_st');
  deltee_option('Fwm_player_css');
  deltee_option('Fwm_player_qqid');
  deltee_option('Fwm_player_name');
  deltee_option('Fwm_player_tips');
  deltee_option('Fwm_player_auto');
  deltee_option('Fwm_player_geci');
  deltee_option('Fwm_player_move');
  deltee_option('Fwm_player_ajax');
  deltee_option('Fwm_player_suiji');
  deltee_option('Fwm_player_random');
  deltee_option('Fwm_player_style');
  deltee_option('Fwm_player_title');
  deltee_option('Fwm_player_string');
  deltee_option('Fwm_player_jquery');
  deltee_option('Fwm_player_cookie');
  deltee_option('Fwm_player_content');
  deltee_option('Fwm_player_searchform');
  deltee_option('Fwm_player_style_custom');
  deltee_option('Fwm_player_jquery_custom');
}
function Fwm_Player_menu_page() {add_menu_page(__('Music Player', FwmTD), __('Music Player', FwmTD), 'administrator', 'Fwm_Player_options', 'Fwm_Player_options', Fwm_Player_URL.'/inc/icon.gif', 99);}
//设置页面
function Fwm_Player_options() { ?>
  <link rel = 'stylesheet' href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' type = 'text/css' />
  <link rel = 'stylesheet' href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/solid.min.css' type = 'text/css' />
  <div class = "meta-box-sortabless">
  <h2><a href = "http://eric.cn.com/?p=1187" target = "_blank"><?php _e('Floating Window Music-Player', FwmTD); echo '</a> v' . Fwm_Player_VER;?></h2>
  <?php current_user_can('manage_options') && update_Fwm_Player_options();?>
  <form method = "post">
  <!--音乐网站代码设置-->
    <div class = "postbox">
      <h3 class = "hndle"><?php _e('Music site code settings', FwmTD)?></h3>
      <div class = "inside">
        <table><tbody>
		<tr><td><img src = "//s1.music.126.net/music.ico" width = "18"><input type = "text" name = "Fwm_player_cookie" value = "<?php echo get_option('Fwm_player_cookie', '90028341_login=1255528626018461671490028341;Path=/;Domain=www.eric.cn.com;expires=End Of Session');?>" class = "regular-text"/></td><td><a title = "<?php _e('Netease cloud music Cookie, detailed settings see the player home page', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></td></tr>
        <tr><td><textarea rows = "4" cols = "53" name = "Fwm_player_qqid" id = "Fwm_player_qqid"><?php echo get_option('Fwm_player_qqid', '逆袭之星途璀璨 电视剧原声大碟第三章*004SnUPP4AaJAu*1|张靓颖*100*2|听这些歌曲串烧如临演唱现场一样过瘾*2066592292*3') ?></textarea></td><td><a title = "<?php _e('Detailed settings see below, fill in NO to close', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></td></tr>
		<tr><td><input type = "text" id = "mtool" class = "regular-text" placeholder = "<?php _e('Enter full URL of music (Only Album / Song list)', FwmTD)?>">
        <button onclick = "xiami()" type = "button"  class = "button-primary"><?php _e('Get code and categorie', FwmTD)?></button></td></tr>
		<tr><td><input placeholder = "<?php _e('Name', FwmTD)?>" type="text" name="text1" id="text1" />
		<input placeholder = "<?php _e('Code', FwmTD)?>" type="text" name="text2" id="text2" />
		<select name="text3" id="text3">
			<option value="0"><?php _e('User ID', FwmTD)?></option>
			<option value="1"><?php _e('Album', FwmTD)?></option>
			<option value="2"><?php _e('Artist', FwmTD)?></option>
			<option value="3"><?php _e('Song list', FwmTD)?></option>
		</select>
		<select name="text4" id="text4">
			<option value="netease"><?php _e('Netease Music', FwmTD)?></option>
			<option value="tencent"><?php _e('QQ Music', FwmTD)?></option>
			<option value="baidu"><?php _e('Baidu Music', FwmTD)?></option>
			<option value="kugou"><?php _e('Kugou Music', FwmTD)?></option>
			<option value="kuwo"><?php _e('Kuwo Music', FwmTD)?></option>
			<option value="xiami"><?php _e('Xiami Music', FwmTD)?></option>
		</select>
		<button onclick = "sdata()" type = "button"  class = "button-primary"><?php _e('Add message', FwmTD)?></button></td></tr>
        </tbody></table>
        <?php _e('Completion Instructions', FwmTD)?>：<br>
        <?php _e('Support Netease, QQ, Baidu, Kugou, Kuwo and Xiami music playback.', FwmTD)?><br>
        <b><?php _e('Netease / QQ / Xiami / Baidu / Kugou / Kuwo music code settings: (Advise to use the above tools to get code and categorie, with or without "|" character as ending)', FwmTD)?></b><br>
        <?php _e('Each message format: <b>Name * Code * Categorie*Source</b>, multiple messages separat with "|", not contain "|" or "*" in Name.', FwmTD)?><br>
		<?php _e('Source as follows: Netease music => netease; QQ music => tencent; Baidu music => baidu; Kugou music => kugou; Kuwo music => kuwo; Xiami music => xiami', FwmTD)?>：<br>
        <?php _e("The artist's code is the number of songs.", FwmTD)?><br>
        <?php _e('Categories as follows: User ID（Only support Netease）=> 0; Album => 1; Artist => 2; Song list => 3', FwmTD)?><br>
        <?php _e('Examples', FwmTD)?>：<br>
		<a href = "//music.163.com/" target = "_blank" title = "<?php _e('Netease Music', FwmTD)?>"/><img src = "//s1.music.126.net/music.ico" width = "18"></a>Eric音像馆*281892685*0*netease<br>
        <a href = "//y.qq.com/" target = "_blank" title = "<?php _e('QQ Music', FwmTD)?>"/><img src = "//y.qq.com/favicon.ico" width = "18"></a>逆袭之星途璀璨 电视剧原声大碟第三章*004SnUPP4AaJAu*1*tencent|张靓颖*100*2*tencent|听这些歌曲串烧如临演唱现场一样过瘾*2066592292*3*tencent<br>
<!--         <a href = "//www.xiami.com/" target = "_blank" title = "<?php _e('Xiami Music', FwmTD)?>"/><img src = "//g.alicdn.com/de/music-static/favicon.ico" width = "18"></a>陈慧娴PRISCILLA-ISM演唱会*2100370674*1*xiami|梁静茹*100*2*xiami|一人一首粤语*749651*3*xiami<br>
        <a href = "//music.taihe.com/" target = "_blank" title = "<?php _e('Baidu Music', FwmTD)?>"/><img src = "//www.baidu.com/favicon.ico" width = "18"></a>上古情歌 电视剧原声带*541680639*1*baidu|华晨宇*100*2*baidu|颜值与歌声齐飞的boys*365752583*3*baidu<br>
-->
		<a href = "//www.kuwo.cn/" target = "_blank" title = "<?php _e('Kuwo Music', FwmTD)?>"/><img src = "//image.kuwo.cn/website/favicon.ico" width = "18"></a>别错过*18085605*1*kuwo|周深*100*2*kuwo|抖音霸榜*3161347461*3*kuwo|<br>
        <a href = "http://www.kugou.com/" target = "_blank" title = "<?php _e('Kugou Music', FwmTD)?>"/><img src = "<?php echo Fwm_Player_URL;?>/inc/kugou.ico" width = "18"></a>周杰伦的床边故事*1645030*1*kugou|张碧晨*100*2*kugou|2017上半年优质古风必听大盘点*127226*3*kugou
        <div class = "Fwm-player-input">

        </div>
      </div>
    </div>
    <!--播放器设置-->
    <div class = "postbox">
      <h3 class = "hndle"><?php _e('Player settings', FwmTD)?></h3>
      <div class = "inside">
        <table><tbody><tr><td><b><?php _e('Name of playlist', FwmTD)?>:</b></td><td><input type = "text" name = "Fwm_player_name" value = "<?php echo get_option('Fwm_player_name', get_bloginfo('name'));?>" class = "regular-text" /></td><td><a title = "<?php _e('Name of playlist', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></td></tr>
        <tr><td><b><?php _e('Prompts content', FwmTD)?>:</b></td><td><input type = "text" name = "Fwm_player_tips" value = "<?php echo get_option('Fwm_player_tips', __('Welcome to ', FwmTD).get_bloginfo('name'));?>" class = "regular-text" /></td><td><a title = "<?php _e('Welcome  prompt', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></td></tr>
        <tr><td><b><?php _e('Time of Auto-hide', FwmTD)?>:</b></td><td><select name = "Fwm_player_st">
        <option selected value = "<?php echo get_option('Fwm_player_st');?>"><?php echo get_option('Fwm_player_st','10'); ?></option>
        <option value = "NO"/>NO(<?php _e('Close', FwmTD)?>)</option><?php for ($i = 1; $i <= 10; $i++) {echo '<option value = "' . $i . '"/>' . $i . '</option>';}
        for ($i = 15; $i <= 30; $i += 5) {echo '<option value = "' . $i . '"/>' . $i . '</option>';}?></select></td><td><a title = "<?php _e('Time of Music-Player auto-hide, seconds', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></td></tr>
        <tr><td><b><?php _e('Load icons', FwmTD)?>:</b></td><td>
        <label><input type = "radio" name = "Fwm_player_style" value = "CUS" <?php if(get_option('Fwm_player_style') == "CUS") echo "checked"?>/><?php _e('Customize ', FwmTD)?></label> 
        <label><input type = "text" name = "Fwm_player_style_custom" placeholder = "<?php _e('Enter the full URL', FwmTD)?>" class = "button-prim3ary" value = "<?php echo get_option('Fwm_player_style_custom', '')?>"></label> 
        <label><input type = "radio" name = "Fwm_player_style" value = "YES" <?php if(get_option('Fwm_player_style') != "NO" && get_option('Fwm_player_jquery') != "CUS") echo "checked"?>/><?php _e('Open', FwmTD)?>&#12288;</label> 
        <label><input type = "radio" name = "Fwm_player_style" value = "NO" <?php if(get_option('Fwm_player_style') == "NO") echo "checked"?>/><?php _e('Close', FwmTD)?></td><td><a title = "Font Awesome 4 Menus<?php _e(', if the plug-in installed choose close', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></label></td></tr>
        <tr><td><b><?php _e('Load Base Lib', FwmTD)?>:</b></td><td>
        <label><input type = "radio" name = "Fwm_player_jquery" value = "CUS" <?php if(get_option('Fwm_player_jquery') == "CUS") echo "checked"?>/><?php _e('Customize ', FwmTD)?></label> 
        <label><input type = "text" name = "Fwm_player_jquery_custom" placeholder = "<?php _e('Enter the full URL', FwmTD)?>" class = "button-prim3ary" value = "<?php echo get_option('Fwm_player_jquery_custom', '')?>"></label> 
        <label><input type = "radio" name = "Fwm_player_jquery" value = "YES" <?php if(get_option('Fwm_player_jquery') != "NO" && get_option('Fwm_player_jquery') != "LOW" && get_option('Fwm_player_jquery') != "CUS") echo "checked"?>/><?php _e('High version', FwmTD)?><a title = "<?php _e('(IE9 above)', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></label> 
        <label><input type = "radio" name = "Fwm_player_jquery" value = "LOW" <?php if(get_option('Fwm_player_jquery') == "LOW") echo "checked"?>/><?php _e('Low version', FwmTD)?><a title = "(IE6/7/8)"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></label> 
        <label><input type = "radio" name = "Fwm_player_jquery" value = "NO" <?php if(get_option('Fwm_player_jquery') == "NO") echo "checked"?>/><?php _e('Close', FwmTD)?></td><td><a title = "<?php _e('JQuery Base Lib', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></label></td></tr></tbody></table>
        <table><tbody>
		<tr><td><label><input type = "checkbox" name = "Fwm_player_auto" value = "YES" <?php if(get_option('Fwm_player_auto') != "NO") echo "checked"?>/><b><?php _e('Autoplay', FwmTD)?></b></label></td><td><label><input type = "checkbox" name = "Fwm_player_geci" value = "YES" <?php if(get_option('Fwm_player_geci') != "NO") echo "checked"?>/><b><?php _e('Open Lyrics', FwmTD)?></b></label></td></tr>
		<tr><td><label><input type = "checkbox" name = "Fwm_player_random" value = "YES" <?php if(get_option('Fwm_player_random') != "NO") echo "checked"?>/><b><?php _e('Random Songs', FwmTD)?></b></label></td><td><label><input type = "checkbox" name = "Fwm_player_suiji" value = "YES" <?php if(get_option('Fwm_player_suiji') != "NO") echo "checked"?>/><b><?php _e('Random Album', FwmTD)?></b></label></td></tr>
        <tr><td><label><input type = "checkbox" name = "Fwm_player_move" value = "YES" <?php if(get_option('Fwm_player_move') != "NO") echo "checked"?>/><b><?php _e('Allow mobile-use', FwmTD)?></b> <a title = "<?php _e('Allow the player to play on the mobile side will consume a lot of traffic, carefully open', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></label></td><td><label><input type = "checkbox" onclick = "chk(this)" name = "Fwm_player_ajax" id = "Fwm_player_ajax" value = "YES" <?php if(get_option('Fwm_player_ajax') != "NO") echo "checked"?>/><b><?php _e('Load anti-refresh', FwmTD)?></b> <a title = "<?php _e('AJAX anti-refresh, if themes or plug-ins installed choose close', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></label></td></tr></tbody></table>
      </div>
    </div>
    <!--内置AJAX防刷新设置-->
    <div id = "ajax" class = "postbox">
      <h3 class = "hndle"><?php _e('Built-in AJAX anti-refresh settings', FwmTD)?></h3>
      <div class = "inside">
        <table><tbody>
		<tr><td><b><?php _e('Content Element ID', FwmTD)?>:</b></td><td><input type = "text" name = "Fwm_player_content" value = "<?php echo get_option('Fwm_player_content', 'content');?>" class = "regular-text"/></td><td><a title = "<?php _e('For most themes this should not need to be changed, however if it does you need to find the container which wraps around the page content (not including any menu bars (vertical and/or horizontal)), this container might already have an ID attribute of DIV, or you may need to assign one.', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></td></tr>
        <tr><td><b><?php _e('Search Form CLASS', FwmTD)?>:</b></td><td><input type = "text" name = "Fwm_player_searchform" value = "<?php echo get_option('Fwm_player_searchform', 'searchform');?>" class = "regular-text"/></td><td><a title = "<?php _e('This plugin automatically binds to the search form with the provided class name (default: searchform), but if this is not set for your theme it will just do a normal post search without ajax.', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></td></tr>
        <tr><td><b><?php _e('Article Title Location', FwmTD)?>:</b></td><td><input type = "text" name = "Fwm_player_title" value = "<?php echo get_option('Fwm_player_title', '2');?>" class = "regular-text"/></td><td><a title = "<?php _e('Article title behind which h1 (not included /h1)', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></td></tr>
        <tr><td><b><?php _e('Exception Link', FwmTD)?>:</b></td><td><input type = "text" name = "Fwm_player_string" value = "<?php echo get_option('Fwm_player_string', '/wp-, .pdf, .zip, .rar, reply');?>" class = "regular-text" /></td><td><a title = "<?php _e('Exception Link Format, separated by commas lowercase', FwmTD)?>"><i class = "fas fa-question-circle" aria-hidden = 1></i></a></td></tr></tbody></table>
      </div>
    </div>
    <!--自定义CSS-->
    <div class = "postbox">
      <h3 class = "hndle"><?php _e('Customize ', FwmTD)?>CSS</h3>
      <div class = "inside">
        <textarea rows = "4" cols = "53" name = "Fwm_player_css"><?php echo get_option('Fwm_player_css') ?></textarea><br>
        <?php _e('Common Settings Example (Default Value)', FwmTD)?>：<br>
        1.<?php _e('Adjust position of play button', FwmTD)?>：<b>#FwmPlayer .status{left:44px!important}</b>（<?php _e('Number gets small, location more left', FwmTD)?>）<br>
        2.<?php _e('Adjust font size of player', FwmTD)?>：<b>#FwmPlayer{font-size:100%!important}</b><br>
        3.<?php _e('Adjust font size of subtitle', FwmTD)?>：<b>#FwmLrc{font-size:1.2em!important}</b><br>
        4.<?php _e('Adjust font size of prompt', FwmTD)?>：<b>#FwmTips{font-size:1.4em!important}</b><br>
        5.<?php _e('Adjust the volume bar height', FwmTD)?>：<b>.volumeprogress{top:-133px!important}</b><br>
        <?php _e('Copy the appropriate code to the above column, without adding any delimiters, adjust the value and save it.', FwmTD)?>
      </div>
    </div>
    <p><input type = "submit" name = "submit" class = "button-primary" value = "<?php _e('Save Changes', FwmTD)?>"/> <b><?php _e('Be sure to save the settings once after the initial installation', FwmTD)?></b></p>
  </form>
  </div>
  <script type = "text/javascript" src = "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script language = "javascript">
    <?php if (get_option('Fwm_player_ajax') == "NO") echo '$("#ajax.postbox").hide();'?>
    function xiami() {
      row = $('#mtool').val().split("/");
	  var str = new Array();var str1 = new Array();
	  if (row[4].length > 5) {str1 = row[4].split("?")}
      if (row[3] == 'album') {str = row[4].split("?"); row[1] = '1'}//虾米，百度
      if (row[5] == 'album' || row[4] == 'album') {str = row[6].split("."); row[1] = '1'}//QQ，酷狗
	  if (str1[0] == 'album') {str = row[4].split("="); str[0] = str[1]; row[1] = '1'}//网易
	  if (row[3] == 'album_detail') {str = row[4].split("."); row[1] = '1'}//酷我
      if (row[3] == 'collect' || row[3] == 'songlist' || row[3] == 'playlist_detail') {str = row[4].split("?"); row[1] = '3'}//虾米，百度，酷我
      if (row[5] == 'playlist' || row[4] == 'special') {str = row[6].split("."); row[1] = '3'}//QQ，酷狗
	  if (str1[0] == 'playlist') {str = row[4].split("="); str[0] = str[1]; row[1] = '3'}//网易
	  $('#text2').val(str[0]);
	  $('#text3').val(row[1]);
	  switch (row[2]) {
		  case 'music.163.com': $('#text4').val('netease');break;
		  case 'y.qq.com': $('#text4').val('tencent');break;
		  case 'music.taihe.com': $('#text4').val('baidu');break;
		  case 'www.xiami.com': $('#text4').val('xiami');break;
		  case 'emumo.xiami.com': $('#text4').val('xiami');break;
		  case 'www.kugou.com': $('#text4').val('kugou');break;
		  case 'www.kuwo.cn': $('#text4').val('kuwo');break;
	  }
    }
	function sdata(){$('#Fwm_player_qqid').val($('#Fwm_player_qqid').val() + $('#text1').val() + "*" + $('#text2').val() + "*" + $('#text3').val() + "*" + $('#text4').val() + "|")}
    function chk(obj) {obj.checked ? $("#ajax.postbox").show() : $("#ajax.postbox").hide()}
  </script>
<?php }
function update_Fwm_Player_options() {
  if (isset($_POST['submit'])) {
    $_POST['Fwm_player_st'] && update_option('Fwm_player_st', sanitize_text_field($_POST['Fwm_player_st']));
    $_POST['Fwm_player_css'] && update_option('Fwm_player_css', sanitize_text_field($_POST['Fwm_player_css']));
    $_POST['Fwm_player_qqid'] && update_option('Fwm_player_qqid', sanitize_text_field($_POST['Fwm_player_qqid']));
    $_POST['Fwm_player_name'] && update_option('Fwm_player_name', sanitize_text_field($_POST['Fwm_player_name']));
    $_POST['Fwm_player_tips'] && update_option('Fwm_player_tips', sanitize_text_field($_POST['Fwm_player_tips']));
    $_POST['Fwm_player_style'] && update_option('Fwm_player_style', sanitize_text_field($_POST['Fwm_player_style']));
    $_POST['Fwm_player_title'] && update_option('Fwm_player_title', sanitize_text_field($_POST['Fwm_player_title']));
    $_POST['Fwm_player_string'] && update_option('Fwm_player_string', sanitize_text_field($_POST['Fwm_player_string']));
    $_POST['Fwm_player_jquery'] && update_option('Fwm_player_jquery', sanitize_text_field($_POST['Fwm_player_jquery']));
	$_POST['Fwm_player_cookie'] && update_option('Fwm_player_cookie', sanitize_text_field($_POST['Fwm_player_cookie']));
    $_POST['Fwm_player_content'] && update_option('Fwm_player_content', sanitize_text_field($_POST['Fwm_player_content']));
    $_POST['Fwm_player_searchform'] && update_option('Fwm_player_searchform', sanitize_text_field($_POST['Fwm_player_searchform']));
    $_POST['Fwm_player_style_custom'] && update_option('Fwm_player_style_custom', sanitize_text_field($_POST['Fwm_player_style_custom']));
    $_POST['Fwm_player_jquery_custom'] && update_option('Fwm_player_jquery_custom', sanitize_text_field($_POST['Fwm_player_jquery_custom']));
    $_POST['Fwm_player_geci'] ? update_option('Fwm_player_geci', sanitize_text_field($_POST['Fwm_player_geci'])) : update_option('Fwm_player_geci', 'NO');
    $_POST['Fwm_player_auto'] ? update_option('Fwm_player_auto', sanitize_text_field($_POST['Fwm_player_auto'])) : update_option('Fwm_player_auto', 'NO');
    $_POST['Fwm_player_move'] ? update_option('Fwm_player_move', sanitize_text_field($_POST['Fwm_player_move'])) : update_option('Fwm_player_move', 'NO');
    $_POST['Fwm_player_ajax'] ? update_option('Fwm_player_ajax', sanitize_text_field($_POST['Fwm_player_ajax'])) : update_option('Fwm_player_ajax', 'NO');
    $_POST['Fwm_player_suiji'] ? update_option('Fwm_player_suiji', sanitize_text_field($_POST['Fwm_player_suiji'])) : update_option('Fwm_player_suiji', 'NO');
    $_POST['Fwm_player_random'] ? update_option('Fwm_player_random', sanitize_text_field($_POST['Fwm_player_random'])) : update_option('Fwm_player_random', 'NO');
    print '<div class = "updated"><p>'; _e('Settings saved successfully', FwmTD); print '！</p></div><p></p>';
  }
}
function Fwm_add_font_awesome() {  
  if (!is_admin() && get_option('Fwm_player_style') =='YES') {
	 wp_enqueue_style( 'Fwm_add_font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', '', '6.0.0' );
     wp_enqueue_style( 'Fwm_add_font_awesome1', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/solid.min.css', '', '6.0.0' );
  }
  !is_admin() && get_option('Fwm_player_style') =='CUS' && wp_enqueue_style( 'Fwm_add_font_awesome', get_option('Fwm_player_style_custom'), '', 'custom' );
}
function Fwm_add_jquery() {  
  if (!is_admin() && get_option('Fwm_player_jquery') =='YES') {
    wp_deregister_script( 'jquery' );
    wp_register_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js', '' ,'3.6.0' ,0);
    wp_enqueue_script('jquery');  
  }
  if (!is_admin() && get_option('Fwm_player_jquery') =='LOW') {
    wp_deregister_script('jquery');
    wp_register_script('jquery','https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js', '' ,'1.12.4' ,0);
    wp_enqueue_script('jquery');  
  }
  if (!is_admin() && get_option('Fwm_player_jquery') =='CUS') {
    wp_deregister_script('jquery');
    wp_register_script('jquery',get_option('Fwm_player_jquery_custom'), '' ,'custom' ,0);
    wp_enqueue_script('jquery');  
  }
}
function Fwm_add_ajax() {  
  if (!is_admin() && get_option('Fwm_player_ajax') =='YES') {
    wp_enqueue_script('ajaxload',Fwm_Player_URL.'/js/ajaxload.js', '' ,Fwm_Player_VER ,0); 
    wp_localize_script( 'ajaxload', 'ajaxload_data', array( 'ajaxhome' => home_url(), 'searchform' => get_option('Fwm_player_searchform'), 'content' => get_option('Fwm_player_content'), 'string' => get_option('Fwm_player_string'), 'title' => get_option('Fwm_player_title')));
  }
}
add_action('init', 'Fwm_add_font_awesome' );
add_action('init', 'Fwm_add_jquery' );
add_action('init', 'Fwm_add_ajax' );
?>